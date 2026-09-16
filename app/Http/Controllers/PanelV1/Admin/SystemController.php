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
            [
                'users'=>$userRows,
                'userRows'=>$userRows,
                'userTabs'=>['الكل','الطلاب','المدربون','المنظمات','المشرفون'],
                'paginator'=>$paginator,
                'pagination'=>['from'=>$paginator->firstItem() ?? 0,'to'=>$paginator->lastItem() ?? 0,'total'=>$paginator->total()],
                'stats'=>$counts,
                'pageTitleText'=>'المستخدمين'
            ]
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
                    $q=\App\Models\DeleteAccountRequest::orderBy('id','desc');
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
                $q=\App\Models\Notification::orderBy('id','desc');
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
            default:
                $meta=AdminMockData::stubMeta('system', $section);
                $title=$meta['stubTitle'] ?? $section;
                $real=$meta;
                break;
        }
        $data=array_merge($shell,$real,['stubTitle'=>$title,'pageTitle'=>$title]);

        $realSections = ['settings','roles','access','groups','badges','custom-badges','instructor-requests','delete-requests','ip','users','tickets','reports','contact','consultations','forums','notifications','import'];
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
        ]));
    }
    public function storeGroup(Request $request)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $request->validate(['name'=>'required|string|max:255','discount'=>'nullable|numeric|min:0|max:100','status'=>'required|in:active,inactive']);
        $data=$request->all();
        $data['created_at']=time();
        $data['creator_id']=$user->id;
        unset($data['_token']);
        \App\Models\Group::create($data);
        return redirect()->route('panel.v1.admin.system.section',['section'=>'groups'])->with('toast',['title'=>'تم','msg'=>'تم إنشاء المجموعة','type'=>'success']);
    }
    public function deleteGroup(Request $request, int $id)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        \App\Models\Group::where('id',$id)->delete();
        return back()->with('toast',['title'=>'تم','msg'=>'تم حذف المجموعة','type'=>'success']);
    }

    public function createBadge(Request $request)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        return $this->renderAdmin($request,'panel_v1.admin.pages.system.badge-form','إنشاء شارة',array_merge(AdminMockData::shell('system','badges'),[
            'formAction'=>route('panel.v1.admin.system.badges.store'),
        ]));
    }
    public function storeBadge(Request $request)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $request->validate(['title'=>'required|string|max:255','description'=>'required|string','type'=>'required|string|max:100','score'=>'nullable|integer|min:0']);
        $data=$request->all();
        $badge=\App\Models\Badge::create([
            'image'=>$data['image'] ?? '/assets/default/img/badge.png',
            'type'=>$data['type'],
            'score'=>$data['score'] ?? null,
            'created_at'=>time(),
        ]);
        \App\Models\Translation\BadgeTranslation::updateOrCreate(['badge_id'=>$badge->id,'locale'=>mb_strtolower($data['locale']??app()->getLocale())],['title'=>$data['title'],'description'=>$data['description']]);
        return redirect()->route('panel.v1.admin.system.section',['section'=>'badges'])->with('toast',['title'=>'تم','msg'=>'تم إنشاء الشارة','type'=>'success']);
    }
    public function deleteBadge(Request $request, int $id)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        \App\Models\Badge::where('id',$id)->delete();
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
    public function deleteRole(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\Role::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    public function createRole(Request $request)
    {
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        return $this->renderAdmin($request,'panel_v1.admin.pages.system.role-form','إنشاء دور',array_merge(AdminMockData::shell('system','roles'),[
            'formAction'=>route('panel.v1.admin.system.roles.store'),
        ]));
    }
    public function storeRole(Request $request)
    {
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $request->validate(['name'=>'required|min:3|max:64|unique:roles,name','caption'=>'required|min:3|max:64']);
        $data=$request->all();
        $role=\App\Models\Role::create(['name'=>$data['name'],'is_admin'=>!empty($data['is_admin'])&&$data['is_admin']=='on','created_at'=>time()]);
        \App\Models\Translation\RoleTranslation::updateOrCreate(['role_id'=>$role->id,'locale'=>mb_strtolower($data['locale']??app()->getLocale())],['caption'=>$data['caption']]);
        \Illuminate\Support\Facades\Cache::forget('sections');
        return redirect()->route('panel.v1.admin.system.section',['section'=>'roles'])->with('toast',['title'=>'تم','msg'=>'تم إنشاء الدور','type'=>'success']);
    }
    public function saveSetting(Request $request,int $id)
    {
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $setting=\App\Models\Setting::findOrFail($id);
        $request->validate(['value'=>'nullable|string|max:2000']);
        $setting->update(['value'=>$request->input('value'),'updated_at'=>time()]);
        return back()->with('toast',['title'=>'تم','msg'=>'تم حفظ الإعداد','type'=>'success']);
    }
    public function deleteContact(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\Contact::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    public function deleteReport(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\CommentReport::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    public function deleteForum(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\Forum::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    public function deleteNotification(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\Notification::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
}
