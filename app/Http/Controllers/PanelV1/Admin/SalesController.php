<?php

namespace App\Http\Controllers\PanelV1\Admin;

use App\Http\Controllers\PanelV1\AdminController;
use App\Http\Controllers\PanelV1\Support\AdminMockData;
use Illuminate\Http\Request;

class SalesController extends AdminController
{
    public function home(Request $request)
    {
        $sales = \App\Models\Sale::with(['buyer','seller','webinar'])->whereNull('refund_at')->orderBy('id','desc')->limit(20)->get();
        $rows = $sales->map(fn($s)=>[
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
                'pageTitleText'=>'قائمة المبيعات',
                'pageSubtitle'=> count($rows).' عملية',
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
        switch($section){
            case 'payouts':
                $title='طلبات السحب';
                $real['payouts']=\App\Models\Payout::with(['user'])->orderBy('id','desc')->limit(20)->get()->map(fn($p)=>[
                    'id'=>'#'.$p->id,'user'=>$p->user->full_name ?? '','amount'=>handlePrice($p->amount),'status'=>$p->status,'date'=>date('Y/m/d',(int)$p->created_at)
                ])->all();
                break;
            case 'sales':
                return $this->home($request);
            case 'budget':
                $title='الميزانية';
                $real['budget']=['total'=>handlePrice(\App\Models\Sale::whereNull('refund_at')->sum('total_amount')),'count'=>\App\Models\Sale::whereNull('refund_at')->count()];
                break;
            case 'offline':
                $title='المدفوعات دون اتصال';
                $real['offlinePayments']=\App\Models\OfflinePayment::orderBy('id','desc')->limit(20)->get();
                break;
            case 'subscriptions':
                $title='خطط الاشتراك';
                $real['subscriptions']=\App\Models\Subscribe::orderBy('id','desc')->limit(20)->get();
                break;
            case 'installments':
                $title='التقسيط';
                $real['installments']=\App\Models\InstallmentOrder::orderBy('id','desc')->limit(20)->get();
                break;
            case 'meetings':
                $title='باقات الاجتماعات'; $real['meetings']=class_exists(\App\Models\Meeting::class) ? \App\Models\Meeting::orderBy('id','desc')->limit(20)->get() : collect(); break;
            case 'packages':
                $title='الباقات'; $real['packages']=\App\Models\RegistrationPackage::orderBy('id','desc')->limit(20)->get(); break;
            default:
                $meta=AdminMockData::stubMeta('sales', $section);
                $title=$meta['stubTitle'] ?? $section;
                $real=$meta;
                break;
        }
        $data=array_merge($shell,$real,['stubTitle'=>$title]);
        return $this->renderAdmin($request,'panel_v1.admin.pages.sales.stub',$data['stubTitle'],$data);
    }
}
