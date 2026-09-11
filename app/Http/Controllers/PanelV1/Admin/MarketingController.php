<?php

namespace App\Http\Controllers\PanelV1\Admin;

use App\Http\Controllers\PanelV1\AdminController;
use App\Http\Controllers\PanelV1\Support\AdminMockData;
use Illuminate\Http\Request;

class MarketingController extends AdminController
{
    public function home(Request $request)
    {
        $discounts=\App\Models\Discount::orderBy('id','desc')->limit(10)->get()->map(fn($d)=>[
            'code'=>$d->code,'title'=>$d->title,'percent'=>$d->percent,'count'=>$d->count ?? '—','status'=>$d->status ?? 'active'
        ])->all();
        $promotions=[];
        try{ $promotions=\App\Models\Promotion::orderBy('id','desc')->limit(10)->get()->map(fn($p)=>['title'=>$p->title ?? 'ترويج #'.$p->id,'status'=>$p->status ?? '—'])->all(); }catch(\Throwable $e){}
        return $this->renderAdmin(
            $request,
            'panel_v1.admin.pages.marketing.content-appearance',
            'إدارة المحتوى والمظهر',
            [
                'discounts'=>$discounts,
                'promotions'=>$promotions,
                'stats'=>[
                    ['label'=>'القسائم النشطة','value'=>(string)\App\Models\Discount::count()],
                    ['label'=>'الترويج','value'=>(string)count($promotions)],
                ],
            ]
        );
    }

    public function section(Request $request, string $section)
    {
        $shell=AdminMockData::shell('marketing', $section);
        $real=[]; $title=$section;
        switch($section){
            case 'discounts':
                $title='القسائم'; $real['discounts']=\App\Models\Discount::orderBy('id','desc')->limit(20)->get(); break;
            case 'affiliate':
                $title='التسويق بالعمولة'; $real['affiliates']=class_exists(\App\Models\Affiliate::class) ? \App\Models\Affiliate::orderBy('id','desc')->limit(20)->get() : collect(); break;
            case 'registration-bonus':
                $title='مكافأة التسجيل'; $real['bonuses']=class_exists(\App\Models\Reward::class) ? \App\Models\Reward::where('type','registration_bonus')->limit(20)->get() : collect(); break;
            case 'cashback':
                $title='الاسترداد النقدي'; $real['cashbacks']=class_exists(\App\Models\CashbackRule::class) ? \App\Models\CashbackRule::orderBy('id','desc')->limit(20)->get() : collect(); break;
            case 'points':
                $title='النقاط'; $real['points']=\App\Models\RewardAccounting::orderBy('id','desc')->limit(20)->get(); break;
            case 'gifts':
                $title='الهدايا'; $real['gifts']=class_exists(\App\Models\Gift::class) ? \App\Models\Gift::orderBy('id','desc')->limit(20)->get() : collect(); break;
            case 'dashboard':
                $title='لوحة التسويق'; $real['stats']=[['label'=>'القسائم','value'=>(string)\App\Models\Discount::count()],['label'=>'الترويج','value'=>class_exists(\App\Models\Promotion::class)?(string)\App\Models\Promotion::count():'0']]; break;
            case 'tools':
                $title='أدوات التسويق'; $real['tools']=collect(); break;
            default:
                $meta=AdminMockData::stubMeta('marketing', $section);
                $title=$meta['stubTitle'] ?? $section;
                $real=$meta;
                break;
        }
        $data=array_merge($shell,$real,['stubTitle'=>$title]);
        return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.stub',$data['stubTitle'],$data);
    }
}
