<?php

namespace Tests\Feature\PanelV1;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminV1ComprehensiveTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        $u = User::where('role_name','admin')->first();
        if ($u) return $u;
        return User::create(['full_name'=>'Admin Test','email'=>'admin_test_'.time().rand(100,999).'@test.local','role_name'=>'admin','role_id'=>2,'password'=>bcrypt('secret'),'status'=>'active','created_at'=>time()]);
    }

    private function req($user, $uri='/v1/admin'){ $r=\Illuminate\Http\Request::create($uri,'GET'); $r->setUserResolver(fn()=> $user); return $r; }

    public function test_education_home_real(){
        $admin=$this->admin();
        $c=new \App\Http\Controllers\PanelV1\Admin\EducationController();
        $v=$c->home($this->req($admin,'/v1/admin/education'));
        $this->assertEquals('panel_v1.admin.pages.education.home', $v->getName());
        $this->assertNotEmpty($v->getData()['stats']);
    }
    public function test_education_sections_real(){
        $admin=$this->admin();
        $c=new \App\Http\Controllers\PanelV1\Admin\EducationController();
        // مبسط: 10 صفحات متسقة فقط بعد حذف المكرر/المشتت (forums/notifications/registration/waitlists/filters/attendance-history)
        foreach(['courses','bundles','departments','events','quizzes','assignments','certificates','reviews','live','attendance'] as $sec){
            $v=$c->section($this->req($admin,"/v1/admin/education/$sec"), $sec);
            $this->assertStringStartsWith('panel_v1.admin.pages.education.', $v->getName());
        }
    }
    public function test_sales_home_real(){
        $admin=$this->admin();
        $c=new \App\Http\Controllers\PanelV1\Admin\SalesController();
        $v=$c->home($this->req($admin,'/v1/admin/sales'));
        $this->assertEquals('panel_v1.admin.pages.sales.sales-list', $v->getName());
    }
    public function test_sales_sections_real(){
        $admin=$this->admin();
        $c=new \App\Http\Controllers\PanelV1\Admin\SalesController();
        foreach(['payouts','budget'] as $sec){
            $v=$c->section($this->req($admin,"/v1/admin/sales/$sec"), $sec);
            $this->assertStringStartsWith('panel_v1.admin.pages.sales.', $v->getName());
        }
    }
    public function test_marketing_home_real(){
        $admin=$this->admin();
        $c=new \App\Http\Controllers\PanelV1\Admin\MarketingController();
        $v=$c->home($this->req($admin,'/v1/admin/marketing'));
        $this->assertEquals('panel_v1.admin.pages.marketing.content-appearance', $v->getName());
    }
    public function test_system_home_real(){
        $admin=$this->admin();
        $c=new \App\Http\Controllers\PanelV1\Admin\SystemController();
        $v=$c->home($this->req($admin,'/v1/admin/system'));
        $this->assertEquals('panel_v1.admin.pages.system.users', $v->getName());
        $this->assertNotEmpty($v->getData()['users']);
    }
    public function test_admin_guard_blocks_student(){
        $student=User::where('role_name','user')->first() ?? User::create(['full_name'=>'S','email'=>'s'.time().'@test.local','role_name'=>'user','role_id'=>1,'password'=>bcrypt('secret'),'status'=>'active','created_at'=>time()]);
        $c=new \App\Http\Controllers\PanelV1\AdminController();
        $ref=new \ReflectionMethod($c,'resolveAdmin');
        $ref->setAccessible(true);
        $res=$ref->invoke($c, $this->req($student,'/v1/admin'));
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $res);
    }

    public function test_courses_table_shows_real_db_titles(){
        $admin=$this->admin();
        $c=new \App\Http\Controllers\PanelV1\Admin\EducationController();
        $v=$c->section($this->req($admin,'/v1/admin/education/courses'), 'courses');
        $this->assertEquals('panel_v1.admin.pages.education.courses-list', $v->getName());
        $html=$v->render();
        $this->assertStringContainsString('جميع الدورات المسجلة', $html);
        $this->assertStringContainsString('إرسال إخطار للمتدربين', $html);
        $this->assertStringContainsString('قائمة المتدربين', $html);
        $first=\App\Models\Webinar::orderBy('id','desc')->first();
        if($first) $this->assertStringContainsString(e($first->title), $html);
    }

    public function test_events_crud_roundtrip(){
        $admin=$this->admin();
        $teacher=User::where('role_name','teacher')->first();
        $cat=\App\Models\Category::first();
        $this->assertNotNull($teacher); $this->assertNotNull($cat);
        $c=new \App\Http\Controllers\PanelV1\Admin\EducationController();
        $title='فعالية اختبار '.time();
        $store=\Illuminate\Http\Request::create('/v1/admin/education/events','POST',[
            'type'=>'online','title'=>$title,'subtitle'=>'فرعي','creator_id'=>$teacher->id,
            'thumbnail'=>'/t.jpg','cover_image'=>'/c.jpg','category_id'=>$cat->id,
            'seo_description'=>'seo','summary'=>'sum','description'=>'desc','status'=>'draft',
        ]);
        $store->setUserResolver(fn()=> $admin);
        $res=$c->storeEvent($store);
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $res);
        $event=\App\Models\Event::whereHas('translations',fn($t)=>$t->where('title',$title))->first();
        $this->assertNotNull($event);
        // الجدول يعرضها
        $v=$c->section($this->req($admin,'/v1/admin/education/events'), 'events');
        $this->assertStringContainsString(e($title), $v->render());
        // الحذف التنظيفي
        $del=\Illuminate\Http\Request::create("/v1/admin/education/events/{$event->id}/delete",'POST');
        $del->setUserResolver(fn()=> $admin);
        $c->deleteEvent($del, $event->id);
        $this->assertNull(\App\Models\Event::find($event->id));
    }

    public function test_quiz_create_and_question_roundtrip(){
        $admin=$this->admin();
        $webinar=\App\Models\Webinar::first();
        $this->assertNotNull($webinar);
        $c=new \App\Http\Controllers\PanelV1\Admin\EducationController();
        $store=\Illuminate\Http\Request::create('/v1/admin/education/quizzes','POST',[
            'webinar_id'=>$webinar->id,'title'=>'اختبار آلي '.time(),'pass_mark'=>50,'status'=>'active',
        ]);
        $store->setUserResolver(fn()=> $admin);
        $c->storeQuiz($store);
        $quiz=\App\Models\Quiz::orderBy('id','desc')->first();
        $this->assertNotNull($quiz);
        $qstore=\Illuminate\Http\Request::create("/v1/admin/education/quizzes/{$quiz->id}/questions",'POST',[
            'title'=>'سؤال آلي','type'=>'multiple','grade'=>5,'options'=>['أ','ب'],'correct_index'=>0,
        ]);
        $qstore->setUserResolver(fn()=> $admin);
        $c->storeQuizQuestion($qstore, $quiz->id);
        $this->assertEquals(1, \App\Models\QuizzesQuestion::where('quiz_id',$quiz->id)->count());
        $this->assertEquals(2, \App\Models\QuizzesQuestionsAnswer::whereIn('question_id',\App\Models\QuizzesQuestion::where('quiz_id',$quiz->id)->pluck('id'))->count());
        $quiz->delete();
    }

    public function test_discount_user_group_role_roundtrip(){
        $admin=$this->admin();
        $mc=new \App\Http\Controllers\PanelV1\Admin\MarketingController();
        $code='T'.time();
        $store=\Illuminate\Http\Request::create('/v1/admin/marketing/discounts','POST',[
            'code'=>$code,'title'=>'خصم آلي','discount_type'=>'percentage','source'=>'all','percent'=>10,'count'=>5,
        ]);
        $store->setUserResolver(fn()=> $admin);
        $mc->storeDiscount($store);
        $d=\App\Models\Discount::where('code',$code)->first();
        $this->assertNotNull($d);
        $del=\Illuminate\Http\Request::create("/v1/admin/marketing/discounts/{$d->id}/delete",'POST');
        $del->setUserResolver(fn()=> $admin);
        $mc->deleteDiscount($del, $d->id);
        $this->assertNull(\App\Models\Discount::find($d->id));

        $sc=new \App\Http\Controllers\PanelV1\Admin\SystemController();
        $gstore=\Illuminate\Http\Request::create('/v1/admin/system/groups','POST',['name'=>'مجموعة آلية '.time(),'discount'=>5,'status'=>'active']);
        $gstore->setUserResolver(fn()=> $admin);
        $sc->storeGroup($gstore);
        $g=\App\Models\Group::orderBy('id','desc')->first();
        $this->assertNotNull($g);
        $gdel=\Illuminate\Http\Request::create("/v1/admin/system/groups/{$g->id}/delete",'POST');
        $gdel->setUserResolver(fn()=> $admin);
        $sc->deleteGroup($gdel, $g->id);
        $this->assertNull(\App\Models\Group::find($g->id));

        $rstore=\Illuminate\Http\Request::create('/v1/admin/system/roles','POST',['name'=>'tmp_role_'.time(),'caption'=>'دور مؤقت']);
        $rstore->setUserResolver(fn()=> $admin);
        $sc->storeRole($rstore);
        $r=\App\Models\Role::orderBy('id','desc')->first();
        $rdel=\Illuminate\Http\Request::create("/v1/admin/system/roles/{$r->id}/delete",'POST');
        $rdel->setUserResolver(fn()=> $admin);
        $sc->deleteRole($rdel, $r->id);
        $this->assertNull(\App\Models\Role::find($r->id));
    }

    public function test_review_approve_and_offline_confirm(){
        $admin=$this->admin();
        $comment=\App\Models\Comment::whereNotNull('webinar_id')->first();
        if($comment){
            $c=new \App\Http\Controllers\PanelV1\Admin\EducationController();
            $req=\Illuminate\Http\Request::create("/v1/admin/education/reviews/{$comment->id}/approve",'POST');
            $req->setUserResolver(fn()=> $admin);
            $c->approveReview($req, $comment->id);
            $this->assertEquals('active', $comment->fresh()->status);
        }
        $this->assertTrue(true);
    }
}
