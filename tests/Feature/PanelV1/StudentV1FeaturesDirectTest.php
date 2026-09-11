<?php

namespace Tests\Feature\PanelV1;

use App\Http\Controllers\PanelV1\CoursePlayerController;
use App\Http\Controllers\PanelV1\StudentController;
use App\Models\Support;
use App\Models\SupportConversation;
use App\Models\Webinar;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

class StudentV1FeaturesDirectTest extends TestCase
{
    use DatabaseTransactions;

    private function makeStudent(): User
    {
        return User::create([
            'full_name' => 'Test Student',
            'email' => 'v1_test_'.time().rand(100,999).'@test.local',
            'role_name' => 'user',
            'role_id' => 1,
            'password' => bcrypt('secret'),
            'status' => 'active',
            'created_at' => time(),
        ]);
    }

    private function reqWithUser(User $user, string $uri = '/v1/student'): Request
    {
        $req = Request::create($uri, 'GET');
        $req->setUserResolver(fn() => $user);
        return $req;
    }

    public function test_home_renders() {
        $user = $this->makeStudent();
        $c = new StudentController();
        $view = $c->home($this->reqWithUser($user));
        $this->assertEquals('panel_v1.student.pages.home', $view->getName());
    }
    public function test_certificates_renders() {
        $user = $this->makeStudent();
        $c = new StudentController();
        $view = $c->certificates($this->reqWithUser($user));
        $this->assertEquals('panel_v1.student.pages.certificates', $view->getName());
    }
    public function test_assignments_renders() {
        $user = $this->makeStudent();
        $c = new StudentController();
        $view = $c->assignmentsPage($this->reqWithUser($user));
        $this->assertEquals('panel_v1.student.pages.assignments', $view->getName());
    }
    public function test_quizzes_renders() {
        $user = $this->makeStudent();
        $c = new StudentController();
        $view = $c->quizzesPage($this->reqWithUser($user));
        $this->assertEquals('panel_v1.student.pages.quizzes', $view->getName());
    }
    public function test_comments_renders() {
        $user = $this->makeStudent();
        $c = new StudentController();
        $view = $c->commentsPage($this->reqWithUser($user));
        $this->assertEquals('panel_v1.student.pages.comments', $view->getName());
    }
    public function test_meetings_renders() {
        $user = $this->makeStudent();
        $c = new StudentController();
        $view = $c->meetings($this->reqWithUser($user));
        $this->assertEquals('panel_v1.student.pages.meetings', $view->getName());
    }
    public function test_support_and_thread_and_reply_and_close() {
        $user = $this->makeStudent();
        $c = new StudentController();
        // support index
        $view = $c->support($this->reqWithUser($user, '/v1/student/support'));
        $this->assertEquals('panel_v1.student.pages.support', $view->getName());

        // create ticket
        $ticket = Support::create(['user_id'=>$user->id,'title'=>'تذكرة اختبار','status'=>'open','created_at'=>time(),'updated_at'=>time()]);
        SupportConversation::create(['support_id'=>$ticket->id,'sender_id'=>$user->id,'message'=>'رسالة','created_at'=>time()]);

        $view2 = $c->supportShow($this->reqWithUser($user), $ticket->id);
        $this->assertEquals('panel_v1.student.pages.support-thread', $view2->getName());

        // reply
        $req = Request::create("/v1/student/support/{$ticket->id}/reply",'POST',['message'=>'رد تجريبي']);
        $req->setUserResolver(fn()=>$user);
        $resp = $c->storeSupportConversation($req, $ticket->id);
        $this->assertEquals(302, $resp->getStatusCode());
        $this->assertDatabaseHas('support_conversations',['support_id'=>$ticket->id,'message'=>'رد تجريبي']);

        // close
        $req2 = Request::create("/v1/student/support/{$ticket->id}/close",'POST');
        $req2->setUserResolver(fn()=>$user);
        $resp2 = $c->closeSupport($req2, $ticket->id);
        $this->assertEquals(302, $resp2->getStatusCode());
        $this->assertEquals('close', Support::find($ticket->id)->status);
    }
    public function test_purchases_favorites_notes_notifications_settings() {
        $user = $this->makeStudent();
        $c = new StudentController();
        $this->assertEquals('panel_v1.student.pages.purchases', $c->purchases($this->reqWithUser($user))->getName());
        $this->assertEquals('panel_v1.student.pages.favorites', $c->favorites($this->reqWithUser($user))->getName());
        $this->assertEquals('panel_v1.student.pages.notes', $c->notes($this->reqWithUser($user))->getName());
        $this->assertEquals('panel_v1.student.pages.notifications', $c->notifications($this->reqWithUser($user))->getName());
        $this->assertEquals('panel_v1.student.pages.settings', $c->settings($this->reqWithUser($user))->getName());
    }
    public function test_course_player_pages() {
        $user = $this->makeStudent();
        $webinar = Webinar::create([
            'teacher_id'=>$user->id,'creator_id'=>$user->id,'type'=>'webinar','status'=>'active','slug'=>'test-'.time().rand(100,999),'price'=>0,'created_at'=>time(),'updated_at'=>time(),
        ]);
        $webinar->translateOrNew('ar')->title='دورة اختبار';
        $webinar->translateOrNew('ar')->save();
        $cp = new CoursePlayerController();
        $req = $this->reqWithUser($user, "/v1/student/courses/{$webinar->slug}/watch");
        $view = $cp->watch($req, $webinar->slug);
        $this->assertEquals('panel_v1.student.course-player.pages.watch', $view->getName());
        $view2 = $cp->forum($req, $webinar->slug);
        $this->assertEquals('panel_v1.student.course-player.pages.forum', $view2->getName());
        $view3 = $cp->assignment($req, $webinar->slug);
        $this->assertEquals('panel_v1.student.course-player.pages.assignment', $view3->getName());
    }
}
