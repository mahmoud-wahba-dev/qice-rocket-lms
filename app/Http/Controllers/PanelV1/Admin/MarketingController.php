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
        $contentCards = [
            ['title' => 'المتجر', 'icon' => 'icon-[tabler--shopping-cart]'],
            ['title' => 'المدونة', 'icon' => 'icon-[tabler--article]'],
            ['title' => 'الصفحات', 'icon' => 'icon-[tabler--file-text]'],
            ['title' => 'صفحات إضافية', 'icon' => 'icon-[tabler--files]'],
            ['title' => 'التوصيات', 'icon' => 'icon-[tabler--star]'],
            ['title' => 'الثيمات', 'icon' => 'icon-[tabler--palette]'],
            ['title' => 'منشئ صفحات الهبوط', 'icon' => 'icon-[tabler--layout]'],
            ['title' => 'العلامات', 'icon' => 'icon-[tabler--tag]'],
            ['title' => 'خريطة المدربين', 'icon' => 'icon-[tabler--map-pin]'],
            ['title' => 'منشئ النماذج', 'icon' => 'icon-[tabler--forms]'],
            ['title' => 'محتوى الذكاء الاصطناعي', 'icon' => 'icon-[tabler--robot]'],
            ['title' => 'طلبات إزالة المحتوى', 'icon' => 'icon-[tabler--trash]'],
        ];
        return $this->renderAdmin(
            $request,
            'panel_v1.admin.pages.marketing.content-appearance',
            'إدارة المحتوى والمظهر',
            [
                'contentCards'=>$contentCards,
                'discounts'=>$discounts,
                'promotions'=>$promotions,
                'stats'=>[
                    ['label'=>'القسائم النشطة','value'=>(string)\App\Models\Discount::count()],
                    ['label'=>'الترويج','value'=>(string)count($promotions)],
                ],
            ]
        );
    }

    public function deleteDiscount(Request $request, int $id)
    {
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        \App\Models\Discount::where('id',$id)->delete();
        return back()->with('toast',['title'=>'تم','msg'=>'تم حذف القسيمة','type'=>'success']);
    }
    public function createDiscount(Request $request)
    {
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.discount-form','إنشاء قسيمة',array_merge(AdminMockData::shell('marketing','discounts'),[
            'formAction'=>route('panel.v1.admin.marketing.discounts.store'),
        ]));
    }
    public function storeDiscount(Request $request)
    {
        $user=$this->resolveAdmin($request); if($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $request->validate([
            'code'=>'required|unique:discounts','title'=>'required|string|max:255',
            'discount_type'=>'required|in:'.implode(',',\App\Models\Discount::$discountTypes),
            'source'=>'required|in:'.implode(',',\App\Models\Discount::$discountSource),
            'percent'=>'nullable|numeric|min:0|max:100','count'=>'nullable|integer|min:1',
        ]);
        $data=$request->all();
        \App\Models\Discount::create([
            'creator_id'=>$user->id,
            'title'=>$data['title'],
            'discount_type'=>$data['discount_type'],
            'source'=>$data['source'],
            'code'=>$data['code'],
            'percent'=>!empty($data['percent'])&&$data['percent']>0?$data['percent']:0,
            'count'=>!empty($data['count'])&&$data['count']>0?$data['count']:1,
            'expired_at'=>time()+365*24*3600,
            'created_at'=>time(),
        ]);
        return redirect()->route('panel.v1.admin.marketing.section',['section'=>'discounts'])->with('toast',['title'=>'تم','msg'=>'تم إنشاء القسيمة','type'=>'success']);
    }
    public function deleteAffiliate(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; if(class_exists(\App\Models\Affiliate::class)) \App\Models\Affiliate::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    public function deleteCashback(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; if(class_exists(\App\Models\CashbackRule::class)) \App\Models\CashbackRule::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    public function deletePoint(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\RewardAccounting::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    public function deleteGift(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; if(class_exists(\App\Models\Gift::class)) \App\Models\Gift::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    public function deleteProduct(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\Product::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم حذف المنتج','type'=>'success']); }
    public function deleteBlog(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\Blog::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم حذف المقال','type'=>'success']); }
    public function deletePage(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\Page::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم حذف الصفحة','type'=>'success']); }

    public function section(Request $request, string $section)
    {
        $shell=AdminMockData::shell('marketing', $section);
        $real=[]; $title=$section;
        $search = trim((string)$request->input('search',''));
        switch($section){
            case 'discounts':
                $title='القسائم';
                $q=\App\Models\Discount::orderBy('id','desc');
                if($search!=='') $q->where('id',$search)->orWhere('code','like',"%{$search}%")->orWhere('title','like',"%{$search}%");
                $real['discounts']=$q->paginate(15)->withQueryString();
                $real['paginator']=$real['discounts'];
                break;
            case 'affiliate':
                $title='التسويق بالعمولة';
                if(class_exists(\App\Models\Affiliate::class)){
                    $q=\App\Models\Affiliate::orderBy('id','desc');
                    if($search!=='') $q->where('id',$search);
                    $real['affiliates']=$q->paginate(15)->withQueryString();
                    $real['paginator']=$real['affiliates'];
                } else { $real['affiliates']=collect(); }
                break;
            case 'registration-bonus':
                $title='مكافأة التسجيل';
                if(class_exists(\App\Models\Reward::class)){
                    $q=\App\Models\Reward::where('type','registration_bonus');
                    if($search!=='') $q->where('id',$search);
                    $real['bonuses']=$q->paginate(15)->withQueryString();
                    $real['paginator']=$real['bonuses'];
                } else { $real['bonuses']=collect(); }
                break;
            case 'cashback':
                $title='الاسترداد النقدي';
                if(class_exists(\App\Models\CashbackRule::class)){
                    $q=\App\Models\CashbackRule::orderBy('id','desc');
                    if($search!=='') $q->where('id',$search);
                    $real['cashbacks']=$q->paginate(15)->withQueryString();
                    $real['paginator']=$real['cashbacks'];
                } else { $real['cashbacks']=collect(); }
                break;
            case 'points':
                $title='النقاط';
                $q=\App\Models\RewardAccounting::orderBy('id','desc');
                if($search!=='') $q->where('id',$search);
                $real['points']=$q->paginate(15)->withQueryString();
                $real['paginator']=$real['points'];
                break;
            case 'gifts':
                $title='الهدايا';
                if(class_exists(\App\Models\Gift::class)){
                    $q=\App\Models\Gift::orderBy('id','desc');
                    if($search!=='') $q->where('id',$search);
                    $real['gifts']=$q->paginate(15)->withQueryString();
                    $real['paginator']=$real['gifts'];
                } else { $real['gifts']=collect(); }
                break;
            case 'products':
                $title='منتجات المتجر';
                $q=\App\Models\Product::orderBy('id','desc');
                if($search!=='') $q->where('id',$search)->orWhereHas('translations',fn($t)=>$t->where('title','like',"%{$search}%"));
                $real['products']=$q->paginate(15)->withQueryString();
                $real['paginator']=$real['products'];
                break;
            case 'blog':
                $title='المدونة';
                $q=\App\Models\Blog::orderBy('id','desc');
                if($search!=='') $q->where('id',$search)->orWhereHas('translations',fn($t)=>$t->where('title','like',"%{$search}%"));
                $real['blogs']=$q->paginate(15)->withQueryString();
                $real['paginator']=$real['blogs'];
                break;
            case 'pages':
                $title='الصفحات';
                $q=\App\Models\Page::orderBy('id','desc');
                if($search!=='') $q->where('id',$search)->orWhereHas('translations',fn($t)=>$t->where('title','like',"%{$search}%"));
                $real['pages']=$q->paginate(15)->withQueryString();
                $real['paginator']=$real['pages'];
                break;
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
        $data=array_merge($shell,$real,['stubTitle'=>$title,'pageTitle'=>$title]);

        $realSections = ['discounts','affiliate','registration-bonus','cashback','points','gifts','dashboard','tools','products','blog','pages'];
        $view = in_array($section, $realSections) ? 'panel_v1.admin.pages.marketing.section-real' : 'panel_v1.admin.pages.marketing.stub';
        if (!view()->exists($view)) $view='panel_v1.admin.pages.marketing.stub';

        return $this->renderAdmin($request,$view,$data['stubTitle'],$data);
    }
}
