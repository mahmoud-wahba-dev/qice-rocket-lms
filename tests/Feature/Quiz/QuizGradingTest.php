<?php

namespace Tests\Feature\Quiz;

use App\Http\Controllers\PanelV1\CoursePlayerController;
use App\Models\Quiz;
use App\Models\QuizzesQuestion;
use App\Models\QuizzesQuestionsAnswer;
use App\Models\QuizzesResult;
use App\Models\Webinar;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class QuizGradingTest extends TestCase
{
    use DatabaseTransactions;

    private User $teacher;
    private User $student;
    private Webinar $webinar;
    private Quiz $quiz;
    private QuizzesQuestion $multiple;
    private ?QuizzesQuestion $descriptive = null;
    private int $correctAnswerId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacher = $this->makeUser('teacher', 4);
        $this->student = $this->makeUser('user', 1);

        $this->webinar = new Webinar();
        $this->webinar->teacher_id = $this->teacher->id;
        $this->webinar->creator_id = $this->teacher->id;
        $this->webinar->type = 'course';
        $this->webinar->status = 'active';
        $this->webinar->slug = 'quizt_' . time() . rand(1000, 9999);
        $this->webinar->created_at = time();
        $this->webinar->save();

        $this->quiz = new Quiz();
        $this->quiz->webinar_id = $this->webinar->id;
        $this->quiz->creator_id = $this->teacher->id;
        $this->quiz->pass_mark = 50;
        $this->quiz->certificate = 0;
        $this->quiz->status = 'active';
        $this->quiz->created_at = time();
        $this->quiz->save();

        $this->multiple = $this->makeQuestion('multiple', 50, 10);

        $correct = new QuizzesQuestionsAnswer();
        $correct->question_id = $this->multiple->id;
        $correct->creator_id = $this->teacher->id;
        $correct->correct = 1;
        $correct->created_at = time();
        $correct->updated_at = time();
        $correct->save();
        $this->correctAnswerId = $correct->id;

        $wrong = new QuizzesQuestionsAnswer();
        $wrong->question_id = $this->multiple->id;
        $wrong->creator_id = $this->teacher->id;
        $wrong->correct = 0;
        $wrong->created_at = time();
        $wrong->updated_at = time();
        $wrong->save();
    }

    private function makeUser(string $roleName, int $roleId): User
    {
        return User::create([
            'full_name' => 'Quiz Test',
            'email' => 'quiz_' . $roleName . '_' . time() . rand(1000, 9999) . '@test.local',
            'role_name' => $roleName,
            'role_id' => $roleId,
            'password' => 'secret',
            'status' => 'active',
            'created_at' => time(),
        ]);
    }

    private function makeQuestion(string $type, int $grade, int $negative): QuizzesQuestion
    {
        $question = new QuizzesQuestion();
        $question->quiz_id = $this->quiz->id;
        $question->creator_id = $this->teacher->id;
        $question->grade = $grade;
        $question->negative_grade = $negative;
        $question->type = $type;
        $question->created_at = time();
        $question->updated_at = time();
        $question->save();

        return $question;
    }

    private function finish(array $answers)
    {
        session()->put('v1_quiz_answers_' . $this->quiz->id, $answers);

        $controller = new CoursePlayerController();
        $ref = new \ReflectionMethod($controller, 'finishQuiz');
        $ref->setAccessible(true);

        return $ref->invoke($controller, $this->student, $this->quiz, $this->webinar->slug);
    }

    private function lastResult(): ?QuizzesResult
    {
        return QuizzesResult::where('quiz_id', $this->quiz->id)
            ->where('user_id', $this->student->id)
            ->orderBy('id', 'desc')
            ->first();
    }

    public function test_correct_multiple_choice_passes(): void
    {
        $response = $this->finish([$this->multiple->id => ['answer' => $this->correctAnswerId]]);

        $this->assertStringContainsString('/quiz', $response->getTargetUrl());

        $result = $this->lastResult();
        $this->assertNotNull($result);
        $this->assertSame('passed', $result->status);
        $this->assertSame(50, (int) $result->user_grade);
    }

    public function test_wrong_answer_applies_negative_mark_and_fails(): void
    {
        $wrongId = QuizzesQuestionsAnswer::where('question_id', $this->multiple->id)
            ->where('correct', 0)->value('id');

        $this->finish([$this->multiple->id => ['answer' => $wrongId]]);

        $result = $this->lastResult();
        $this->assertNotNull($result);
        $this->assertSame('failed', $result->status);
        $this->assertSame(0, (int) $result->user_grade);
    }

    public function test_descriptive_answer_forces_waiting_status(): void
    {
        $this->descriptive = $this->makeQuestion('descriptive', 50, 0);

        $this->finish([
            $this->multiple->id => ['answer' => $this->correctAnswerId],
            $this->descriptive->id => ['text' => 'شرح تجريبي'],
        ]);

        $result = $this->lastResult();
        $this->assertNotNull($result);
        $this->assertSame('waiting', $result->status);
    }

    public function test_grade_is_never_negative(): void
    {
        $wrongId = QuizzesQuestionsAnswer::where('question_id', $this->multiple->id)
            ->where('correct', 0)->value('id');

        // Two wrong answers would sum below zero without clamping (tested via single heavy negative).
        $this->multiple->negative_grade = 999;
        $this->multiple->save();

        $this->finish([$this->multiple->id => ['answer' => $wrongId]]);

        $this->assertSame(0, (int) $this->lastResult()->user_grade);
    }
}
