<?php

namespace App\Http\Controllers\PanelV1\Admin;

use App\Exports\salesExport;
use App\Http\Controllers\PanelV1\AdminController;
use App\Http\Controllers\PanelV1\Support\AdminMockData;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SalesController extends AdminController
{
    public function home(Request $request)
    {
        $search = trim((string)$request->input('search',''));
        $q = \App\Models\Sale::with(['buyer','seller','webinar'])->whereNull('refund_at')->orderBy('id','desc');
        if($search !== ''){
            $q->where(function($qq) use ($search){
                $qq->where('id', $search);
                $qq->orWhereHas('buyer', fn($b)=>$b->where('full_name','like',"%{$search}%")->orWhere('email','like',"%{$search}%"));
            });
        }
        $paginator = $q->paginate(15)->withQueryString();
        $rows = collect($paginator->items())->map(fn($s)=>[
            'id'=>'#'.$s->id,
            'student'=> $s->buyer->full_name ?? '—',
            'student_email'=> $s->buyer->email ?? '',
            'instructor'=> $s->seller->full_name ?? $s->webinar->teacher->full_name ?? '—',
            'service'=> $s->webinar->title ?? $s->type,
            'price'=> handlePrice($s->amount),
            'discount'=> handlePrice($s->discount),
            'vat'=> handlePrice($s->tax ?? 0),
            'type'=> $s->type==='webinar' ? 'دورة' : $s->type,
            'date'=> date('Y/m/d',(int)$s->created_at),
            'status'=> empty($s->refund_at) ? 'مكتمل' : 'مسترد',
        ])->all();
        return $this->renderAdmin(
            $request,
            'panel_v1.admin.pages.sales.sales-list',
            'قائمة المبيعات',
            [
                'salesRows'=>$rows,
                'paginator'=>$paginator,
                'pagination'=>['from'=>$paginator->firstItem() ?? 0,'to'=>$paginator->lastItem() ?? 0,'total'=>$paginator->total()],
                'pageTitleText'=>'قائمة المبيعات',
                'pageSubtitle'=> $paginator->total().' عملية',
                'stats'=>[
                    ['label'=>'إجمالي المبيعات','value'=>handlePrice(\App\Models\Sale::whereNull('refund_at')->sum('total_amount'))],
                    ['label'=>'عدد العمليات','value'=>(string)\App\Models\Sale::whereNull('refund_at')->count()],
                ],
            ]
        );
    }

    public function section(Request $request, string $section)
    {
        $shell = AdminMockData::shell('sales', $section);
        $real=[]; $title=$section;
        $search = trim((string)$request->input('search',''));
        switch($section){
            case 'payouts':
                $title='طلبات السحب';
                $q = \App\Models\Payout::with(['user'])->orderBy('id','desc');
                if($search !== '') $q->where('id',$search)->orWhereHas('user', fn($u)=>$u->where('full_name','like',"%{$search}%"));
                $p = $q->paginate(15)->withQueryString();
                $real['payouts'] = $p->getCollection()->map(fn($pp)=>[
                    'id'=>'#'.$pp->id,'user'=>$pp->user->full_name ?? '','amount'=>handlePrice($pp->amount),'status'=>$pp->status,'date'=>date('Y/m/d',(int)$pp->created_at)
                ])->all();
                $real['payoutPaginator'] = $p;
                $real['paginator'] = $p;
                break;
            case 'sales':
                return $this->home($request);
            case 'budget':
                $title='الميزانية';
                $real['budget']=['total'=>handlePrice(\App\Models\Sale::whereNull('refund_at')->sum('total_amount')),'count'=>\App\Models\Sale::whereNull('refund_at')->count()];
                break;
            case 'offline':
                $title='المدفوعات دون اتصال';
                $q = \App\Models\OfflinePayment::orderBy('id','desc');
                if($search !== '') $q->where('id',$search);
                $real['offlinePayments']=$q->paginate(15)->withQueryString();
                $real['paginator']=$real['offlinePayments'];
                break;
            case 'subscriptions':
                $title='خطط الاشتراك';
                $q = \App\Models\Subscribe::orderBy('id','desc');
                if($search !== '') $q->where('id',$search)->orWhere('title','like',"%{$search}%");
                $real['subscriptions']=$q->paginate(15)->withQueryString();
                $real['paginator']=$real['subscriptions'];
                break;
            case 'installments':
                $title='التقسيط';
                $q = \App\Models\InstallmentOrder::orderBy('id','desc');
                if($search !== '') $q->where('id',$search);
                $real['installments']=$q->paginate(15)->withQueryString();
                $real['paginator']=$real['installments'];
                break;
            case 'meetings':
                $title='باقات الاجتماعات';
                if(class_exists(\App\Models\Meeting::class)){
                    $q = \App\Models\Meeting::orderBy('id','desc');
                    if($search !== '') $q->where('id',$search);
                    $real['meetings']=$q->paginate(15)->withQueryString();
                    $real['paginator']=$real['meetings'];
                } else { $real['meetings']=collect(); }
                break;
            case 'documents':
                $title='المستندات والأرصدة';
                $q = \App\Models\Accounting::with(['user'])->orderBy('id','desc');
                if($search !== '') $q->where('id',$search);
                $real['documents']=$q->paginate(15)->withQueryString();
                $real['paginator']=$real['documents'];
                break;
            case 'packages':
                $title='الباقات';
                $q = \App\Models\RegistrationPackage::orderBy('id','desc');
                if($search !== '') $q->where('id',$search)->orWhere('title','like',"%{$search}%");
                $real['packages']=$q->paginate(15)->withQueryString();
                $real['paginator']=$real['packages'];
                break;
            default:
                $meta=AdminMockData::stubMeta('sales', $section);
                $title=$meta['stubTitle'] ?? $section;
                $real=$meta;
                break;
        }
        $data=array_merge($shell,$real,['stubTitle'=>$title,'pageTitle'=>$title]);

        $realSections = ['payouts','budget','offline','subscriptions','installments','packages','meetings','sales','documents'];
        $view = in_array($section, $realSections) ? 'panel_v1.admin.pages.sales.section-real' : 'panel_v1.admin.pages.sales.stub';
        if (!view()->exists($view)) $view='panel_v1.admin.pages.sales.stub';

        return $this->renderAdmin($request,$view,$data['stubTitle'],$data);
    }

    public function exportSales(Request $request)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $q = \App\Models\Sale::with(['buyer','webinar'])->whereNull('refund_at')->orderBy('id','desc');
        if($s=$request->input('search')) $q->where('id',$s);
        $sales = $q->limit(1000)->get();
        return Excel::download(new salesExport($sales), 'sales-'.date('Y-m-d').'.xlsx');
    }

    public function approvePayout(Request $request, int $id)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        \App\Models\Payout::where('id',$id)->update(['status'=>'done','updated_at'=>time()]);
        return back()->with('toast',['title'=>'تم','msg'=>'تمت الموافقة على السحب','type'=>'success']);
    }

    public function rejectPayout(Request $request, int $id)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        \App\Models\Payout::where('id',$id)->update(['status'=>'rejected','updated_at'=>time()]);
        return back()->with('toast',['title'=>'تم','msg'=>'تم رفض السحب','type'=>'success']);
    }

    public function approveOffline(Request $request,int $id){
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u;
        $op=\App\Models\OfflinePayment::findOrFail($id);
        \App\Models\Accounting::create([
            'creator_id'=>$u->id,'user_id'=>$op->user_id,'amount'=>$op->amount,
            'type'=>\App\Models\Accounting::$addiction,'type_account'=>\App\Models\Accounting::$asset,
            'description'=>'تأكيد دفعة دون اتصال','created_at'=>time(),
        ]);
        $op->update(['status'=>\App\Models\OfflinePayment::$approved]);
        try{ sendNotification('offline_payment_approved',['[amount]'=>handlePrice($op->amount)],$op->user_id); }catch(\Throwable $e){}
        return back()->with('toast',['title'=>'تم','msg'=>'تم تأكيد الدفعة وشحن المحفظة','type'=>'success']);
    }
    public function rejectOffline(Request $request,int $id){
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u;
        $op=\App\Models\OfflinePayment::findOrFail($id);
        $op->update(['status'=>\App\Models\OfflinePayment::$reject]);
        try{ sendNotification('offline_payment_rejected',['[amount]'=>handlePrice($op->amount)],$op->user_id); }catch(\Throwable $e){}
        return back()->with('toast',['title'=>'تم','msg'=>'تم رفض الدفعة','type'=>'success']);
    }
    public function deleteOffline(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\OfflinePayment::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    public function deleteSubscription(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\Subscribe::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    public function deleteInstallment(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\InstallmentOrder::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    public function deletePackage(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\RegistrationPackage::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
}
