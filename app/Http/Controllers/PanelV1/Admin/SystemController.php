<?php

namespace App\Http\Controllers\PanelV1\Admin;

use App\Exports\UsersExport;
use App\Http\Controllers\PanelV1\AdminController;
use App\Http\Controllers\PanelV1\Support\AdminMockData;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SystemController extends AdminController
{
    public function home(Request $request)
    {
        $search = trim((string)$request->input('search',''));
        $q = \App\User::orderBy('id','desc');
        if($search !== ''){
            $q->where(function($qq) use ($search){
                $qq->where('id', $search);
                $qq->orWhere('full_name','like',"%{$search}%");
                $qq->orWhere('email','like',"%{$search}%");
            });
        }
        $paginator = $q->paginate(15)->withQueryString();
        $userRows=collect($paginator->items())->map(function($u){
            $groupTitle = 'عام';
            try { $g = $u->getUserGroup(); if(!empty($g) && !empty($g->name)) $groupTitle = $g->name; } catch(\Throwable $e) {}
            return [
                'id'=>$u->id,
                'name'=>$u->full_name,
                'email'=>$u->email,
                'role'=>$u->role_name,
                'balance'=> handlePrice($u->balance ?? 0),
                'income'=> handlePrice($u->income ?? 0),
                'group'=> $groupTitle,
                'registered_at'=> date('Y/m/d',(int)$u->created_at),
                'status'=>$u->status,
            ];
        })->all();
        $counts=[
            ['label'=>'المتدربون','value'=>(string)\App\User::where('role_name','user')->count()],
            ['label'=>'المدربون','value'=>(string)\App\User::where('role_name','teacher')->count()],
            ['label'=>'المنظمات','value'=>(string)\App\User::where('role_name','organization')->count()],
        ];
        return $this->renderAdmin(
            $request,
            'panel_v1.admin.pages.system.users',
            'المستخدمين',
            array_merge(AdminMockData::shell('system','home'), [
                'users'=>$userRows,
                'userRows'=>$userRows,
                'userTabs'=>['الكل','الطلاب','المدربون','المنظمات','المشرفون'],
                'paginator'=>$paginator,
                'pagination'=>['from'=>$paginator->firstItem() ?? 0,'to'=>$paginator->lastItem() ?? 0,'total'=>$paginator->total()],
                'stats'=>$counts,
                'pageTitleText'=>'المستخدمين'
            ])
        );
    }

    public function section(Request $request, string $section)
    {
        $shell=AdminMockData::shell('system', $section);
        $real=[]; $title=$section;
        $search = trim((string)$request->input('search',''));
        switch($section){
            case 'users':
                return $this->home($request);
            case 'settings':
                $title='الإعدادات';
                $q=\App\Models\Setting::orderBy('id','desc');
                if($search!=='') $q->where('id',$search)->orWhere('name','like',"%{$search}%");
                $real['settings']=$q->paginate(15)->withQueryString();
                $real['paginator']=$real['settings'];
                break;
            case 'roles':
                $title='الأدوار';
                $q=\App\Models\Role::orderBy('id','desc');
                if($search!=='') $q->where('id',$search)->orWhere('name','like',"%{$search}%");
                $real['roles']=$q->paginate(15)->withQueryString();
                $real['paginator']=$real['roles'];
                break;
            case 'access':
                $title='إدارة الوصول';
                $q=\App\Models\Role::orderBy('id','desc');
                if($search!=='') $q->where('id',$search);
                $real['access']=$q->paginate(15)->withQueryString();
                $real['paginator']=$real['access'];
                break;
            case 'groups':
                $title='المجموعات';
                if(class_exists(\App\Models\Group::class)){
                    $q=\App\Models\Group::orderBy('id','desc');
                    if($search!=='') $q->where('id',$search)->orWhere('name','like',"%{$search}%");
                    $real['groups']=$q->paginate(15)->withQueryString();
                    $real['paginator']=$real['groups'];
                } else { $real['groups']=collect(); }
                break;
            case 'badges':
                $title='الشارات';
                if(class_exists(\App\Models\Badge::class)){
                    $q=\App\Models\Badge::orderBy('id','desc');
                    if($search!=='') $q->where('id',$search)->orWhere('title','like',"%{$search}%");
                    $real['badges']=$q->paginate(15)->withQueryString();
                    $real['paginator']=$real['badges'];
                } else { $real['badges']=collect(); }
                break;
            case 'custom-badges':
                $title='شارات مخصصة';
                if(class_exists(\App\Models\Badge::class)){
                    $q=\App\Models\Badge::orderBy('id','desc');
                    if($search!=='') $q->where('id',$search);
                    $real['badges']=$q->paginate(15)->withQueryString();
                    $real['paginator']=$real['badges'];
                } else { $real['badges']=collect(); }
                break;
            case 'instructor-requests':
                $title='طلبات المدربين';
                $q=\App\User::where('role_name','teacher')->where('status','pending')->orderBy('id','desc');
                if($search!=='') $q->where(function($qq) use ($search){ $qq->where('id',$search)->orWhere('full_name','like',"%{$search}%"); });
                $real['requests']=$q->paginate(15)->withQueryString();
                $real['paginator']=$real['requests'];
                break;
            case 'delete-requests':
                $title='طلبات الحذف';
                if(class_exists(\App\Models\DeleteAccountRequest::class)){
                    $q=\App\Models\DeleteAccountRequest::with(['user'])->orderBy('id','desc');
                    if($search!=='') $q->where('id',$search);
                    $real['deletes']=$q->paginate(15)->withQueryString();
                    $real['paginator']=$real['deletes'];
                } else { $real['deletes']=collect(); }
                break;
            case 'ip':
                $title='عناوين IP';
                $q=\App\Models\IpRestriction::orderBy('id','desc');
                if($search!=='') $q->where('id',$search)->orWhere('ip_address','like',"%{$search}%");
                $real['ips']=$q->paginate(15)->withQueryString();
                $real['paginator']=$real['ips'];
                break;
            case 'tickets':
                $title='نظام التذاكر';
                $q=\App\Models\Support::with(['user','department'])->orderBy('id','desc');
                if($search!=='') $q->where('id',$search)->orWhere('title','like',"%{$search}%");
                $real['tickets']=$q->paginate(15)->withQueryString();
                $real['paginator']=$real['tickets'];
                break;
            case 'reports':
                $title='البلاغات';
                $q=\App\Models\CommentReport::with(['user','comment'])->orderBy('id','desc');
                if($search!=='') $q->where('id',$search);
                $real['reports']=$q->paginate(15)->withQueryString();
                $real['paginator']=$real['reports'];
                break;
            case 'contact':
                $title='رسائل التواصل';
                $q=\App\Models\Contact::orderBy('id','desc');
                if($search!=='') $q->where('id',$search)->orWhere('name','like',"%{$search}%")->orWhere('email','like',"%{$search}%");
                $real['contacts']=$q->paginate(15)->withQueryString();
                $real['paginator']=$real['contacts'];
                break;
            case 'consultations':
                $title='الاستشارات';
                $q=\App\Models\ReserveMeeting::with(['user'])->orderBy('id','desc');
                if($search!=='') $q->where('id',$search);
                $real['consultations']=$q->paginate(15)->withQueryString();
                $real['paginator']=$real['consultations'];
                break;
            case 'forums':
                $title='المنتديات';
                $q=\App\Models\Forum::orderBy('id','desc');
                if($search!=='') $q->where('id',$search);
                $real['forums']=$q->paginate(15)->withQueryString();
                $real['paginator']=$real['forums'];
                break;
            case 'notifications':
                $title='مركز الإشعارات';
                $q=\App\Models\Notification::with(['user'])->orderBy('id','desc');
                if($search!=='') $q->where('id',$search);
                $real['notifications']=$q->paginate(15)->withQueryString();
                $real['paginator']=$real['notifications'];
                break;
            case 'import':
                $title='الاستيراد الجماعي';
                $real['imports']=\App\Models\BulkImport::orderBy('id','desc')->paginate(15)->withQueryString();
                $real['paginator']=$real['imports'];
                $real['validItems']=session()->get('valid_items_session',[]);
                $real['invalidCount']=session()->get('invalid_items_count_session',0);
                break;
            case 'support_departments':
                $title='أقسام الدعم'; $q=class_exists(\App\Models\SupportDepartment::class)?\App\Models\SupportDepartment::orderBy('id','desc'):\App\Models\Support::orderBy('id','desc'); if($search!=='') $q->where('id',$search); $real['supportDepartments']=$q->paginate(15)->withQueryString(); $real['paginator']=$real['supportDepartments']; break;
            case 'notification_templates':
                $title='قوالب الإشعارات'; $q=\App\Models\NotificationTemplate::orderBy('id','desc'); if($search!=='') $q->where('id',$search)->orWhere('title','like',"%{$search}%"); $real['notificationTemplates']=$q->paginate(15)->withQueryString(); $real['paginator']=$real['notificationTemplates']; break;
            case 'region':
                $title='المناطق'; $q=\App\Models\Region::orderBy('id','desc'); if($search!=='') $q->where('id',$search)->orWhere('title','like',"%{$search}%"); $real['regions']=$q->paginate(15)->withQueryString(); $real['paginator']=$real['regions']; break;
            case 'login_history':
                $title='سجل تسجيل الدخول'; $q=\App\Models\UserLoginHistory::with(['user'])->orderBy('id','desc'); if($search!=='') $q->where('id',$search); $real['loginHistories']=$q->paginate(15)->withQueryString(); $real['paginator']=$real['loginHistories']; break;
            case 'not_access':
                $title='المستخدمون بلا وصول'; $q=\App\Models\Sale::where('access_to_purchased_item',false)->with(['buyer','webinar'])->orderBy('id','desc'); $real['notAccess']=$q->paginate(15)->withQueryString(); $real['paginator']=$real['notAccess']; break;
            case 'ai_contents':
                $title='محتوى الذكاء الاصطناعي'; $q=class_exists(\App\Models\AIContent::class)?\App\Models\AIContent::orderBy('id','desc'):\App\Models\Setting::where('name','like','%ai%')->orderBy('id','desc'); $real['aiContents']=$q->paginate(15)->withQueryString(); $real['paginator']=$real['aiContents']; break;
            case 'agora_history':
                $title='سجل Agora'; $q=class_exists(\App\Models\AgoraHistory::class)?\App\Models\AgoraHistory::orderBy('id','desc'):\App\Models\Session::orderBy('id','desc'); if($search!=='') $q->where('id',$search); $real['agoraHistory']=$q->paginate(15)->withQueryString(); $real['paginator']=$real['agoraHistory']; break;
            case 'forum_topics':
                $title='مواضيع المنتدى'; $q=\App\Models\ForumTopic::with(['forum','creator'])->orderBy('id','desc'); if($search!=='') $q->where('id',$search)->orWhere('title','like',"%{$search}%"); $real['forumTopics']=$q->paginate(15)->withQueryString(); $real['paginator']=$real['forumTopics']; break;
            case 'forum_reports':
                $title='بلاغات المنتدى'; $q=class_exists(\App\Models\ForumTopicReport::class)?\App\Models\ForumTopicReport::orderBy('id','desc'):\App\Models\CommentReport::orderBy('id','desc'); $real['forumReports']=$q->paginate(15)->withQueryString(); $real['paginator']=$real['forumReports']; break;
            case 'forum_settings':
                $title='إعدادات المنتدى'; $real['forumSettings']=\App\Models\Setting::where('name','like','%forum%')->get(); break;
            default:
                $meta=AdminMockData::stubMeta('system', $section);
                $title=$meta['stubTitle'] ?? $section;
                $real=$meta;
                break;
        }
        $data=array_merge($shell,$real,['stubTitle'=>$title,'pageTitle'=>$title]);

        $realSections = ['settings','roles','access','groups','badges','custom-badges','instructor-requests','delete-requests','ip','users','tickets','reports','contact','consultations','forums','notifications','import','support_departments','notification_templates','region','login_history','not_access','ai_contents','agora_history','forum_topics','forum_reports','forum_settings'];
        $view = in_array($section, $realSections) ? 'panel_v1.admin.pages.system.section-real' : 'panel_v1.admin.pages.system.stub';
        if (!view()->exists($view)) $view='panel_v1.admin.pages.system.stub';

        return $this->renderAdmin($request,$view,$data['stubTitle'],$data);
    }

    public function exportUsers(Request $request)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $q = \App\User::orderBy('id','desc');
        if($s=$request->input('search')) $q->where(function($qq)use($s){$qq->where('id',$s)->orWhere('full_name','like',"%{$s}%")->orWhere('email','like',"%{$s}%");});
        $users = $q->limit(1000)->get();
        return Excel::download(new UsersExport($users), 'users-'.date('Y-m-d').'.xlsx');
    }

    public function createUser(Request $request)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $roles = \App\Models\Role::orderBy('created_at','desc')->get();
        $userGroups = \App\Models\Group::where('status','active')->orderBy('created_at','desc')->get();
        return $this->renderAdmin($request, 'panel_v1.admin.pages.system.user-form', 'إضافة مستخدم جديد', array_merge(AdminMockData::shell('system','home'), [
            'roles'=>$roles,
            'userGroups'=>$userGroups,
            'formAction'=>route('panel.v1.admin.system.users.store'),
        ]));
    }

    public function storeUser(Request $request)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;

        $data = $request->all();
        $emailOrMobile = preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i", $request->input('email_or_mobile')) ? 'email' : 'mobile';
        $request->merge([$emailOrMobile=>$data['email_or_mobile'] ?? null]);

        $request->validate([
            $emailOrMobile => ($emailOrMobile=='mobile') ? 'required|numeric|unique:users' : 'required|string|email|max:255|unique:users',
            'full_name'=>'required|min:3|max:255',
            'role_id'=>'required|exists:roles,id',
            'password'=>'required|string|min:6',
            'status'=>'required|in:active,inactive',
        ]);

        $role = \App\Models\Role::find($request->input('role_id'));
        $referralSettings = function_exists('getReferralSettings') ? getReferralSettings() : null;
        $usersAffiliateStatus = (!empty($referralSettings) && !empty($referralSettings['users_affiliate_status']));

        $newUser = \App\User::create([
            'full_name'=>$request->input('full_name'),
            'role_name'=>$role->name,
            'role_id'=>$role->id,
            $emailOrMobile=>$request->input($emailOrMobile),
            'password'=>\App\User::generatePassword($request->input('password')),
            'status'=>$request->input('status'),
            'affiliate'=>$usersAffiliateStatus,
            'verified'=>true,
            'created_at'=>time(),
        ]);

        if($request->filled('group_id')){
            $group = \App\Models\Group::find($request->input('group_id'));
            if($group){
                \App\Models\GroupUser::create(['group_id'=>$group->id,'user_id'=>$newUser->id,'created_at'=>time()]);
            }
        }

        return redirect()->route('panel.v1.admin.system.home')->with('toast',['title'=>'تم','msg'=>'تم إنشاء المستخدم','type'=>'success']);
    }

    public function editUser(Request $request, int $id)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $editUser=\App\User::findOrFail($id);
        if($editUser->isAdmin()) abort(403,'لا يمكن تعديل مدير');
        $roles=\App\Models\Role::orderBy('created_at','desc')->get();
        return $this->renderAdmin($request,'panel_v1.admin.pages.system.user-edit','تعديل مستخدم',array_merge(AdminMockData::shell('system','home'),[
            'editUser'=>$editUser,'roles'=>$roles,
            'formAction'=>route('panel.v1.admin.system.users.update',['id'=>$editUser->id]),
        ]));
    }
    public function updateUser(Request $request, int $id)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $editUser=\App\User::findOrFail($id);
        if($editUser->isAdmin()) abort(403);
        $request->validate([
            'full_name'=>'required|min:3|max:255',
            'email'=>'nullable|email|unique:users,email,'.$editUser->id,
            'mobile'=>'nullable|numeric|unique:users,mobile,'.$editUser->id,
            'password'=>'nullable|string|min:6',
            'status'=>'required|in:active,inactive',
            'role_id'=>'nullable|exists:roles,id',
        ]);
        $data=$request->all();
        $editUser->full_name=$data['full_name'];
        if(!empty($data['email'])) $editUser->email=$data['email'];
        if(array_key_exists('mobile',$data)) $editUser->mobile=$data['mobile'];
        $editUser->status=$data['status'];
        if(!empty($data['password'])) $editUser->password=\App\User::generatePassword($data['password']);
        if(!empty($data['role_id'])){
            $role=\App\Models\Role::find($data['role_id']);
            if($role){ $editUser->role_name=$role->name; $editUser->role_id=$role->id; }
        }
        if(!empty($data['ban'])&&$data['ban']=='1'){
            $editUser->ban=true;
            $editUser->ban_start_at=!empty($data['ban_start_at'])?strtotime($data['ban_start_at']):time();
            $editUser->ban_end_at=!empty($data['ban_end_at'])?strtotime($data['ban_end_at']):null;
        } else {
            $editUser->ban=false; $editUser->ban_start_at=null; $editUser->ban_end_at=null;
        }
        $editUser->save();
        return redirect()->route('panel.v1.admin.system.home')->with('toast',['title'=>'تم','msg'=>'تم تحديث المستخدم','type'=>'success']);
    }
    public function deleteUser(Request $request, int $id)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $del=\App\User::findOrFail($id);
        if($del->isAdmin()) abort(403);
        $del->delete();
        return back()->with('toast',['title'=>'تم','msg'=>'تم حذف المستخدم','type'=>'success']);
    }
    public function approveInstructorRequest(Request $request, int $id)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $u = \App\User::where('id',$id)->where('role_name','teacher')->where('status','pending')->firstOrFail();
        $u->update(['status'=>'active','updated_at'=>time()]);
        return back()->with('toast',['title'=>'تم','msg'=>'تمت الموافقة على المدرب','type'=>'success']);
    }

    public function rejectInstructorRequest(Request $request, int $id)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        \App\User::where('id',$id)->where('role_name','teacher')->update(['status'=>'inactive','updated_at'=>time()]);
        return back()->with('toast',['title'=>'تم','msg'=>'تم الرفض','type'=>'success']);
    }

    public function createGroup(Request $request)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        return $this->renderAdmin($request,'panel_v1.admin.pages.system.group-form','إنشاء مجموعة',array_merge(AdminMockData::shell('system','groups'),[
            'formAction'=>route('panel.v1.admin.system.groups.store'),
            'group'=>null,
            'userGroups'=>collect(),
            'groupRegistrationPackage'=>null,
        ]));
    }
    public function storeGroup(Request $request)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $request->validate(['name'=>'required','users'=>'nullable|array']);
        $data=$request->all();
        $data['created_at']=time();
        $data['creator_id']=$user->id;
        unset($data['_token']);
        // إنشاء المجموعة بنفس منطق Admin\GroupController::store
        $group = \App\Models\Group::create($data);
        $users = $request->get('users');
        if (!empty($users)) {
            foreach ($users as $userId) {
                if (\App\Models\GroupUser::where('user_id',$userId)->first()) continue;
                \App\Models\GroupUser::create(['group_id'=>$group->id,'user_id'=>$userId,'created_at'=>time()]);
                $notifyOptions=['[u.g.title]'=>$group->name];
                try{ sendNotification('change_user_group',$notifyOptions,$userId); sendNotification('add_to_user_group',$notifyOptions,$userId);}catch(\Throwable $e){}
            }
        }
        return redirect(route('panel.v1.admin.system.groups.edit',['id'=>$group->id]))->with('toast',['title'=>'تم','msg'=>'تم إنشاء المجموعة بنجاح','type'=>'success']);
    }
    public function editGroup(Request $request,int $id)
    {
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $group=\App\Models\Group::findOrFail($id);
        $userGroups=\App\Models\GroupUser::where('group_id',$id)->with(['user'=>fn($q)=>$q->select('id','full_name')])->get();
        return $this->renderAdmin($request,'panel_v1.admin.pages.system.group-form','تعديل مجموعة: '.$group->name,array_merge(AdminMockData::shell('system','groups'),[
            'group'=>$group,
            'userGroups'=>$userGroups,
            'groupRegistrationPackage'=>$group->groupRegistrationPackage ?? null,
            'formAction'=>route('panel.v1.admin.system.groups.update',['id'=>$group->id]),
        ]));
    }
    public function updateGroup(Request $request,int $id)
    {
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $group=\App\Models\Group::findOrFail($id);
        $request->validate(['name'=>'required','users'=>'nullable|array','percent'=>'nullable']);
        $data=$request->all();
        $this->storeUserCommissionsForGroup($group,$data);
        unset($data['_token'],$data['commissions']);
        $group->update($data);
        $users=$request->get('users');
        $group->groupUsers()->delete();
        if(!empty($users)){
            foreach($users as $userId){
                \App\Models\GroupUser::create(['group_id'=>$group->id,'user_id'=>$userId,'created_at'=>time()]);
                $notifyOptions=['[u.g.title]'=>$group->name];
                try{ sendNotification('change_user_group',$notifyOptions,$userId); sendNotification('add_to_user_group',$notifyOptions,$userId);}catch(\Throwable $e){}
            }
        }
        return redirect(route('panel.v1.admin.system.groups.edit',['id'=>$group->id]))->with('toast',['title'=>'تم','msg'=>'تم تحديث المجموعة','type'=>'success']);
    }
    private function storeUserCommissionsForGroup($group,$data)
    {
        $group->commissions()->delete();
        if(!empty($data['commissions'])){
            $insert=[];
            foreach($data['commissions'] as $source=>$commission){
                if(!empty($commission['type']) && !empty($commission['value'])){
                    $value=$commission['value'];
                    if($commission['type']=='fixed_amount') $value=convertPriceToDefaultCurrency($value);
                    $insert[]=['user_id'=>null,'user_group_id'=>$group->id,'source'=>$source,'type'=>$commission['type'],'value'=>$value];
                }
            }
            if(!empty($insert)) \App\Models\UserCommission::query()->insert($insert);
        }
    }
    public function groupRegistrationPackage(Request $request,int $id)
    {
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $request->validate(['instructors_count'=>'nullable|numeric','students_count'=>'nullable|numeric','courses_capacity'=>'nullable|numeric','courses_count'=>'nullable|numeric','meeting_count'=>'nullable|numeric']);
        $group=\App\Models\Group::findOrFail($id);
        $data=$request->all();
        \App\Models\GroupRegistrationPackage::updateOrCreate(['group_id'=>$group->id],[ 'instructors_count'=>$data['instructors_count']??null,'students_count'=>$data['students_count']??null,'courses_capacity'=>$data['courses_capacity']??null,'courses_count'=>$data['courses_count']??null,'meeting_count'=>$data['meeting_count']??null,'status'=>$data['status'] ?? 'active','created_at'=>time()]);
        return back()->with('toast',['title'=>'تم','msg'=>'تم حفظ باقة التسجيل','type'=>'success']);
    }
    public function deleteGroup(Request $request, int $id)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        \App\Models\Group::find($id)?->delete();
        return redirect()->route('panel.v1.admin.system.section',['section'=>'groups'])->with('toast',['title'=>'تم','msg'=>'تم حذف المجموعة','type'=>'success']);
    }

    public function createBadge(Request $request)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        return $this->renderAdmin($request,'panel_v1.admin.pages.system.badge-form','إنشاء شارة',array_merge(AdminMockData::shell('system','badges'),[
            'formAction'=>route('panel.v1.admin.system.badges.store'),
            'badge'=>null,
        ]));
    }
    public function storeBadge(Request $request)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $request->validate(['title'=>'required','description'=>'required','image'=>'required','type'=>'required','condition'=>'required|array','condition.*'=>'required','score'=>'nullable|integer|min:0']);
        $data=$request->all();
        $badge=\App\Models\Badge::create(['image'=>$data['image'],'type'=>$data['type'],'condition'=>json_encode($data['condition']),'score'=>$data['score']??null,'created_at'=>time()]);
        \App\Models\Translation\BadgeTranslation::updateOrCreate(['badge_id'=>$badge->id,'locale'=>mb_strtolower($data['locale'] ?? app()->getLocale())],['title'=>$data['title'],'description'=>$data['description']]);
        return redirect()->route('panel.v1.admin.system.section',['section'=>'badges'])->with('toast',['title'=>'تم','msg'=>'تم إنشاء الشارة','type'=>'success']);
    }
    public function editBadge(Request $request,int $id)
    {
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $badge=\App\Models\Badge::findOrFail($id);
        $badge->condition=json_decode($badge->condition, true);
        return $this->renderAdmin($request,'panel_v1.admin.pages.system.badge-form','تعديل شارة',array_merge(AdminMockData::shell('system','badges'),[
            'badge'=>$badge,'formAction'=>route('panel.v1.admin.system.badges.update',['id'=>$badge->id]),
        ]));
    }
    public function updateBadge(Request $request,int $id)
    {
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $badge=\App\Models\Badge::findOrFail($id);
        $request->validate(['title'=>'required','description'=>'required','image'=>'required','condition'=>'required|array','condition.*'=>'required','score'=>'nullable|integer|min:0']);
        $data=$request->all();
        $badge->update(['image'=>$data['image'],'condition'=>json_encode($data['condition']),'score'=>$data['score']??null]);
        \App\Models\Translation\BadgeTranslation::updateOrCreate(['badge_id'=>$badge->id,'locale'=>mb_strtolower($data['locale'] ?? app()->getLocale())],['title'=>$data['title'],'description'=>$data['description']]);
        return redirect()->route('panel.v1.admin.system.section',['section'=>'badges'])->with('toast',['title'=>'تم','msg'=>'تم تحديث الشارة','type'=>'success']);
    }
    public function deleteBadge(Request $request, int $id)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        \App\Models\Badge::findOrFail($id)->delete();
        return back()->with('toast',['title'=>'تم','msg'=>'تم حذف الشارة','type'=>'success']);
    }

    public function confirmDeleteRequest(Request $request, int $id)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $req=\App\Models\DeleteAccountRequest::findOrFail($id);
        $u=\App\User::where('id',$req->user_id)->first();
        if(!empty($u) && !$u->isAdmin()){
            $u->delete();
            try{ \Illuminate\Support\Facades\Storage::disk('public')->deleteDirectory((string)$u->id); }catch(\Throwable $e){}
            $req->delete();
        }
        return back()->with('toast',['title'=>'تم','msg'=>'تم تنفيذ طلب الحذف','type'=>'success']);
    }
    public function rejectDeleteRequest(Request $request, int $id)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        \App\Models\DeleteAccountRequest::where('id',$id)->delete();
        return back()->with('toast',['title'=>'تم','msg'=>'تم رفض طلب الحذف','type'=>'success']);
    }
    public function finishConsultation(Request $request, int $id)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        \App\Models\ReserveMeeting::where('id',$id)->update(['status'=>\App\Models\ReserveMeeting::$finished]);
        return back()->with('toast',['title'=>'تم','msg'=>'تم إنهاء الموعد','type'=>'success']);
    }
    public function cancelConsultation(Request $request, int $id)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $appointment=\App\Models\ReserveMeeting::where('id',$id)->firstOrFail();
        $sale=\App\Models\Sale::where('id',$appointment->sale_id)->whereNull('refund_at')->first();
        if(!empty($sale)){
            \App\Models\Accounting::refundAccounting($sale);
            $sale->update(['refund_at'=>time()]);
        }
        $appointment->update(['status'=>\App\Models\ReserveMeeting::$canceled]);
        return back()->with('toast',['title'=>'تم','msg'=>'تم إلغاء الموعد','type'=>'success']);
    }
    public function validateImport(Request $request)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $request->validate([
            'type'=>'required|in:courses,categories,users,products',
            'csv_file'=>'required|file',
            'locale'=>'required_if:type,courses,products,categories',
            'currency'=>'required_if:type,courses,products',
        ]);
        $data=$request->all();
        $type=$data['type'];
        $csv=\League\Csv\Reader::createFromPath($request->file('csv_file')->getRealPath(),'r');
        $csv->setHeaderOffset(0);
        $importChannel=\App\Mixins\BulkImports\ImportManager::makeChannel($type);
        $validatedItems=[]; $errorsItems=[]; $duplicateRowsLogs=[];
        foreach($csv->getRecords() as $index=>$row){
            $validator=\Illuminate\Support\Facades\Validator::make($row,$importChannel->getValidatorRule());
            if($validator->fails()){ $errorsItems[]=$row; continue; }
            if($type=="users"){ $importChannel->checkDuplicateRows($duplicateRowsLogs,$row,$index); }
            if(!empty($duplicateRowsLogs['errors'])&&!empty($duplicateRowsLogs['errors'][$index])){ $errorsItems[]=$row; }
            else { $validatedItems[]=$row; }
        }
        if(count($validatedItems)) session()->put('valid_items_session',$validatedItems);
        session()->put('invalid_items_count_session',count($errorsItems));
        session()->put('import_type_session',$type);
        session()->put('import_locale_session',$data['locale']??null);
        session()->put('import_currency_session',$data['currency']??null);
        return redirect()->route('panel.v1.admin.system.section',['section'=>'import'])->with('toast',['title'=>'تم','msg'=>'صالح: '.count($validatedItems).' — غير صالح: '.count($errorsItems),'type'=>'success']);
    }
    public function confirmImport(Request $request)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $validItems=session()->get('valid_items_session',[]);
        $invalidCount=session()->get('invalid_items_count_session',0) ?? 0;
        if(!empty($validItems)&&count($validItems)){
            $type=session()->get('import_type_session');
            \App\Mixins\BulkImports\ImportManager::makeChannel($type)->import($validItems,session()->get('import_locale_session'),session()->get('import_currency_session'));
            \App\Models\BulkImport::create(['user_id'=>$user->id,'data_type'=>$type,'valid_items'=>count($validItems),'invalid_items'=>$invalidCount,'created_at'=>time()]);
            session()->forget(['valid_items_session','invalid_items_count_session','import_type_session','import_locale_session','import_currency_session']);
            return redirect()->route('panel.v1.admin.system.section',['section'=>'import'])->with('toast',['title'=>'تم','msg'=>'تم استيراد '.count($validItems).' سجل','type'=>'success']);
        }
        return back()->with('toast',['title'=>'تنبيه','msg'=>'لا توجد سجلات صالحة للاستيراد','type'=>'error']);
    }
    public function downloadImportSample(Request $request)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $type=$request->get('type');
        abort_unless(in_array($type,['courses','categories','users','products']),404);
        $filePath=public_path("/vendor/imports-csv-sample-files/{$type}.csv");
        abort_unless(file_exists($filePath),404);
        return response()->download($filePath,"{$type}.csv",['Content-Type'=>'text/csv']);
    }
    public function deleteConsultation(Request $request, int $id)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        \App\Models\ReserveMeeting::where('id',$id)->delete();
        return back()->with('toast',['title'=>'تم','msg'=>'تم حذف الموعد','type'=>'success']);
    }
    public function deleteSupport(Request $request, int $id)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        \App\Models\Support::where('id',$id)->delete();
        return back()->with('toast',['title'=>'تم','msg'=>'تم حذف التذكرة','type'=>'success']);
    }
    public function editRole(Request $request,int $id)
    {
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $role=\App\Models\Role::findOrFail($id);
        $permissions=\App\Models\Permission::where('role_id',$role->id)->get()->keyBy('section_id');
        $sections=\App\Models\Section::whereNull('section_group_id')->with('children')->get();
        return $this->renderAdmin($request,'panel_v1.admin.pages.system.role-form','تعديل دور: '.$role->name,array_merge(AdminMockData::shell('system','roles'),[
            'role'=>$role,'permissions'=>$permissions,'sections'=>$sections,
            'formAction'=>route('panel.v1.admin.system.roles.update',['id'=>$role->id]),
        ]));
    }
    public function updateRole(Request $request,int $id)
    {
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $role=\App\Models\Role::findOrFail($id);
        $request->validate(['caption'=>'required']);
        $data=$request->all();
        $role->update(['is_admin'=>((!empty($data['is_admin'])&&$data['is_admin']=='on')||$role->name==\App\Models\Role::$admin)]);
        \App\Models\Translation\RoleTranslation::updateOrCreate(['role_id'=>$role->id,'locale'=>mb_strtolower($data['locale']??app()->getLocale())],['caption'=>$data['caption']]);
        \App\Models\Permission::where('role_id',$role->id)->delete();
        if(!empty($data['permissions'])) $this->storeRolePermissions($role,$data['permissions']);
        \Illuminate\Support\Facades\Cache::forget('sections');
        return redirect(route('panel.v1.admin.system.roles.edit',['id'=>$role->id]).'?locale='.mb_strtolower($data['locale']??app()->getLocale()))->with('toast',['title'=>'تم','msg'=>'تم تحديث الدور','type'=>'success']);
    }
    private function storeRolePermissions($role,$sections){ $ids=\App\Models\Section::whereIn('id',$sections)->pluck('id'); $perms=[]; foreach($ids as $sid) $perms[]=['role_id'=>$role->id,'section_id'=>$sid,'allow'=>true]; if(!empty($perms)) \App\Models\Permission::insert($perms); }
    public function deleteRole(Request $request,int $id){
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u;
        $role=\App\Models\Role::findOrFail($id);
        if($role->canDelete()){ $role->delete(); $toast=['title'=>'تم','msg'=>'تم حذف الدور','type'=>'success']; } else { $toast=['title'=>'فشل','msg'=>'لا يمكن حذف هذا الدور','type'=>'error']; }
        return redirect()->route('panel.v1.admin.system.section',['section'=>'roles'])->with('toast',$toast);
    }
    public function createRole(Request $request)
    {
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $sections=\App\Models\Section::whereNull('section_group_id')->with('children')->get();
        return $this->renderAdmin($request,'panel_v1.admin.pages.system.role-form','إنشاء دور',array_merge(AdminMockData::shell('system','roles'),[
            'sections'=>$sections,'formAction'=>route('panel.v1.admin.system.roles.store'),
        ]));
    }
    public function storeRole(Request $request)
    {
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $request->validate(['name'=>'required|min:3|max:64|unique:roles,name','caption'=>'required|min:3|max:64|unique:role_translations,caption']);
        $data=$request->all();
        $role=\App\Models\Role::create(['name'=>$data['name'],'is_admin'=>!empty($data['is_admin'])&&$data['is_admin']=='on','created_at'=>time()]);
        \App\Models\Translation\RoleTranslation::updateOrCreate(['role_id'=>$role->id,'locale'=>mb_strtolower($data['locale']??app()->getLocale())],['caption'=>$data['caption']]);
        if($request->has('permissions')) $this->storeRolePermissions($role,$data['permissions']);
        \Illuminate\Support\Facades\Cache::forget('sections');
        return redirect(getAdminPanelUrl("/roles/{$role->id}/edit"))->with('toast',['title'=>'تم','msg'=>'تم إنشاء الدور','type'=>'success']);
    }
    public function saveSetting(Request $request,int $id)
    {
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $setting=\App\Models\Setting::findOrFail($id);
        $request->validate(['value'=>'nullable|string|max:2000']);
        $setting->update(['value'=>$request->input('value'),'updated_at'=>time()]);
        return back()->with('toast',['title'=>'تم','msg'=>'تم حفظ الإعداد','type'=>'success']);
    }
    public function markAllNotificationsRead(Request $request)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $ids = $user->getUnReadNotifications()->pluck('id');
        $existing = \App\Models\NotificationStatus::where('user_id',$user->id)->whereIn('notification_id',$ids)->pluck('notification_id')->all();
        $now = time();
        foreach ($ids->diff($existing) as $nid) {
            \App\Models\NotificationStatus::create(['user_id'=>$user->id,'notification_id'=>$nid,'seen_at'=>$now]);
        }
        return redirect()->route('panel.v1.admin.system.section',['section'=>'notifications'])->with('toast',['title'=>'تم','msg'=>'تم وضع علامة مقروء على جميع الإشعارات','type'=>'success']);
    }
    // — Forums — مطابق Admin\ForumController:88/163
    public function createForum(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $groups=\App\Models\Group::where('status','active')->get(); $roles=\App\Models\Role::all(); return $this->renderAdmin($request,'panel_v1.admin.pages.system.forum-form','منتدى جديد',array_merge(AdminMockData::shell('system','forums'),['userGroups'=>$groups,'roles'=>$roles,'forum'=>null,'subForums'=>collect(),'formAction'=>route('panel.v1.admin.system.forums.store')]));}
    public function storeForum(Request $request){
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u;
        $request->validate(['title'=>'required|min:3|max:255','description'=>'required','icon'=>'required','cover'=>'required']);
        $data=$request->all(); $forum=\App\Models\Forum::create(['slug'=>\App\Models\Forum::makeSlug($data['title']),'icon'=>$data['icon'],'cover'=>$data['cover'],'group_id'=>$data['group_id']??null,'role_id'=>$data['role_id']??null,'status'=>$data['status']??'active','close'=>!empty($data['close'])&&$data['close']==1]);
        \App\Models\Translation\ForumTranslation::updateOrCreate(['forum_id'=>$forum->id,'locale'=>mb_strtolower($data['locale']??app()->getLocale())],['title'=>$data['title'],'description'=>$data['description']]);
        $this->setForumSubForums($forum,$request->get('sub_forums'), !empty($request->get('has_sub'))&&$request->get('has_sub')=='on', $data['locale']??app()->getLocale());
        return redirect()->route('panel.v1.admin.system.forums.edit',['id'=>$forum->id])->with('toast',['title'=>'تم','msg'=>'تم إنشاء المنتدى','type'=>'success']);
    }
    public function editForum(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $forum=\App\Models\Forum::findOrFail($id); $subs=\App\Models\Forum::where('parent_id',$forum->id)->orderBy('order')->get(); $groups=\App\Models\Group::where('status','active')->get(); $roles=\App\Models\Role::all(); return $this->renderAdmin($request,'panel_v1.admin.pages.system.forum-form','تعديل منتدى',array_merge(AdminMockData::shell('system','forums'),['userGroups'=>$groups,'roles'=>$roles,'forum'=>$forum,'subForums'=>$subs,'formAction'=>route('panel.v1.admin.system.forums.update',['id'=>$forum->id])]));}
    public function updateForum(Request $request,int $id){
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $forum=\App\Models\Forum::findOrFail($id);
        $request->validate(['title'=>'required|min:3|max:255','description'=>'required','icon'=>'required','cover'=>'required']);
        $data=$request->all(); $forum->update(['icon'=>$data['icon'],'cover'=>$data['cover'],'group_id'=>$data['group_id']??null,'role_id'=>$data['role_id']??null,'status'=>$data['status']??'active','close'=>!empty($data['close'])&&$data['close']==1]);
        \App\Models\Translation\ForumTranslation::updateOrCreate(['forum_id'=>$forum->id,'locale'=>mb_strtolower($data['locale']??app()->getLocale())],['title'=>$data['title'],'description'=>$data['description']]);
        $this->setForumSubForums($forum,$request->get('sub_forums'), !empty($request->get('has_sub'))&&$request->get('has_sub')=='on', $data['locale']??app()->getLocale());
        return redirect()->route('panel.v1.admin.system.forums.edit',['id'=>$forum->id])->with('toast',['title'=>'تم','msg'=>'تم التحديث','type'=>'success']);
    }
    private function setForumSubForums($forum,$subForums,$has,$locale){
        $order=1; $old=[]; if($has && !empty($subForums)) foreach($subForums as $k=>$sf){ if(is_numeric($k)) $old[]=$k; if(!empty($sf['title'])){ $check=is_numeric($k)?\App\Models\Forum::find($k):null; if(!empty($check)){ $check->update(['order'=>$order,'icon'=>$sf['icon'],'group_id'=>$sf['group_id']??null,'role_id'=>$sf['role_id']??null,'status'=>$sf['status'],'close'=>$forum->close||(!empty($sf['close'])&&$sf['close']==1)]); \App\Models\Translation\ForumTranslation::updateOrCreate(['forum_id'=>$check->id,'locale'=>mb_strtolower($locale)],['title'=>$sf['title'],'description'=>$sf['description']]); } else { $n=\App\Models\Forum::create(['slug'=>\App\Models\Forum::makeSlug($sf['title']),'parent_id'=>$forum->id,'order'=>$order,'icon'=>$sf['icon'],'group_id'=>$sf['group_id']??null,'role_id'=>$sf['role_id']??null,'status'=>$sf['status'],'close'=>$forum->close||(!empty($sf['close'])&&$sf['close']==1)]); \App\Models\Translation\ForumTranslation::updateOrCreate(['forum_id'=>$n->id,'locale'=>mb_strtolower($locale)],['title'=>$sf['title'],'description'=>$sf['description']]); $old[]=$n->id; } $order++; } } \App\Models\Forum::where('parent_id',$forum->id)->whereNotIn('id',$old)->delete();
    }
    public function deleteContact(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\Contact::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    public function deleteReport(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\CommentReport::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    public function deleteForum(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $f=\App\Models\Forum::where('id',$id)->first(); if(!empty($f)){ \App\Models\Forum::where('parent_id',$f->id)->delete(); $f->delete(); } return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    public function deleteNotification(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\Notification::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }

    // ===== Support Departments =====
    public function supportDepartments(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $q=class_exists(\App\Models\SupportDepartment::class)?\App\Models\SupportDepartment::orderBy('id','desc'):\App\Models\Support::orderBy('id','desc'); $p=$q->paginate(15)->withQueryString(); return $this->renderAdmin($request,'panel_v1.admin.pages.system.section-real','أقسام الدعم',array_merge(AdminMockData::shell('system','support_departments'),['supportDepartments'=>$p,'paginator'=>$p,'stubTitle'=>'أقسام الدعم'])); }
    public function createSupportDepartment(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; return $this->renderAdmin($request,'panel_v1.admin.pages.system.support-department-form','قسم دعم جديد',array_merge(AdminMockData::shell('system','support_departments'),['department'=>null,'formAction'=>route('panel.v1.admin.system.support-departments.store')])); }
    public function storeSupportDepartment(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $request->validate(['title'=>'required']); $data=$request->all(); if(class_exists(\App\Models\SupportDepartment::class)) \App\Models\SupportDepartment::create(['title'=>$data['title']]); return redirect()->route('panel.v1.admin.system.section',['section'=>'support_departments'])->with('toast',['title'=>'تم','msg'=>'تم الإنشاء','type'=>'success']); }
    public function deleteSupportDepartment(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; if(class_exists(\App\Models\SupportDepartment::class)) \App\Models\SupportDepartment::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }

    // ===== Notification Templates =====
    public function notificationTemplates(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $q=\App\Models\NotificationTemplate::orderBy('id','desc'); $p=$q->paginate(15)->withQueryString(); return $this->renderAdmin($request,'panel_v1.admin.pages.system.section-real','قوالب الإشعارات',array_merge(AdminMockData::shell('system','notification_templates'),['notificationTemplates'=>$p,'paginator'=>$p,'stubTitle'=>'قوالب الإشعارات'])); }
    public function editNotificationTemplate(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $t=\App\Models\NotificationTemplate::findOrFail($id); return $this->renderAdmin($request,'panel_v1.admin.pages.system.notification-template-form','تعديل قالب',array_merge(AdminMockData::shell('system','notification_templates'),['template'=>$t,'formAction'=>route('panel.v1.admin.system.notification-templates.update',['id'=>$t->id])])); }
    public function updateNotificationTemplate(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $request->validate(['title'=>'required']); $data=$request->all(); \App\Models\NotificationTemplate::where('id',$id)->update(['title'=>$data['title'],'template'=>$data['template']??null]); return redirect()->route('panel.v1.admin.system.notification-templates.edit',['id'=>$id])->with('toast',['title'=>'تم','msg'=>'تم التحديث','type'=>'success']); }
    public function deleteNotificationTemplate(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\NotificationTemplate::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }

    // ===== Region =====
    public function regionList(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $q=\App\Models\Region::orderBy('id','desc'); $p=$q->paginate(15)->withQueryString(); return $this->renderAdmin($request,'panel_v1.admin.pages.system.section-real','المناطق',array_merge(AdminMockData::shell('system','region'),['regions'=>$p,'paginator'=>$p,'stubTitle'=>'المناطق'])); }
    public function createRegion(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $countries=\App\Models\Region::where('type',\App\Models\Region::$country)->get(); return $this->renderAdmin($request,'panel_v1.admin.pages.system.region-form','منطقة جديدة',array_merge(AdminMockData::shell('system','region'),['region'=>null,'countries'=>$countries,'formAction'=>route('panel.v1.admin.system.regions.store')])); }
    public function storeRegion(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $request->validate(['title'=>'required','type'=>'required']); $data=$request->all(); \App\Models\Region::create(['title'=>$data['title'],'type'=>$data['type'],'parent_id'=>$data['parent_id']??null,'geo_center'=>null]); return redirect()->route('panel.v1.admin.system.section',['section'=>'region'])->with('toast',['title'=>'تم','msg'=>'تم الإنشاء','type'=>'success']); }
    public function deleteRegion(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\Region::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }

    // ===== Login History / Not Access / AI / Agora / Forum =====
    public function loginHistory(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $q=\App\Models\UserLoginHistory::with('user')->orderBy('id','desc'); $p=$q->paginate(15)->withQueryString(); return $this->renderAdmin($request,'panel_v1.admin.pages.system.section-real','سجل الدخول',array_merge(AdminMockData::shell('system','login_history'),['loginHistories'=>$p,'paginator'=>$p,'stubTitle'=>'سجل الدخول'])); }
    public function deleteLoginHistory(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\UserLoginHistory::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    public function usersNotAccess(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $q=\App\Models\Sale::where('access_to_purchased_item',false)->with(['buyer','webinar'])->orderBy('id','desc'); $p=$q->paginate(15)->withQueryString(); return $this->renderAdmin($request,'panel_v1.admin.pages.system.section-real','بلا وصول',array_merge(AdminMockData::shell('system','not_access'),['notAccess'=>$p,'paginator'=>$p,'stubTitle'=>'بلا وصول'])); }
    public function enableNotAccess(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\Sale::where('id',$id)->update(['access_to_purchased_item'=>true]); return back()->with('toast',['title'=>'تم','msg'=>'تم التفعيل','type'=>'success']); }
    public function aiContents(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $model=class_exists(\App\Models\AIContent::class)?\App\Models\AIContent::orderBy('id','desc'):\App\Models\Setting::where('name','like','%ai%')->orderBy('id','desc'); $p=$model->paginate(15)->withQueryString(); return $this->renderAdmin($request,'panel_v1.admin.pages.system.section-real','AI',array_merge(AdminMockData::shell('system','ai_contents'),['aiContents'=>$p,'paginator'=>$p,'stubTitle'=>'AI'])); }
    public function deleteAiContent(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; if(class_exists(\App\Models\AIContent::class)) \App\Models\AIContent::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    public function agoraHistory(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $q=class_exists(\App\Models\AgoraHistory::class)?\App\Models\AgoraHistory::orderBy('id','desc'):\App\Models\Session::orderBy('id','desc'); $p=$q->paginate(15)->withQueryString(); return $this->renderAdmin($request,'panel_v1.admin.pages.system.section-real','Agora',array_merge(AdminMockData::shell('system','agora_history'),['agoraHistory'=>$p,'paginator'=>$p,'stubTitle'=>'Agora'])); }
    public function forumTopics(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $q=\App\Models\ForumTopic::with(['forum','creator'])->orderBy('id','desc'); $p=$q->paginate(15)->withQueryString(); return $this->renderAdmin($request,'panel_v1.admin.pages.system.section-real','مواضيع المنتدى',array_merge(AdminMockData::shell('system','forum_topics'),['forumTopics'=>$p,'paginator'=>$p,'stubTitle'=>'مواضيع المنتدى'])); }
    public function deleteForumTopic(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\ForumTopic::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    public function forumSettings(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $settings=\App\Models\Setting::where('name','like','%forum%')->get(); return $this->renderAdmin($request,'panel_v1.admin.pages.system.section-real','إعدادات المنتدى',array_merge(AdminMockData::shell('system','forum_settings'),['forumSettings'=>$settings,'stubTitle'=>'إعدادات المنتدى'])); }

    // ===== P4: Themes / Translator / Update / Licenses =====
    public function themes(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $q=class_exists(\App\Models\Theme::class)?\App\Models\Theme::orderBy('id','desc'):collect([]); if(is_object($q)&&method_exists($q,'paginate')) $p=$q->paginate(15)->withQueryString(); else $p=new \Illuminate\Pagination\LengthAwarePaginator([],0,15); return $this->renderAdmin($request,'panel_v1.admin.pages.system.section-real','الثيمات',array_merge(AdminMockData::shell('system','themes'),['themes'=>$p,'paginator'=>$p,'stubTitle'=>'الثيمات'])); }
    public function translator(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; return $this->renderAdmin($request,'panel_v1.admin.pages.system.section-real','المترجم',array_merge(AdminMockData::shell('system','translator'),['stubTitle'=>'المترجم'])); }
    public function updateSystem(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; return $this->renderAdmin($request,'panel_v1.admin.pages.system.section-real','التحديث',array_merge(AdminMockData::shell('system','update'),['stubTitle'=>'التحديث'])); }
    public function licenses(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; return $this->renderAdmin($request,'panel_v1.admin.pages.system.section-real','التراخيص',array_merge(AdminMockData::shell('system','licenses'),['stubTitle'=>'التراخيص'])); }
}
