<?php

namespace App\Http\Controllers\PanelV1\Admin;

use App\Exports\salesExport;
use App\Http\Controllers\PanelV1\AdminController;
use App\Http\Controllers\PanelV1\Support\AdminMockData;
use App\Models\Accounting;
use App\Models\Order;
use App\Models\ReserveMeeting;
use App\Models\Sale;
use App\Models\SaleLog;
use App\Models\Webinar;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class SalesController extends AdminController
{
    public function home(Request $request)
    {
        $query = Sale::whereNull('product_order_id');
        $totalSales = ['count'=> (clone $query)->count(), 'amount'=> (clone $query)->sum('total_amount')];
        $classesSales = ['count'=> (clone $query)->whereNotNull('webinar_id')->count(), 'amount'=> (clone $query)->whereNotNull('webinar_id')->sum('total_amount')];
        $appointmentSales = ['count'=> (clone $query)->whereNotNull('meeting_id')->count(), 'amount'=> (clone $query)->whereNotNull('meeting_id')->sum('total_amount')];
        $failedSales = Order::where('status', Order::$fail)->count();

        $salesQuery = $this->getSalesFilters($query, $request);
        $paginator = $salesQuery->orderBy('created_at','desc')->with(['buyer','webinar','meeting','subscribe','promotion'])->paginate(15)->withQueryString();
        foreach($paginator as $sale){
            $this->makeSaleTitle($sale);
            if(empty($sale->saleLog)){
                try{ SaleLog::create(['sale_id'=>$sale->id,'viewed_at'=>time()]); }catch(\Throwable $e){}
            }
        }
        $rows = collect($paginator->items())->map(fn($s)=>[
            'id'=>'#'.$s->id,
            'student'=> $s->buyer->full_name ?? '—',
            'student_email'=> $s->buyer->email ?? '',
            'instructor'=> $s->item_seller ?? '—',
            'service'=> $s->item_title ?? $s->type,
            'price'=> handlePrice($s->amount),
            'discount'=> handlePrice($s->discount),
            'vat'=> handlePrice($s->tax ?? 0),
            'type'=> $s->type==='webinar' ? 'دورة' : $s->type,
            'date'=> date('Y/m/d',(int)$s->created_at),
            'status'=> empty($s->refund_at) ? 'مكتمل' : 'مسترد',
        ])->all();

        $teacherIds = $request->get('teacher_ids');
        $studentIds = $request->get('student_ids');
        $teachers = !empty($teacherIds) ? User::select('id','full_name')->whereIn('id', (array)$teacherIds)->get() : collect();
        $students = !empty($studentIds) ? User::select('id','full_name')->whereIn('id', (array)$studentIds)->get() : collect();

        return $this->renderAdmin($request,'panel_v1.admin.pages.sales.sales-list','قائمة المبيعات',array_merge(AdminMockData::shell('sales','sales-list'),[
            'salesRows'=>$rows,'paginator'=>$paginator,'pagination'=>['from'=>$paginator->firstItem()??0,'to'=>$paginator->lastItem()??0,'total'=>$paginator->total()],
            'pageTitleText'=>'قائمة المبيعات','pageSubtitle'=> $paginator->total().' عملية',
            'stats'=>[
                ['label'=>'إجمالي المبيعات','value'=>handlePrice($totalSales['amount'])],
                ['label'=>'مبيعات الدورات','value'=>handlePrice($classesSales['amount'])],
                ['label'=>'مبيعات المواعيد','value'=>handlePrice($appointmentSales['amount'])],
                ['label'=>'فشل','value'=>(string)$failedSales],
            ],
            'teachers'=>$teachers,'students'=>$students,
            'totalSales'=>$totalSales,'classesSales'=>$classesSales,'appointmentSales'=>$appointmentSales,'failedSales'=>$failedSales,
        ]));
    }

    private function makeSaleTitle($sale){
        if(!empty($sale->webinar_id) || !empty($sale->bundle_id)){
            $item = !empty($sale->webinar_id) ? $sale->webinar : $sale->bundle;
            $sale->item_title = $item ? $item->title : trans('update.deleted_item');
            $sale->item_id = $item ? $item->id : '';
            $sale->item_seller = ($item && $item->creator) ? $item->creator->full_name : trans('update.deleted_user');
            $sale->seller_id = ($item && $item->creator) ? $item->creator->id : '';
        } elseif(!empty($sale->event_ticket_id)){
            $item = $sale->eventTicket; $event = $item?->event;
            $sale->item_title = $item ? $item->title : trans('update.deleted_item');
            $sale->item_id = $item ? $item->id : '';
            $sale->item_seller = ($event && $event->creator) ? $event->creator->full_name : trans('update.deleted_user');
        } elseif(!empty($sale->meeting_package_id)){
            $item = $sale->meetingPackage;
            $sale->item_title = $item ? $item->title : trans('update.deleted_item');
            $sale->item_id = $item ? $item->id : '';
            $sale->item_seller = ($item && $item->creator) ? $item->creator->full_name : trans('update.deleted_user');
        } elseif(!empty($sale->meeting_id)){
            $sale->item_title = trans('panel.meeting'); $sale->item_id=$sale->meeting_id;
            $sale->item_seller = ($sale->meeting && $sale->meeting->creator) ? $sale->meeting->creator->full_name : trans('update.deleted_user');
        } elseif(!empty($sale->subscribe_id)){
            $sale->item_title = !empty($sale->subscribe) ? $sale->subscribe->title : trans('update.deleted_subscribe');
            $sale->item_id=$sale->subscribe_id; $sale->item_seller='Admin';
        } elseif(!empty($sale->promotion_id)){
            $sale->item_title = !empty($sale->promotion) ? $sale->promotion->title : trans('update.deleted_promotion');
            $sale->item_id=$sale->promotion_id; $sale->item_seller='Admin';
        } elseif(!empty($sale->registration_package_id)){
            $sale->item_title = !empty($sale->registrationPackage) ? $sale->registrationPackage->title : 'Deleted registration Package';
            $sale->item_id=$sale->registration_package_id; $sale->item_seller='Admin';
        } elseif(!empty($sale->gift_id) && !empty($sale->gift)){
            $gift=$sale->gift; $item=!empty($gift->webinar_id)?$gift->webinar:(!empty($gift->bundle_id)?$gift->bundle:$gift->product);
            $sale->item_title=$gift->getItemTitle(); $sale->item_id=$item->id ?? '';
            $sale->item_seller=!empty($item->creator)?$item->creator->full_name:trans('update.deleted_user');
        } elseif(!empty($sale->installment_payment_id) && !empty($sale->installmentOrderPayment)){
            $pay=$sale->installmentOrderPayment; $order=$pay->installmentOrder; $item=$order?->getItem();
            $sale->item_title=!empty($item)?$item->title:'--'; $sale->item_id=!empty($item)?$item->id:'--';
            $sale->item_seller=(!empty($item)&&!empty($item->creator))?$item->creator->full_name:trans('update.deleted_user');
        } else { $sale->item_title='---'; $sale->item_id='---'; $sale->item_seller='---'; }
        return $sale;
    }

    private function getSalesFilters($query, $request){
        $itemTitle=$request->get('item_title');
        $from=$request->get('from'); $to=$request->get('to'); $status=$request->get('status');
        $webinarIds=$request->get('webinar_ids',[]); $teacherIds=$request->get('teacher_ids',[]); $studentIds=$request->get('student_ids',[]);
        $userIds=array_merge((array)$teacherIds,(array)$studentIds);
        if(!empty($itemTitle)){ $ids=Webinar::whereTranslationLike('title', "%$itemTitle%")->pluck('id')->toArray(); $webinarIds=array_merge((array)$webinarIds,$ids); }
        $query=fromAndToDateFilter($from,$to,$query,'created_at');
        if(!empty($status)){
            if($status=='success') $query->whereNull('refund_at');
            elseif($status=='refund') $query->whereNotNull('refund_at');
            elseif($status=='blocked') $query->where('access_to_purchased_item', false);
        }
        if(!empty($webinarIds) && count($webinarIds)) $query->whereIn('webinar_id',$webinarIds);
        if(!empty($userIds) && count($userIds)) $query->where(function($q)use($userIds){ $q->whereIn('buyer_id',$userIds)->orWhereIn('seller_id',$userIds); });
        $search=trim((string)$request->input('search',''));
        if($search!==''){
            $query->where(function($qq)use($search){
                $qq->where('id',$search);
                $qq->orWhereHas('buyer', fn($b)=>$b->where('full_name','like',"%{$search}%")->orWhere('email','like',"%{$search}%"));
            });
        }
        return $query;
    }

    public function refund(Request $request,int $id){
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $sale=Sale::findOrFail($id);
        if($sale->type==Sale::$subscribe){
            $withSub=Sale::whereNotNull('webinar_id')->where('buyer_id',$sale->buyer_id)->where('subscribe_id',$sale->subscribe_id)->whereNull('refund_at')->with('webinar','subscribe')->get();
            foreach($withSub as $s){ $s->update(['refund_at'=>time()]); if(!empty($s->webinar)&&!empty($s->subscribe)) Accounting::refundAccountingForSaleWithSubscribe($s->webinar,$s->subscribe); }
        }
        if(!empty($sale->total_amount)) Accounting::refundAccounting($sale);
        if(!empty($sale->meeting_id) && $sale->type==Sale::$meeting){
            $app=ReserveMeeting::where('meeting_id',$sale->meeting_id)->where('sale_id',$sale->id)->first();
            if(!empty($app)) $app->update(['status'=>ReserveMeeting::$canceled]);
        }
        $sale->update(['refund_at'=>time()]);
        return back()->with('toast',['title'=>'تم','msg'=>'تم الاسترجاع','type'=>'success']);
    }

    public function invoice(Request $request,int $id){
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $sale=Sale::where('id',$id)->with(['order','buyer'=>fn($q)=>$q->select('id','full_name'),'webinar'=>fn($q)=>$q->with(['teacher'=>fn($q)=>$q->select('id','full_name'),'creator'=>fn($q)=>$q->select('id','full_name')]),'bundle'])->first();
        if(!empty($sale)){
            $webinar=$sale->webinar ?? $sale->bundle;
            if(!empty($webinar)) return view('admin.financial.sales.invoice',['pageTitle'=>trans('webinars.invoice_page_title'),'sale'=>$sale,'webinar'=>$webinar]);
        }
        abort(404);
    }

    public function section(Request $request, string $section)
    {
        $shell = AdminMockData::shell('sales', $section);
        $real=[]; $title=$section;
        $search = trim((string)$request->input('search',''));
        switch($section){
            case 'sales-list':
                return $this->home($request);
            case 'payouts':
                $title='طلبات السحب';
                $payoutType=$request->get('payout','requests');
                $q=\App\Models\Payout::query();
                if($payoutType=='requests') $q->where('status', \App\Models\Payout::$waiting); else $q->where('status','!=', \App\Models\Payout::$waiting);
                $q=$this->payoutFilters($q,$request);
                $p=$q->paginate(15)->withQueryString();
                $real['payouts']=$p->getCollection()->map(fn($pp)=>['id'=>'#'.$pp->id,'user'=>$pp->user->full_name ?? '','amount'=>handlePrice($pp->amount),'status'=>$pp->status,'date'=>date('Y/m/d',(int)$pp->created_at)])->all();
                $real['payoutPaginator']=$p; $real['paginator']=$p;
                $real['roles']=\App\Models\Role::all();
                $real['offlineBanks']=\App\Models\OfflineBank::orderBy('created_at','desc')->with(['specifications'])->get();
                $real['payoutType']=$payoutType;
                break;
            case 'sales': return $this->home($request);
            case 'budget':
                $title='الميزانية';
                $real['budget']=['total'=>handlePrice(Sale::whereNull('refund_at')->sum('total_amount')),'count'=>Sale::whereNull('refund_at')->count()];
                break;
            case 'offline':
                $title='المدفوعات دون اتصال';
                $pageType=$request->get('page_type','requests');
                $q=\App\Models\OfflinePayment::with(['user']);
                if($pageType=='requests') $q->where('status', \App\Models\OfflinePayment::$waiting); else $q->where('status','!=', \App\Models\OfflinePayment::$waiting);
                $q=$this->offlineFilters($q,$request);
                $real['offlinePayments']=$q->paginate(15)->withQueryString(); $real['offlinePayments']->appends(['page_type'=>$pageType]);
                $real['paginator']=$real['offlinePayments']; $real['pageType']=$pageType;
                $real['roles']=\App\Models\Role::all();
                $real['offlineBanks']=\App\Models\OfflineBank::orderBy('created_at','desc')->with(['specifications'])->get();
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
                $q = \App\Models\MeetingPackage::orderBy('id','desc');
                if($search !== '') $q->where('id',$search)->orWhereHas('translations', fn($t)=>$t->where('title','like',"%{$search}%"));
                $real['meetings']=$q->paginate(15)->withQueryString();
                $real['paginator']=$real['meetings'];
                break;
            case 'packages':
                $title='الباقات';
                $q = \App\Models\RegistrationPackage::orderBy('id','desc');
                if($search !== '') $q->where('id',$search)->orWhere('title','like',"%{$search}%");
                $real['packages']=$q->paginate(15)->withQueryString();
                $real['paginator']=$real['packages'];
                break;
            case 'documents':
                $title='المستندات والأرصدة';
                $q = \App\Models\Accounting::with(['user'])->orderBy('id','desc');
                if($search !== '') $q->where('id',$search);
                $from=$request->get('from'); $to=$request->get('to'); $user=$request->get('user'); $type=$request->get('type'); $typeAccount=$request->get('type_account');
                $q=fromAndToDateFilter($from,$to,$q,'created_at');
                if(!empty($user)) $q->whereIn('user_id', (array)$user);
                if(!empty($type) && $type!=='all') $q->where('type',$type);
                if(!empty($typeAccount) && $typeAccount!=='all') $q->where('type_account',$typeAccount);
                $real['documents']=$q->paginate(15)->withQueryString();
                $real['paginator']=$real['documents'];
                break;
            case 'payment_channels':
                $title='قنوات الدفع';
                $q=\App\Models\PaymentChannel::orderBy('created_at','desc');
                if($search !== '') $q->where('title','like',"%{$search}%")->orWhere('class_name','like',"%{$search}%");
                $real['paymentChannels']=$q->paginate(15)->withQueryString();
                $real['paginator']=$real['paymentChannels'];
                $real['stubTitle']=$title;
                break;
            case 'meeting_packages_sold':
                $title='باقات الاجتماعات المباعة';
                $q=\App\Models\MeetingPackageSold::with(['user','meetingPackage.creator'])->orderBy('id','desc');
                if($search !== '') $q->where('id',$search);
                $from=$request->get('from'); $to=$request->get('to'); $q=fromAndToDateFilter($from,$to,$q,'created_at');
                $real['meetingPackagesSold']=$q->paginate(15)->withQueryString();
                $real['paginator']=$real['meetingPackagesSold'];
                break;
            case 'event_sold_tickets':
                $title='تذاكر الفعاليات المباعة';
                $q=\App\Models\EventSoldTicket::with(['event','user','ticket'])->orderBy('id','desc');
                if($search !== '') $q->where('id',$search);
                $real['eventSoldTickets']=$q->paginate(15)->withQueryString();
                $real['paginator']=$real['eventSoldTickets'];
                break;
            default:
                $meta=AdminMockData::stubMeta('sales', $section);
                $title=$meta['stubTitle'] ?? $section;
                $real=$meta;
                break;
        }
        $data=array_merge($shell,$real,['stubTitle'=>$title,'pageTitle'=>$title]);
        $realSections = ['payouts','budget','offline','subscriptions','installments','packages','meetings','sales','documents','payment_channels','meeting_packages_sold','event_sold_tickets'];
        $view = in_array($section, $realSections) ? 'panel_v1.admin.pages.sales.section-real' : 'panel_v1.admin.pages.sales.stub';
        if (!view()->exists($view)) $view='panel_v1.admin.pages.sales.stub';
        return $this->renderAdmin($request,$view,$data['stubTitle'],$data);
    }

    private function payoutFilters($query,$request){
        $from=$request->get('from'); $to=$request->get('to'); $search=$request->get('search'); $userIds=$request->get('user_ids',[]); $roleId=$request->get('role_id'); $accountType=$request->get('account_type'); $sort=$request->get('sort');
        if(!empty($search)){ $ids=User::where('full_name','like',"%$search%")->pluck('id')->toArray(); $userIds=array_merge((array)$userIds,$ids); }
        if(!empty($roleId)){ $role=\App\Models\Role::where('id',$roleId)->first(); if(!empty($role)){ $ids=$role->users()->pluck('id')->toArray(); $userIds=array_merge((array)$userIds,$ids); } }
        $query=fromAndToDateFilter($from,$to,$query,'created_at');
        if(!empty($userIds) && count($userIds)) $query->whereIn('user_id',$userIds);
        if(!empty($accountType)) $query->where('account_bank_name',$accountType);
        if(!empty($sort)){
            switch($sort){
                case 'amount_asc': $query->orderBy('amount','asc'); break;
                case 'amount_desc': $query->orderBy('amount','desc'); break;
                case 'created_at_asc': $query->orderBy('created_at','asc'); break;
                case 'created_at_desc': $query->orderBy('created_at','desc'); break;
            }
        } else $query->orderBy('created_at','desc');
        return $query;
    }

    private function offlineFilters($query,$request){
        $from=$request->get('from'); $to=$request->get('to'); $search=$request->get('search'); $userIds=$request->get('user_ids',[]); $roleId=$request->get('role_id'); $accountType=$request->get('account_type'); $sort=$request->get('sort'); $status=$request->get('status');
        if(!empty($search)){ $ids=User::where('full_name','like',"%$search%")->pluck('id')->toArray(); $userIds=array_merge((array)$userIds,$ids); }
        if(!empty($roleId)){ $role=\App\Models\Role::where('id',$roleId)->first(); if(!empty($role)){ $ids=$role->users()->pluck('id')->toArray(); $userIds=array_merge((array)$userIds,$ids); } }
        $query=fromAndToDateFilter($from,$to,$query,'created_at');
        if(!empty($userIds) && count($userIds)) $query->whereIn('user_id',$userIds);
        if(!empty($accountType)) $query->where('offline_bank_id',$accountType);
        if(!empty($status)) $query->where('status',$status);
        if(!empty($sort)){
            switch($sort){
                case 'amount_asc': $query->orderBy('amount','asc'); break;
                case 'amount_desc': $query->orderBy('amount','desc'); break;
                case 'pay_date_asc': $query->orderBy('pay_date','asc'); break;
                case 'pay_date_desc': $query->orderBy('pay_date','desc'); break;
            }
        } else $query->orderBy('created_at','desc');
        return $query;
    }

    public function exportSales(Request $request)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $q = Sale::query();
        $salesQuery=$this->getSalesFilters($q,$request);
        $sales=$salesQuery->orderBy('created_at','desc')->with(['buyer','webinar','meeting','subscribe','promotion'])->get();
        foreach($sales as $s) $this->makeSaleTitle($s);
        return Excel::download(new salesExport($sales), 'sales-'.date('Y-m-d').'.xlsx');
    }

    public function payout(Request $request,int $id){
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $payout=\App\Models\Payout::findOrFail($id);
        $financial=getFinancialSettings();
        if($payout->user->getPayout() < ($financial['minimum_payout'] ?? 0)) return back()->with('msg', trans('public.income_los_then_minimum_payout'));
        Accounting::create(['creator_id'=>auth()->user()->id,'user_id'=>$payout->user_id,'amount'=>$payout->amount,'type'=>Accounting::$deduction,'type_account'=>Accounting::$income,'description'=>trans('financial.payout_request'),'created_at'=>time()]);
        sendNotification('payout_proceed',['[payout.amount]'=>$payout->amount,'[payout.account]'=>$payout->account_bank_name],$payout->user_id);
        $payout->update(['status'=>\App\Models\Payout::$done]);
        return back()->with('toast',['title'=>'تم','msg'=>'تمت الموافقة ودفع المبلغ','type'=>'success']);
    }

    public function approvePayout(Request $request, int $id){ return $this->payout($request,$id); }

    public function rejectPayout(Request $request, int $id)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        \App\Models\Payout::findOrFail($id)->update(['status'=>\App\Models\Payout::$reject]);
        return back()->with('toast',['title'=>'تم','msg'=>'تم رفض السحب','type'=>'success']);
    }

    public function exportPayouts(Request $request){
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $payoutType=$request->get('payout','requests');
        $q=\App\Models\Payout::query();
        if($payoutType=='requests') $q->where('status', \App\Models\Payout::$waiting); else $q->where('status','!=', \App\Models\Payout::$waiting);
        $payouts=$this->payoutFilters($q,$request)->get();
        return Excel::download(new \App\Exports\PayoutExport($payouts), ($payoutType=='requests'?trans('financial.payouts_requests'):trans('financial.payouts_history')).'.xlsx');
    }

    public function approveOffline(Request $request,int $id){
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u;
        $op=\App\Models\OfflinePayment::findOrFail($id);
        if($op->type === \App\Models\OfflinePayment::$typeCart && !empty($op->order_id)){
            $order=Order::where('id',$op->order_id)->where('user_id',$op->user_id)->first();
            if($order && $order->status !== Order::$paid){
                $order->update(['payment_method'=>Order::$paymentChannel]);
                (new \App\Http\Controllers\Web\PaymentController())->setPaymentAccounting($order);
                $order->update(['status'=>Order::$paid]);
            }
            $op->update(['status'=>\App\Models\OfflinePayment::$approved]);
            try{ sendNotification('offline_payment_approved',['[amount]'=>handlePrice($op->amount)],$op->user_id); }catch(\Throwable $e){}
            return back()->with('toast',['title'=>'تم','msg'=>'تم تأكيد طلب السلة','type'=>'success']);
        }
        Accounting::create(['creator_id'=>$u->id,'user_id'=>$op->user_id,'amount'=>$op->amount,'type'=>Accounting::$addiction,'type_account'=>Accounting::$asset,'description'=>trans('admin/pages/setting.notification_offline_payment_approved'),'created_at'=>time()]);
        $op->update(['status'=>\App\Models\OfflinePayment::$approved]);
        try{ sendNotification('offline_payment_approved',['[amount]'=>handlePrice($op->amount)],$op->user_id); }catch(\Throwable $e){}
        $accReward=\App\Models\RewardAccounting::calculateScore(\App\Models\Reward::ACCOUNT_CHARGE,$op->amount);
        \App\Models\RewardAccounting::makeRewardAccounting($op->user_id,$accReward,\App\Models\Reward::ACCOUNT_CHARGE);
        $walletReward=\App\Models\RewardAccounting::calculateScore(\App\Models\Reward::CHARGE_WALLET,$op->amount);
        \App\Models\RewardAccounting::makeRewardAccounting($op->user_id,$walletReward,\App\Models\Reward::CHARGE_WALLET);
        if(!empty($op->user)){
            $order=new Order(); $order->total_amount=$op->amount; $order->user_id=$op->user_id;
            try{ (new \App\Mixins\Cashback\CashbackAccounting($op->user))->rechargeWallet($order); }catch(\Throwable $e){}
        }
        return back()->with('toast',['title'=>'تم','msg'=>'تم تأكيد الدفعة وشحن المحفظة','type'=>'success']);
    }
    public function rejectOffline(Request $request,int $id){
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u;
        $op=\App\Models\OfflinePayment::findOrFail($id);
        $op->update(['status'=>\App\Models\OfflinePayment::$reject]);
        try{ sendNotification('offline_payment_rejected',['[amount]'=>handlePrice($op->amount)],$op->user_id); }catch(\Throwable $e){}
        return back()->with('toast',['title'=>'تم','msg'=>'تم رفض الدفعة','type'=>'success']);
    }
    public function cartItems(Request $request,int $id){
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u;
        $op=\App\Models\OfflinePayment::with(['order.orderItems'=>fn($q)=>$q->with(['webinar','product','bundle','subscribe','promotion','registrationPackage'])])->findOrFail($id);
        if(!$op->order) return back();
        return view('admin.financial.offline_payments.cart_items',['pageTitle'=>trans('update.cart_items'),'offlinePayment'=>$op,'order'=>$op->order,'orderItems'=>$op->order->orderItems]);
    }
    public function exportOffline(Request $request){
        $pageType=$request->get('page_type','requests');
        $q=\App\Models\OfflinePayment::query();
        if($pageType=='requests') $q->where('status', \App\Models\OfflinePayment::$waiting); else $q->where('status','!=', \App\Models\OfflinePayment::$waiting);
        $q=$this->offlineFilters($q,$request);
        return Excel::download(new \App\Exports\OfflinePaymentsExport($q->get()), 'offline_payment_'.$pageType.'.xlsx');
    }
    public function deleteOffline(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\OfflinePayment::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    // — Subscribes — مطابق Admin\SubscribesController:34/97
    public function createSubscribe(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $cats=\App\Models\Category::getCategories(); return $this->renderAdmin($request,'panel_v1.admin.pages.sales.subscribe-form','اشتراك جديد',array_merge(AdminMockData::shell('sales','subscriptions'),['categories'=>$cats,'subscribe'=>null,'formAction'=>route('panel.v1.admin.sales.subscribes.store')]));}
    public function storeSubscribe(Request $request){
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u;
        $request->validate(['title'=>'required|string|max:255','subtitle'=>'required|string|max:255','usable_count'=>'required|numeric','days'=>'required|numeric','price'=>'required|numeric','icon'=>'required|string']);
        $data=$request->all(); $sd=['target_type'=>$data['target_type']??'all','target'=>$data['target']??null,'usable_count'=>$data['usable_count'],'days'=>$data['days'],'price'=>$data['price'],'icon'=>$data['icon'],'is_popular'=>!empty($data['is_popular'])&&$data['is_popular']=='1','infinite_use'=>!empty($data['infinite_use'])&&$data['infinite_use']=='1','created_at'=>time()];
        $sub=\App\Models\Subscribe::create($sd);
        \App\Models\Translation\SubscribeTranslation::updateOrCreate(['subscribe_id'=>$sub->id,'locale'=>mb_strtolower($data['locale']??app()->getLocale())],['title'=>$data['title'],'subtitle'=>$data['subtitle']??null,'description'=>$data['description']??null]);
        // specification items
        \App\Models\SubscribeSpecificationItem::where('subscribe_id',$sub->id)->delete();
        foreach(['category_ids'=>'category_id','instructor_ids'=>'instructor_id','courses_ids'=>'course_id','bundle_ids'=>'bundle_id'] as $k=>$col) if(!empty($data[$k])){ $ins=[]; foreach($data[$k] as $it) $ins[]=['subscribe_id'=>$sub->id,$col=>$it]; if(!empty($ins)) \App\Models\SubscribeSpecificationItem::insert($ins); }
        return redirect()->route('panel.v1.admin.sales.subscribes.edit',['id'=>$sub->id])->with('toast',['title'=>'تم','msg'=>'تم إنشاء الاشتراك','type'=>'success']);
    }
    public function editSubscribe(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $sub=\App\Models\Subscribe::findOrFail($id); $cats=\App\Models\Category::getCategories(); return $this->renderAdmin($request,'panel_v1.admin.pages.sales.subscribe-form','تعديل اشتراك',array_merge(AdminMockData::shell('sales','subscriptions'),['categories'=>$cats,'subscribe'=>$sub,'formAction'=>route('panel.v1.admin.sales.subscribes.update',['id'=>$sub->id])]));}
    public function updateSubscribe(Request $request,int $id){
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $sub=\App\Models\Subscribe::findOrFail($id);
        $request->validate(['title'=>'required|string|max:255','subtitle'=>'required|string|max:255','usable_count'=>'required|numeric','days'=>'required|numeric','price'=>'required|numeric','icon'=>'required|string']);
        $data=$request->all(); $sd=['target_type'=>$data['target_type']??'all','target'=>$data['target']??null,'usable_count'=>$data['usable_count'],'days'=>$data['days'],'price'=>$data['price'],'icon'=>$data['icon'],'is_popular'=>!empty($data['is_popular'])&&$data['is_popular']=='1','infinite_use'=>!empty($data['infinite_use'])&&$data['infinite_use']=='1'];
        $sub->update($sd);
        \App\Models\Translation\SubscribeTranslation::updateOrCreate(['subscribe_id'=>$sub->id,'locale'=>mb_strtolower($data['locale']??app()->getLocale())],['title'=>$data['title'],'subtitle'=>$data['subtitle']??null,'description'=>$data['description']??null]);
        \App\Models\SubscribeSpecificationItem::where('subscribe_id',$sub->id)->delete();
        foreach(['category_ids'=>'category_id','instructor_ids'=>'instructor_id','courses_ids'=>'course_id','bundle_ids'=>'bundle_id'] as $k=>$col) if(!empty($data[$k])){ $ins=[]; foreach($data[$k] as $it) $ins[]=['subscribe_id'=>$sub->id,$col=>$it]; if(!empty($ins)) \App\Models\SubscribeSpecificationItem::insert($ins); }
        return redirect()->route('panel.v1.admin.sales.subscribes.edit',['id'=>$sub->id])->with('toast',['title'=>'تم','msg'=>'تم التحديث','type'=>'success']);
    }
    // — Registration Packages — مطابق Admin\RegistrationPackagesController:78/152
    public function createPackage(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; return $this->renderAdmin($request,'panel_v1.admin.pages.sales.package-form','باقة تسجيل جديدة',array_merge(AdminMockData::shell('sales','packages'),['package'=>null,'formAction'=>route('panel.v1.admin.sales.packages.store')]));}
    public function storePackage(Request $request){
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u;
        $request->validate(['title'=>'required|string','description'=>'required|string','days'=>'required|numeric','price'=>'required|numeric','icon'=>'required|string','role'=>'required|in:instructors,organizations']);
        $data=$request->all(); $status=in_array($data['status']??'',['active','disabled'])?$data['status']:'disabled';
        $pkg=\App\Models\RegistrationPackage::create(['days'=>$data['days'],'price'=>$data['price'],'icon'=>$data['icon'],'role'=>$data['role'],'instructors_count'=>$data['instructors_count']??null,'students_count'=>$data['students_count']??null,'courses_capacity'=>$data['courses_capacity']??null,'courses_count'=>$data['courses_count']??null,'meeting_count'=>$data['meeting_count']??null,'product_count'=>$data['product_count']??null,'events_count'=>$data['events_count']??null,'meeting_packages_count'=>$data['meeting_packages_count']??null,'ai_content_access'=>!empty($data['ai_content_access'])&&$data['ai_content_access']=='1','status'=>$status,'created_at'=>time()]);
        \App\Models\Translation\RegistrationPackageTranslation::updateOrCreate(['registration_package_id'=>$pkg->id,'locale'=>mb_strtolower($data['locale']??app()->getLocale())],['title'=>$data['title'],'description'=>$data['description']]);
        return redirect()->route('panel.v1.admin.sales.packages.edit',['id'=>$pkg->id])->with('toast',['title'=>'تم','msg'=>'تم إنشاء الباقة','type'=>'success']);
    }
    public function editPackage(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $pkg=\App\Models\RegistrationPackage::findOrFail($id); return $this->renderAdmin($request,'panel_v1.admin.pages.sales.package-form','تعديل باقة',array_merge(AdminMockData::shell('sales','packages'),['package'=>$pkg,'formAction'=>route('panel.v1.admin.sales.packages.update',['id'=>$pkg->id])]));}
    public function updatePackage(Request $request,int $id){
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $pkg=\App\Models\RegistrationPackage::findOrFail($id);
        $request->validate(['title'=>'required|string','description'=>'required|string','days'=>'required|numeric','price'=>'required|numeric','icon'=>'required|string','role'=>'required|in:instructors,organizations']);
        $data=$request->all(); $status=in_array($data['status']??'',['active','disabled'])?$data['status']:'disabled';
        $pkg->update(['days'=>$data['days'],'price'=>$data['price'],'icon'=>$data['icon'],'role'=>$data['role'],'instructors_count'=>$data['instructors_count']??null,'students_count'=>$data['students_count']??null,'courses_capacity'=>$data['courses_capacity']??null,'courses_count'=>$data['courses_count']??null,'meeting_count'=>$data['meeting_count']??null,'product_count'=>$data['product_count']??null,'events_count'=>$data['events_count']??null,'meeting_packages_count'=>$data['meeting_packages_count']??null,'ai_content_access'=>!empty($data['ai_content_access'])&&$data['ai_content_access']=='1','status'=>$status,'created_at'=>time()]);
        \App\Models\Translation\RegistrationPackageTranslation::updateOrCreate(['registration_package_id'=>$pkg->id,'locale'=>mb_strtolower($data['locale']??app()->getLocale())],['title'=>$data['title'],'description'=>$data['description']]);
        return redirect()->route('panel.v1.admin.sales.packages.edit',['id'=>$pkg->id])->with('toast',['title'=>'تم','msg'=>'تم التحديث','type'=>'success']);
    }
    // — Installments — مطابق Admin\InstallmentsController:80/126 (مبسط مع خطوات)
    public function installmentsList(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $q=\App\Models\Installment::orderBy('created_at','desc')->withCount(['steps']); $p=$q->paginate(15)->withQueryString(); foreach($p as $inst) $inst->sales_count=\App\Models\InstallmentOrder::where('installment_id',$inst->id)->whereIn('status',['open','pending_verification'])->count(); return $this->renderAdmin($request,'panel_v1.admin.pages.sales.installments-list','خطط التقسيط',array_merge(AdminMockData::shell('sales','installments'),['installments'=>$p,'paginator'=>$p,'stubTitle'=>'التقسيط']));}
    public function createInstallment(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $groups=\App\Models\Group::where('status','active')->get(); $cats=\App\Models\Category::getCategories(); $subs=\App\Models\Subscribe::all(); $packs=\App\Models\RegistrationPackage::all(); return $this->renderAdmin($request,'panel_v1.admin.pages.sales.installment-form','خطة تقسيط جديدة',array_merge(AdminMockData::shell('sales','installments'),['userGroups'=>$groups,'categories'=>$cats,'subscriptionPackages'=>$subs,'registrationPackages'=>$packs,'installment'=>null,'formAction'=>route('panel.v1.admin.sales.installments.store')]));}
    public function storeInstallment(Request $request){
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u;
        $request->validate(['title'=>'required','main_title'=>'required','description'=>'required','target_type'=>'required']);
        $data=$request->all(); $s=!empty($data['start_date'])?convertTimeToUTCzone($data['start_date'],getTimezone())->getTimestamp():null; $e=!empty($data['end_date'])?convertTimeToUTCzone($data['end_date'],getTimezone())->getTimestamp():null;
        $inst=\App\Models\Installment::create(['target_type'=>$data['target_type'],'target'=>$data['target']??null,'capacity'=>$data['capacity']??null,'start_date'=>$s,'end_date'=>$e,'verification'=>!empty($data['verification'])&&$data['verification']=='on','request_uploads'=>!empty($data['request_uploads'])&&$data['request_uploads']=='on','bypass_verification_for_verified_users'=>!empty($data['bypass_verification_for_verified_users'])&&$data['bypass_verification_for_verified_users']=='on','upfront'=>$data['upfront']??null,'upfront_type'=>!empty($data['upfront'])?($data['upfront_type']??null):null,'enable'=>!empty($data['enable'])&&$data['enable']=='on','created_at'=>time()]);
        \App\Models\Translation\InstallmentTranslation::updateOrCreate(['installment_id'=>$inst->id,'locale'=>mb_strtolower($data['locale']??app()->getLocale())],['title'=>$data['title'],'main_title'=>$data['main_title'],'description'=>$data['description'],'banner'=>$data['banner']??null,'options'=>!empty($data['installment_options'])?implode(\App\Models\Installment::$optionsExplodeKey, array_filter($data['installment_options'])):null]);
        foreach(['category_ids'=>'category_id','instructor_ids'=>'instructor_id','seller_ids'=>'seller_id','webinar_ids'=>'webinar_id','product_ids'=>'product_id','bundle_ids'=>'bundle_id','subscribe_ids'=>'subscribe_id','registration_package_ids'=>'registration_package_id'] as $k=>$col) if(!empty($data[$k])){ $ins=[]; foreach($data[$k] as $it) $ins[]=['installment_id'=>$inst->id,$col=>$it]; if(!empty($ins)) \App\Models\InstallmentSpecificationItem::insert($ins); }
        if(!empty($data['steps'])){ $order=0; foreach($data['steps'] as $sid=>$sd) if(!empty($sd['title'])&&!empty($sd['amount'])){ $step=\App\Models\InstallmentStep::create(['installment_id'=>$inst->id,'deadline'=>$sd['deadline']??null,'amount'=>$sd['amount'],'amount_type'=>$sd['amount_type']??'fixed_amount','order'=>$order]); \App\Models\Translation\InstallmentStepTranslation::updateOrCreate(['installment_step_id'=>$step->id,'locale'=>mb_strtolower($data['locale']??app()->getLocale())],['title'=>$sd['title']]); $order++; } }
        foreach(($data['group_ids']??[]) as $gid) if(!empty($gid)) \App\Models\InstallmentUserGroup::create(['installment_id'=>$inst->id,'group_id'=>$gid]);
        return redirect()->route('panel.v1.admin.sales.installments.edit',['id'=>$inst->id])->with('toast',['title'=>'تم','msg'=>'تم إنشاء الخطة','type'=>'success']);
    }
    public function editInstallment(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $inst=\App\Models\Installment::findOrFail($id); $groups=\App\Models\Group::where('status','active')->get(); $cats=\App\Models\Category::getCategories(); $subs=\App\Models\Subscribe::all(); $packs=\App\Models\RegistrationPackage::all(); return $this->renderAdmin($request,'panel_v1.admin.pages.sales.installment-form','تعديل تقسيط',array_merge(AdminMockData::shell('sales','installments'),['userGroups'=>$groups,'categories'=>$cats,'subscriptionPackages'=>$subs,'registrationPackages'=>$packs,'installment'=>$inst,'formAction'=>route('panel.v1.admin.sales.installments.update',['id'=>$inst->id])]));}
    public function updateInstallment(Request $request,int $id){
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $inst=\App\Models\Installment::findOrFail($id);
        $request->validate(['title'=>'required','main_title'=>'required','description'=>'required','target_type'=>'required']);
        $data=$request->all(); $s=!empty($data['start_date'])?convertTimeToUTCzone($data['start_date'],getTimezone())->getTimestamp():null; $e=!empty($data['end_date'])?convertTimeToUTCzone($data['end_date'],getTimezone())->getTimestamp():null;
        $inst->update(['target_type'=>$data['target_type'],'target'=>$data['target']??null,'capacity'=>$data['capacity']??null,'start_date'=>$s,'end_date'=>$e,'verification'=>!empty($data['verification'])&&$data['verification']=='on','request_uploads'=>!empty($data['request_uploads'])&&$data['request_uploads']=='on','bypass_verification_for_verified_users'=>!empty($data['bypass_verification_for_verified_users'])&&$data['bypass_verification_for_verified_users']=='on','upfront'=>$data['upfront']??null,'upfront_type'=>!empty($data['upfront'])?($data['upfront_type']??null):null,'enable'=>!empty($data['enable'])&&$data['enable']=='on']);
        \App\Models\Translation\InstallmentTranslation::updateOrCreate(['installment_id'=>$inst->id,'locale'=>mb_strtolower($data['locale']??app()->getLocale())],['title'=>$data['title'],'main_title'=>$data['main_title'],'description'=>$data['description'],'banner'=>$data['banner']??null,'options'=>!empty($data['installment_options'])?implode(\App\Models\Installment::$optionsExplodeKey, array_filter($data['installment_options'])):null]);
        \App\Models\InstallmentSpecificationItem::where('installment_id',$inst->id)->delete(); foreach(['category_ids'=>'category_id','instructor_ids'=>'instructor_id','seller_ids'=>'seller_id','webinar_ids'=>'webinar_id','product_ids'=>'product_id','bundle_ids'=>'bundle_id','subscribe_ids'=>'subscribe_id','registration_package_ids'=>'registration_package_id'] as $k=>$col) if(!empty($data[$k])){ $ins=[]; foreach($data[$k] as $it) $ins[]=['installment_id'=>$inst->id,$col=>$it]; if(!empty($ins)) \App\Models\InstallmentSpecificationItem::insert($ins); }
        $ignore=[]; $order=0; foreach(($data['steps']??[]) as $sid=>$sd) if(!empty($sd['title'])&&!empty($sd['amount'])){ $step=is_numeric($sid)?\App\Models\InstallmentStep::where('id',$sid)->where('installment_id',$inst->id)->first():null; if(!empty($step)) $step->update(['deadline'=>$sd['deadline']??null,'amount'=>$sd['amount'],'amount_type'=>$sd['amount_type']??'fixed_amount','order'=>$order]); else $step=\App\Models\InstallmentStep::create(['installment_id'=>$inst->id,'deadline'=>$sd['deadline']??null,'amount'=>$sd['amount'],'amount_type'=>$sd['amount_type']??'fixed_amount','order'=>$order]); \App\Models\Translation\InstallmentStepTranslation::updateOrCreate(['installment_step_id'=>$step->id,'locale'=>mb_strtolower($data['locale']??app()->getLocale())],['title'=>$sd['title']]); $ignore[]=$step->id; $order++; } \App\Models\InstallmentStep::where('installment_id',$inst->id)->whereNotIn('id',$ignore)->delete();
        \App\Models\InstallmentUserGroup::where('installment_id',$inst->id)->delete(); foreach(($data['group_ids']??[]) as $gid) if(!empty($gid)) \App\Models\InstallmentUserGroup::create(['installment_id'=>$inst->id,'group_id'=>$gid]);
        return redirect()->route('panel.v1.admin.sales.installments.edit',['id'=>$inst->id])->with('toast',['title'=>'تم','msg'=>'تم التحديث','type'=>'success']);
    }
    public function deleteInstallmentPlan(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\Installment::findOrFail($id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    public function deleteSubscription(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\Subscribe::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    public function deleteInstallment(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\InstallmentOrder::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    public function deletePackage(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\RegistrationPackage::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    // ===== Meeting Packages — parity مع Admin\MeetingPackagesController =====
    public function createMeetingPackage(Request $request){
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        return $this->renderAdmin($request,'panel_v1.admin.pages.sales.meeting-package-form','باقة اجتماعات جديدة',array_merge(AdminMockData::shell('sales','meetings'),[
            'package'=>null,'formAction'=>route('panel.v1.admin.sales.meeting-packages.store'),
        ]));
    }
    public function storeMeetingPackage(Request $request){
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $request->validate(['creator_id'=>'required|exists:users,id','title'=>'required|max:255','duration'=>'required|numeric','duration_type'=>'required|in:day,week,month,year','sessions'=>'required|numeric','session_duration'=>'required|numeric','price'=>'nullable|numeric']);
        $data=$request->all();
        $pkg=\App\Models\MeetingPackage::create([
            'creator_id'=>$data['creator_id'],'icon'=>$data['icon'] ?? null,'duration'=>$data['duration'],'duration_type'=>$data['duration_type'],
            'sessions'=>$data['sessions'],'session_duration'=>$data['session_duration'],
            'price'=>!empty($data['price'])?convertPriceToDefaultCurrency($data['price']):null,
            'discount'=>$data['discount'] ?? null,'enable'=>!empty($data['enable']) && $data['enable']=='on','created_at'=>time(),
        ]);
        \App\Models\Translation\MeetingPackageTranslation::updateOrCreate(['meeting_package_id'=>$pkg->id,'locale'=>mb_strtolower($data['locale'] ?? app()->getLocale())],['title'=>$data['title']]);
        return redirect()->route('panel.v1.admin.sales.meeting-packages.edit',['id'=>$pkg->id])->with('toast',['title'=>'تم','msg'=>'تم إنشاء الباقة','type'=>'success']);
    }
    public function editMeetingPackage(Request $request,int $id){
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $pkg=\App\Models\MeetingPackage::findOrFail($id);
        return $this->renderAdmin($request,'panel_v1.admin.pages.sales.meeting-package-form','تعديل باقة اجتماعات',array_merge(AdminMockData::shell('sales','meetings'),[
            'package'=>$pkg,'formAction'=>route('panel.v1.admin.sales.meeting-packages.update',['id'=>$pkg->id]),
        ]));
    }
    public function updateMeetingPackage(Request $request,int $id){
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $pkg=\App\Models\MeetingPackage::findOrFail($id);
        $request->validate(['creator_id'=>'required|exists:users,id','title'=>'required|max:255','duration'=>'required|numeric','duration_type'=>'required|in:day,week,month,year','sessions'=>'required|numeric','session_duration'=>'required|numeric','price'=>'nullable|numeric']);
        $data=$request->all();
        $pkg->update([
            'creator_id'=>$data['creator_id'],'icon'=>$data['icon'] ?? null,'duration'=>$data['duration'],'duration_type'=>$data['duration_type'],
            'sessions'=>$data['sessions'],'session_duration'=>$data['session_duration'],
            'price'=>!empty($data['price'])?convertPriceToDefaultCurrency($data['price']):null,
            'discount'=>$data['discount'] ?? null,'enable'=>!empty($data['enable']) && $data['enable']=='on',
        ]);
        \App\Models\Translation\MeetingPackageTranslation::updateOrCreate(['meeting_package_id'=>$pkg->id,'locale'=>mb_strtolower($data['locale'] ?? app()->getLocale())],['title'=>$data['title']]);
        return redirect()->route('panel.v1.admin.sales.meeting-packages.edit',['id'=>$pkg->id])->with('toast',['title'=>'تم','msg'=>'تم التحديث','type'=>'success']);
    }
    public function deleteMeetingPackage(Request $request,int $id){
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user; \App\Models\MeetingPackage::where('id',$id)->delete();
        return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']);
    }

    // ===== Payment Channels — parity مع Admin\PaymentChannelController =====
    public function editPaymentChannel(Request $request,int $id){
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $channel=\App\Models\PaymentChannel::findOrFail($id);
        $manager=null; $credentialItems=[]; $showTestModeToggle=false;
        try{ $manager=\App\PaymentChannels\ChannelManager::makeChannel($channel); $credentialItems=$manager->getCredentialItems(); $showTestModeToggle=$manager->getShowTestModeToggle(); }catch(\Throwable $e){}
        return $this->renderAdmin($request,'panel_v1.admin.pages.sales.payment-channel-form','تعديل قناة دفع: '.$channel->title,array_merge(AdminMockData::shell('sales','payment_channels'),['channel'=>$channel,'credentialItems'=>$credentialItems,'showTestModeToggle'=>$showTestModeToggle,'formAction'=>route('panel.v1.admin.sales.payment-channels.update',['id'=>$channel->id])]));
    }
    public function updatePaymentChannel(Request $request,int $id){
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $request->validate(['title'=>'required']);
        $data=$request->all(); $ch=\App\Models\PaymentChannel::findOrFail($id);
        $credentials = $data['credentials'] ?? [];
        if (!empty($data['test_mode']) && $data['test_mode'] === 'on') {
            $credentials['test_mode'] = 'on';
        } else {
            $credentials['test_mode'] = '';
        }
        $ch->update([
            'title'=>$data['title'],
            'image'=>$data['image'] ?? $ch->image,
            'status'=>$data['status'] ?? $ch->status,
            'credentials' => !empty($credentials) ? json_encode($credentials) : $ch->credentials,
            'currencies'=> !empty($data['currencies']) ? json_encode($data['currencies']) : $ch->currencies,
        ]);
        return redirect()->route('panel.v1.admin.sales.payment-channels.edit',['id'=>$ch->id])->with('toast',['title'=>'تم','msg'=>'تم التحديث','type'=>'success']);
    }
    public function togglePaymentChannel(Request $request,int $id){
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $ch=\App\Models\PaymentChannel::findOrFail($id); $ch->update(['status'=>($ch->status=='active')?'inactive':'active']); return back()->with('toast',['title'=>'تم','msg'=>'تم التبديل','type'=>'success']);
    }

    // ===== Documents — parity مع Admin\DocumentController =====
    public function createDocument(Request $request){ $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user; return $this->renderAdmin($request,'panel_v1.admin.pages.sales.document-form','مستند مالي جديد',array_merge(AdminMockData::shell('sales','documents'),['formAction'=>route('panel.v1.admin.sales.documents.store')])); }
    public function storeDocument(Request $request){
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $request->validate(['currency'=>'required','amount'=>'required','user_id'=>'required|exists:users,id','type'=>'required']);
        $data=$request->all(); $targetUser=\App\User::findOrFail($data['user_id']); $amount=$data['amount'];
        try{ $mc=new \App\Mixins\Financial\MultiCurrency(); $spec=$mc->getSpecificCurrency($data['currency']); if(!empty($spec)) $amount=convertPriceToDefaultCurrency($amount,$spec); }catch(\Throwable $e){}
        \App\Models\Accounting::create(['creator_id'=>auth()->user()->id,'amount'=>$amount,'user_id'=>$targetUser->id,'type'=>$data['type'],'description'=>$data['description'] ?? null,'type_account'=>\App\Models\Accounting::$asset,'store_type'=>\App\Models\Accounting::$storeManual,'created_at'=>time()]);
        try{ sendNotification('new_financial_document',['[c.title]'=>'','[f.d.type]'=>trans("update.{$data['type']}"),'[amount]'=>handlePrice($amount,true,true,false,$targetUser)],$targetUser->id); }catch(\Throwable $e){}
        return redirect()->route('panel.v1.admin.sales.section',['section'=>'documents'])->with('toast',['title'=>'تم','msg'=>'تم إنشاء المستند','type'=>'success']);
    }
    public function printDocument(Request $request,int $id){ $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user; $doc=\App\Models\Accounting::findOrFail($id); return view('admin.financial.documents.print',['document'=>$doc]); }
}
