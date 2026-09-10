<?php

namespace Tests\Feature\PanelV1;

use App\Http\Controllers\PanelV1\CoursePlayerController;
use App\Http\Controllers\PanelV1\InstructorController;
use App\Models\Quiz;
use App\Models\Sale;
use App\Models\Webinar;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class OwnershipAndAccessTest extends TestCase
{
    use DatabaseTransactions;

    private function makeUser(string $roleName, int $roleId): User
    {
        return User::create([
            'full_name' => 'Owner Test',
            'email' => 'owner_' . $roleName . '_' . time() . rand(1000, 9999) . '@test.local',
            'role_name' => $roleName,
            'role_id' => $roleId,
            'password' => 'secret',
            'status' => 'active',
            'created_at' => time(),
        ]);
    }

    private function makeWebinar(User $teacher, string $slug): Webinar
    {
        $webinar = new Webinar();
        $webinar->teacher_id = $teacher->id;
        $webinar->creator_id = $teacher->id;
        $webinar->type = 'course';
        $webinar->status = 'active';
        $webinar->slug = $slug . '_' . time() . rand(1000, 9999);
        $webinar->created_at = time();
        $webinar->save();

        return $webinar;
    }

    public function test_teacher_cannot_open_foreign_webinar(): void
    {
        $owner = $this->makeUser('teacher', 4);
        $intruder = $this->makeUser('teacher', 4);
        $webinar = $this->makeWebinar($owner, 'owned');

        $controller = new InstructorController();
        $ref = new \ReflectionMethod($controller, 'teacherWebinarOrFail');
        $ref->setAccessible(true);

        // firstOrFail() throws ModelNotFoundException; the HTTP layer turns it into a 404.
        $this->expectException(ModelNotFoundException::class);
        $ref->invoke($controller, $intruder, $webinar->slug);
    }

    public function test_teacher_opens_own_webinar(): void
    {
        $owner = $this->makeUser('teacher', 4);
        $webinar = $this->makeWebinar($owner, 'mine');

        $controller = new InstructorController();
        $ref = new \ReflectionMethod($controller, 'teacherWebinarOrFail');
        $ref->setAccessible(true);

        $this->assertSame($webinar->id, $ref->invoke($controller, $owner, $webinar->slug)->id);
    }

    public function test_teacher_cannot_grade_foreign_quiz(): void
    {
        $owner = $this->makeUser('teacher', 4);
        $intruder = $this->makeUser('teacher', 4);
        $webinar = $this->makeWebinar($owner, 'q');

        $quiz = new Quiz();
        $quiz->webinar_id = $webinar->id;
        $quiz->creator_id = $owner->id;
        $quiz->pass_mark = 50;
        $quiz->certificate = 0;
        $quiz->status = 'active';
        $quiz->created_at = time();
        $quiz->save();

        $controller = new InstructorController();
        $ref = new \ReflectionMethod($controller, 'teacherQuizOrFail');
        $ref->setAccessible(true);

        // teacherQuizOrFail() calls abort(404) for foreign quizzes.
        $this->expectException(NotFoundHttpException::class);
        $ref->invoke($controller, $intruder, $quiz->id);
    }

    public function test_free_course_needs_no_purchase(): void
    {
        $student = $this->makeUser('user', 1);
        $teacher = $this->makeUser('teacher', 4);
        $webinar = $this->makeWebinar($teacher, 'free');
        $webinar->price = null;
        $webinar->save();

        $controller = new CoursePlayerController();
        $ref = new \ReflectionMethod($controller, 'canAccess');
        $ref->setAccessible(true);

        $this->assertTrue($ref->invoke($controller, $student, $webinar->fresh()));
    }

    public function test_paid_course_requires_purchase(): void
    {
        $student = $this->makeUser('user', 1);
        $teacher = $this->makeUser('teacher', 4);
        $webinar = $this->makeWebinar($teacher, 'paid');
        $webinar->price = 100;
        $webinar->save();

        $controller = new CoursePlayerController();
        $ref = new \ReflectionMethod($controller, 'canAccess');
        $ref->setAccessible(true);

        $this->assertFalse($ref->invoke($controller, $student, $webinar->fresh()));

        Sale::create([
            'buyer_id' => $student->id, 'seller_id' => $teacher->id,
            'webinar_id' => $webinar->id, 'type' => 'webinar',
            'amount' => 100, 'total_amount' => 100, 'created_at' => time(),
        ]);

        $this->assertTrue($ref->invoke($controller, $student, $webinar->fresh()));
    }
}
