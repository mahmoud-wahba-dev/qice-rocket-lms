<?php

namespace App\Http\Controllers\PanelV1\Admin;

use App\Http\Controllers\PanelV1\AdminController;
use App\Http\Controllers\PanelV1\Support\AdminMockData;
use Illuminate\Http\Request;

class SystemController extends AdminController
{
    public function home(Request $request)
    {
        $users=\App\User::orderBy('id','desc')->limit(20)->get()->map(fn($u)=>[
            'id'=>$u->id,'name'=>$u->full_name,'email'=>$u->email,'role'=>$u->role_name,'status'=>$u->status
        ])->all();
        $counts=[
            ['label'=>'المتدربون','value'=>(string)\App\User::where('role_name','user')->count()],
            ['label'=>'المدربون','value'=>(string)\App\User::where('role_name','teacher')->count()],
            ['label'=>'المنظمات','value'=>(string)\App\User::where('role_name','organization')->count()],
        ];
        return $this->renderAdmin(
            $request,
            'panel_v1.admin.pages.system.users',
            'المستخدمين',
            ['users'=>$users,'stats'=>$counts,'pageTitleText'=>'المستخدمين']
        );
    }

    public function section(Request $request, string $section)
    {
        $shell=AdminMockData::shell('system', $section);
        $real=[]; $title=$section;
        switch($section){
            case 'users':
                return $this->home($request);
            case 'settings':
                $title='الإعدادات'; $real['settings']=\App\Models\Setting::orderBy('id','desc')->limit(20)->get(); break;
            case 'roles':
                $title='الأدوار'; $real['roles']=\App\Models\Role::orderBy('id','desc')->limit(20)->get(); break;
            case 'access':
                $title='إدارة الوصول'; $real['access']=\App\Models\Role::orderBy('id','desc')->limit(20)->get(); break;
            case 'groups':
                $title='المجموعات'; $real['groups']=class_exists(\App\Models\Group::class) ? \App\Models\Group::orderBy('id','desc')->limit(20)->get() : collect(); break;
            case 'badges':
                $title='الشارات'; $real['badges']=class_exists(\App\Models\Badge::class) ? \App\Models\Badge::orderBy('id','desc')->limit(20)->get() : collect(); break;
            case 'custom-badges':
                $title='شارات مخصصة'; $real['badges']=class_exists(\App\Models\Badge::class) ? \App\Models\Badge::orderBy('id','desc')->limit(20)->get() : collect(); break;
            case 'instructor-requests':
                $title='طلبات المدربين'; $real['requests']=\App\User::where('role_name','teacher')->where('status','pending')->orderBy('id','desc')->limit(20)->get(); break;
            case 'delete-requests':
                $title='طلبات الحذف'; $real['deletes']=class_exists(\App\Models\DeleteAccountRequest::class) ? \App\Models\DeleteAccountRequest::orderBy('id','desc')->limit(20)->get() : collect(); break;
            case 'ip':
                $title='عناوين IP'; $real['ips']=\App\Models\IpRestriction::orderBy('id','desc')->limit(20)->get(); break;
            default:
                $meta=AdminMockData::stubMeta('system', $section);
                $title=$meta['stubTitle'] ?? $section;
                $real=$meta;
                break;
        }
        $data=array_merge($shell,$real,['stubTitle'=>$title]);
        return $this->renderAdmin($request,'panel_v1.admin.pages.system.stub',$data['stubTitle'],$data);
    }
}
