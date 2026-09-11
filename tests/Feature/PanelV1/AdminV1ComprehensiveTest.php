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
        foreach(['courses','bundles','assignments','quizzes','certificates','live','forums','reviews','departments','attendance'] as $sec){
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
}
