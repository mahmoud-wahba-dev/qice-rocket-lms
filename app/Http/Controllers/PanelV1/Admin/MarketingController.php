<?php

namespace App\Http\Controllers\PanelV1\Admin;

use App\Http\Controllers\PanelV1\AdminController;
use App\Http\Controllers\PanelV1\Support\AdminMockData;
use Illuminate\Http\Request;

class MarketingController extends AdminController
{
    public function home(Request $request)
    {
        $discounts = \App\Models\Discount::orderBy('id','desc')->limit(10)->get()->map(fn($d)=>[
            'code'=>$d->code,'title'=>$d->title,'percent'=>$d->percent,'count'=>$d->count ?? '—','status'=>($d->expired_at < time() ? 'expired' : 'active')
        ])->all();
        $promotions = [];
        try { $promotions = \App\Models\Promotion::orderBy('id','desc')->limit(10)->get()->map(fn($p)=>['title'=>$p->title ?? 'ترويج #'.$p->id,'status'=>$p->status ?? '—'])->all(); } catch (\Throwable $e) {}
        // طبق الأصل للصورة — 9 كروت "إدارة أدوات التسويق" بألوانك الحالية (light + primary)
        $contentCards = [
            ['title' => 'أكواد الخصم', 'icon' => 'icon-[tabler--code]', 'section' => 'discounts'],
            ['title' => 'خصم على السلة', 'icon' => 'icon-[tabler--shopping-cart]', 'section' => 'cart_discount'],
            ['title' => 'خصومات الدورات', 'icon' => 'icon-[tabler--book]', 'section' => 'discounts'],
            ['title' => 'السلة المتروكة', 'icon' => 'icon-[tabler--shopping-cart-x]', 'section' => 'abandoned_cart'],
            ['title' => 'المحاضرات الرائجة', 'icon' => 'icon-[tabler--stack-2]', 'section' => 'dashboard'],
            ['title' => 'خطط الترويج والبانرات الإعلانية', 'icon' => 'icon-[tabler--speakerphone]', 'section' => 'banners'],
            ['title' => 'القائمة البريدية', 'icon' => 'icon-[tabler--mail]', 'section' => 'newsletters'],
            ['title' => 'شريط أعلى/أسفل', 'icon' => 'icon-[tabler--layout-navbar]', 'section' => 'floating_bars'],
            ['title' => 'النافذة الإعلانية المنبثقة', 'icon' => 'icon-[tabler--window]', 'section' => 'advertising_modal'],
        ];
        return $this->renderAdmin(
            $request,
            'panel_v1.admin.pages.marketing.content-appearance',
            'إدارة أدوات التسويق',
            array_merge(AdminMockData::shell('marketing', 'home'), [
                'pageTitleText' => 'إدارة أدوات التسويق',
                'pageSubtitle' => 'مركز التحكم بإعدادات الحملات الترويجية وتفعيل أدوات الخصم والتسويق لزيادة المبيعات.',
                'contentCards'=>$contentCards,
                'discounts'=>$discounts,
                'promotions'=>$promotions,
                'stats'=>[
                    ['label'=>'القسائم النشطة','value'=>(string)\App\Models\Discount::count()],
                    ['label'=>'الترويج','value'=>(string)count($promotions)],
                    ['label'=>'المدونة','value'=>(string)\App\Models\Blog::count()],
                    ['label'=>'الصفحات','value'=>(string)\App\Models\Page::count()],
                ],
            ])
        );
    }

    public function createDiscount(Request $request)
    {
        $user = $this->resolveAdmin($request); if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $userGroups = \App\Models\Group::orderBy('created_at','desc')->where('status','active')->get();
        $webinars = \App\Models\Webinar::orderBy('id','desc')->limit(50)->get()->map(fn($w)=>['id'=>$w->id,'title'=>$w->title])->all();
        $bundles = \App\Models\Bundle::orderBy('id','desc')->limit(50)->get()->map(fn($b)=>['id'=>$b->id,'title'=>$b->title ?? 'حزمة #'.$b->id])->all();
        $categories = \App\Models\Category::whereNull('parent_id')->orderBy('order')->get()->map(fn($c)=>['id'=>$c->id,'title'=>$c->title])->all();
        return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.discount-form','إنشاء قسيمة',array_merge(AdminMockData::shell('marketing','discounts'),[
            'userGroups'=>$userGroups,'webinars'=>$webinars,'bundles'=>$bundles,'categories'=>$categories,
            'formAction'=>route('panel.v1.admin.marketing.discounts.store'),
        ]));
    }

    public function storeDiscount(Request $request)
    {
        $user = $this->resolveAdmin($request); if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $request->validate([
            'title'=>'required',
            'discount_type'=>'required|in:'.implode(',',\App\Models\Discount::$discountTypes),
            'source'=>'required|in:'.implode(',',\App\Models\Discount::$discountSource),
            'code'=>'required|unique:discounts',
            'percent'=>'nullable|numeric|min:0|max:100',
            'amount'=>'nullable|numeric|min:0',
            'count'=>'nullable|integer|min:1',
            'expired_at'=>'nullable',
        ]);
        $data = $request->all();
        $expiredAt = !empty($data['expired_at']) ? convertTimeToUTCzone($data['expired_at'], getTimezone()) : now()->addYear();
        $userType = !empty($data['user_id']) ? 'special_users' : 'all_users';
        $discount = \App\Models\Discount::create([
            'creator_id'=>$user->id,
            'title'=>$data['title'],
            'discount_type'=>$data['discount_type'],
            'source'=>$data['source'],
            'code'=>$data['code'],
            'percent'=>!empty($data['percent'])&&$data['percent']>0?$data['percent']:0,
            'amount'=>!empty($data['amount'])?convertPriceToDefaultCurrency($data['amount']):null,
            'max_amount'=>!empty($data['max_amount'])?convertPriceToDefaultCurrency($data['max_amount']):null,
            'minimum_order'=>!empty($data['minimum_order'])?convertPriceToDefaultCurrency($data['minimum_order']):null,
            'count'=>!empty($data['count'])&&$data['count']>0?$data['count']:1,
            'user_type'=>$userType,
            'product_type'=>$data['product_type']??null,
            'for_first_purchase'=>!empty($data['for_first_purchase']),
            'expired_at'=>$expiredAt->getTimestamp(),
            'created_at'=>time(),
        ]);
        $this->handleDiscountRelations($discount, $data);
        return redirect()->route('panel.v1.admin.marketing.section',['section'=>'discounts'])->with('toast',['title'=>'تم','msg'=>'تم إنشاء القسيمة','type'=>'success']);
    }

    public function editDiscount(Request $request,int $id)
    {
        $user = $this->resolveAdmin($request); if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $discount = \App\Models\Discount::findOrFail($id);
        $userGroups = \App\Models\Group::orderBy('created_at','desc')->where('status','active')->get();
        $webinars = \App\Models\Webinar::orderBy('id','desc')->limit(50)->get()->map(fn($w)=>['id'=>$w->id,'title'=>$w->title])->all();
        $bundles = \App\Models\Bundle::orderBy('id','desc')->limit(50)->get()->map(fn($b)=>['id'=>$b->id,'title'=>$b->title ?? 'حزمة #'.$b->id])->all();
        $categories = \App\Models\Category::whereNull('parent_id')->orderBy('order')->get()->map(fn($c)=>['id'=>$c->id,'title'=>$c->title])->all();
        $userDiscounts = \App\Models\DiscountUser::where('discount_id',$id)->get();
        $discountCourses = \App\Models\DiscountCourse::where('discount_id',$id)->pluck('course_id')->toArray();
        $discountBundles = \App\Models\DiscountBundle::where('discount_id',$id)->pluck('bundle_id')->toArray();
        $discountGroupIds = $discount->discountGroups->pluck('group_id')->toArray() ?? [];
        return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.discount-form','تعديل قسيمة',array_merge(AdminMockData::shell('marketing','discounts'),[
            'discount'=>$discount,'userGroups'=>$userGroups,'webinars'=>$webinars,'bundles'=>$bundles,'categories'=>$categories,
            'userDiscounts'=>$userDiscounts,'discountGroupIds'=>$discountGroupIds,'discountCourses'=>$discountCourses,'discountBundles'=>$discountBundles,
            'formAction'=>route('panel.v1.admin.marketing.discounts.update',['id'=>$id]),
        ]));
    }

    public function updateDiscount(Request $request,int $id)
    {
        $user = $this->resolveAdmin($request); if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;
        $discount = \App\Models\Discount::findOrFail($id);
        $request->validate([
            'title'=>'required',
            'discount_type'=>'required|in:'.implode(',',\App\Models\Discount::$discountTypes),
            'source'=>'required|in:'.implode(',',\App\Models\Discount::$discountSource),
            'code'=>'required|unique:discounts,code,'.$discount->id,
            'percent'=>'nullable|numeric|min:0|max:100',
            'expired_at'=>'nullable',
        ]);
        $data = $request->all();
        $expiredAt = !empty($data['expired_at']) ? convertTimeToUTCzone($data['expired_at'], getTimezone()) : now()->addYear();
        $userType = !empty($data['user_id']) ? 'special_users' : 'all_users';
        $discount->update([
            'title'=>$data['title'],'discount_type'=>$data['discount_type'],'source'=>$data['source'],'code'=>$data['code'],
            'percent'=>!empty($data['percent'])&&$data['percent']>0?$data['percent']:0,
            'amount'=>!empty($data['amount'])?convertPriceToDefaultCurrency($data['amount']):null,
            'max_amount'=>!empty($data['max_amount'])?convertPriceToDefaultCurrency($data['max_amount']):null,
            'minimum_order'=>!empty($data['minimum_order'])?convertPriceToDefaultCurrency($data['minimum_order']):null,
            'count'=>!empty($data['count'])&&$data['count']>0?$data['count']:1,
            'user_type'=>$userType,'product_type'=>$data['product_type']??null,'for_first_purchase'=>!empty($data['for_first_purchase']),'expired_at'=>$expiredAt->getTimestamp(),
        ]);
        \App\Models\DiscountUser::where('discount_id',$discount->id)->delete();
        \App\Models\DiscountCourse::where('discount_id',$discount->id)->delete();
        \App\Models\DiscountBundle::where('discount_id',$discount->id)->delete();
        \App\Models\DiscountCategory::where('discount_id',$discount->id)->delete();
        \App\Models\DiscountGroup::where('discount_id',$discount->id)->delete();
        $this->handleDiscountRelations($discount,$data);
        return redirect()->route('panel.v1.admin.marketing.section',['section'=>'discounts'])->with('toast',['title'=>'تم','msg'=>'تم تحديث القسيمة','type'=>'success']);
    }

    private function handleDiscountRelations($discount,$data)
    {
        if(!empty($data['user_id'])) \App\Models\DiscountUser::create(['discount_id'=>$discount->id,'user_id'=>$data['user_id'],'created_at'=>time()]);
        foreach(($data['webinar_ids']??[]) as $cid) \App\Models\DiscountCourse::create(['discount_id'=>$discount->id,'course_id'=>$cid,'created_at'=>time()]);
        foreach(($data['bundle_ids']??[]) as $bid) \App\Models\DiscountBundle::create(['discount_id'=>$discount->id,'bundle_id'=>$bid,'created_at'=>time()]);
        foreach(($data['category_ids']??[]) as $cid) \App\Models\DiscountCategory::create(['discount_id'=>$discount->id,'category_id'=>$cid,'created_at'=>time()]);
        foreach(($data['group_ids']??[]) as $gid) \App\Models\DiscountGroup::create(['discount_id'=>$discount->id,'group_id'=>$gid,'created_at'=>time()]);
    }

    public function deleteDiscount(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\Discount::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم حذف القسيمة','type'=>'success']); }
    // — Gifts — مطابق Admin\GiftsController:240/256
    public function sendGiftReminder(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $gift=\App\Models\Gift::findOrFail($id); $gift->sendReminderToRecipient(0); return back()->with('toast',['title'=>'تم','msg'=>'تم إرسال التذكير','type'=>'success']); }
    public function cancelGift(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $gift=\App\Models\Gift::findOrFail($id); $sale=$gift->sale; if(!empty($sale) && !empty($sale->total_amount)){ \App\Models\Accounting::refundAccounting($sale); $sale->update(['refund_at'=>time()]); } $gift->update(['status'=>'cancel']); return back()->with('toast',['title'=>'تم','msg'=>'تم إلغاء الهدية','type'=>'success']); }
    // — Cashback — مطابق Admin\CashbackRuleController:106/359
    public function createCashback(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $groups=\App\Models\Group::where('status','active')->get(); $cats=\App\Models\Category::getCategories(); $subs=\App\Models\Subscribe::all(); $packs=\App\Models\RegistrationPackage::all(); return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.cashback-form','قاعدة كاش باك جديدة',array_merge(AdminMockData::shell('marketing','cashback'),['userGroups'=>$groups,'categories'=>$cats,'subscriptionPackages'=>$subs,'registrationPackages'=>$packs,'rule'=>null,'formAction'=>route('panel.v1.admin.marketing.cashback.store')]));}
    public function storeCashback(Request $request){
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u;
        $request->validate(['title'=>'required','target_type'=>'required','amount'=>'required|numeric','start_date'=>'required']);
        $data=$request->all(); $s=!empty($data['start_date'])?convertTimeToUTCzone($data['start_date'],getTimezone())->getTimestamp():null; $e=!empty($data['end_date'])?convertTimeToUTCzone($data['end_date'],getTimezone())->getTimestamp():null;
        $rule=\App\Models\CashbackRule::create(['target_type'=>$data['target_type'],'target'=>$data['target']??null,'start_date'=>$s,'end_date'=>$e,'amount'=>$data['amount'],'amount_type'=>$data['amount_type']??'fixed_amount','apply_cashback_per_item'=>(($data['amount_type']??'')=='fixed_amount'&&!empty($data['apply_cashback_per_item'])&&$data['apply_cashback_per_item']=='on'),'max_amount'=>(($data['amount_type']??'')=='percent'&&!empty($data['max_amount'])?$data['max_amount']:null),'min_amount'=>$data['min_amount']??null,'enable'=>!empty($data['enable'])&&$data['enable']=='on','created_at'=>time()]);
        $this->storeCashbackExtra($rule,$data); return redirect()->route('panel.v1.admin.marketing.cashback.edit',['id'=>$rule->id])->with('toast',['title'=>'تم','msg'=>'تم إنشاء القاعدة','type'=>'success']);
    }
    public function editCashback(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $rule=\App\Models\CashbackRule::findOrFail($id); $groups=\App\Models\Group::where('status','active')->get(); $cats=\App\Models\Category::getCategories(); $subs=\App\Models\Subscribe::all(); $packs=\App\Models\RegistrationPackage::all(); return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.cashback-form','تعديل كاش باك',array_merge(AdminMockData::shell('marketing','cashback'),['userGroups'=>$groups,'categories'=>$cats,'subscriptionPackages'=>$subs,'registrationPackages'=>$packs,'rule'=>$rule,'formAction'=>route('panel.v1.admin.marketing.cashback.update',['id'=>$rule->id])]));}
    public function updateCashback(Request $request,int $id){
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $rule=\App\Models\CashbackRule::findOrFail($id);
        $request->validate(['title'=>'required','target_type'=>'required','amount'=>'required|numeric','start_date'=>'required']);
        $data=$request->all(); $s=!empty($data['start_date'])?convertTimeToUTCzone($data['start_date'],getTimezone())->getTimestamp():null; $e=!empty($data['end_date'])?convertTimeToUTCzone($data['end_date'],getTimezone())->getTimestamp():null;
        $rule->update(['target_type'=>$data['target_type'],'target'=>$data['target']??null,'start_date'=>$s,'end_date'=>$e,'amount'=>$data['amount'],'amount_type'=>$data['amount_type']??'fixed_amount','apply_cashback_per_item'=>(($data['amount_type']??'')=='fixed_amount'&&!empty($data['apply_cashback_per_item'])&&$data['apply_cashback_per_item']=='on'),'max_amount'=>(($data['amount_type']??'')=='percent'&&!empty($data['max_amount'])?$data['max_amount']:null),'min_amount'=>$data['min_amount']??null,'enable'=>!empty($data['enable'])&&$data['enable']=='on']);
        $this->storeCashbackExtra($rule,$data); return redirect()->route('panel.v1.admin.marketing.cashback.edit',['id'=>$rule->id])->with('toast',['title'=>'تم','msg'=>'تم تحديث القاعدة','type'=>'success']);
    }
    private function storeCashbackExtra($rule,$data){
        \App\Models\Translation\CashbackRuleTranslation::updateOrCreate(['cashback_rule_id'=>$rule->id,'locale'=>mb_strtolower($data['locale']??app()->getLocale())],['title'=>$data['title']]);
        \App\Models\CashbackRuleSpecificationItem::where('cashback_rule_id',$rule->id)->delete();
        $specs=['category_ids'=>'category_id','instructor_ids'=>'instructor_id','seller_ids'=>'seller_id','webinar_ids'=>'webinar_id','product_ids'=>'product_id','bundle_ids'=>'bundle_id','subscribe_ids'=>'subscribe_id','registration_package_ids'=>'registration_package_id'];
        foreach($specs as $key=>$col){ if(!empty($data[$key])){ $ins=[]; foreach($data[$key] as $item) $ins[]=['cashback_rule_id'=>$rule->id,$col=>$item]; if(!empty($ins)) \App\Models\CashbackRuleSpecificationItem::insert($ins); } }
        \App\Models\CashbackRuleUserGroup::where('cashback_rule_id',$rule->id)->delete();
        foreach(($data['group_ids']??[]) as $gid) if(!empty($gid)) \App\Models\CashbackRuleUserGroup::create(['cashback_rule_id'=>$rule->id,'group_id'=>$gid]);
        foreach(($data['users_ids']??[]) as $uid) if(!empty($uid)) \App\Models\CashbackRuleUserGroup::create(['cashback_rule_id'=>$rule->id,'user_id'=>$uid]);
    }
    public function toggleCashback(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $r=\App\Models\CashbackRule::findOrFail($id); $r->update(['enable'=>!$r->enable]); return back()->with('toast',['title'=>'تم','msg'=>'تم التبديل','type'=>'success']); }
    // — Special Offers — مطابق Admin\SpecialOfferController:123/200
    public function createSpecialOffer(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $subs=\App\Models\Subscribe::all(); $packs=\App\Models\RegistrationPackage::where('status','active')->get(); return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.special-offer-form','عرض خاص جديد',array_merge(AdminMockData::shell('marketing','special_offers'),['subscribes'=>$subs,'registrationPackages'=>$packs,'offer'=>null,'formAction'=>route('panel.v1.admin.marketing.special-offers.store')]));}
    public function storeSpecialOffer(Request $request){
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u;
        $request->validate(['percent'=>'required','from_date'=>'required','to_date'=>'required']);
        $data=$request->all(); $item=$this->getSpecialOfferItem($data); if($item && $item->activeSpecialOffer()) return back()->with('toast',['title'=>'فشل','msg'=>'العنصر لديه عرض فعال','type'=>'error']);
        $from=convertTimeToUTCzone($data['from_date'],getTimezone())->getTimestamp(); $to=convertTimeToUTCzone($data['to_date'],getTimezone())->getTimestamp();
        \App\Models\SpecialOffer::create(['creator_id'=>$u->id,'name'=>$data['name']??'عرض','webinar_id'=>$data['webinar_id']??null,'bundle_id'=>$data['bundle_id']??null,'subscribe_id'=>$data['subscribe_id']??null,'registration_package_id'=>$data['registration_package_id']??null,'percent'=>$data['percent'],'status'=>$data['status']??'active','created_at'=>time(),'from_date'=>$from,'to_date'=>$to]);
        return redirect()->route('panel.v1.admin.marketing.section',['section'=>'special_offers'])->with('toast',['title'=>'تم','msg'=>'تم إنشاء العرض','type'=>'success']);
    }
    public function editSpecialOffer(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $offer=\App\Models\SpecialOffer::findOrFail($id); $subs=\App\Models\Subscribe::all(); $packs=\App\Models\RegistrationPackage::where('status','active')->get(); return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.special-offer-form','تعديل عرض',array_merge(AdminMockData::shell('marketing','special_offers'),['subscribes'=>$subs,'registrationPackages'=>$packs,'offer'=>$offer,'formAction'=>route('panel.v1.admin.marketing.special-offers.update',['id'=>$offer->id])]));}
    public function updateSpecialOffer(Request $request,int $id){
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $offer=\App\Models\SpecialOffer::findOrFail($id);
        $request->validate(['percent'=>'required','from_date'=>'required','to_date'=>'required']);
        $data=$request->all(); $from=convertTimeToUTCzone($data['from_date'],getTimezone())->getTimestamp(); $to=convertTimeToUTCzone($data['to_date'],getTimezone())->getTimestamp();
        $offer->update(['name'=>$data['name']??$offer->name,'webinar_id'=>$data['webinar_id']??null,'bundle_id'=>$data['bundle_id']??null,'subscribe_id'=>$data['subscribe_id']??null,'registration_package_id'=>$data['registration_package_id']??null,'percent'=>$data['percent'],'status'=>$data['status']??'active','from_date'=>$from,'to_date'=>$to]);
        return redirect()->route('panel.v1.admin.marketing.section',['section'=>'special_offers'])->with('toast',['title'=>'تم','msg'=>'تم التحديث','type'=>'success']);
    }
    public function deleteSpecialOffer(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\SpecialOffer::findOrFail($id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    private function getSpecialOfferItem($data){
        if(!empty($data['webinar_id'])) return \App\Models\Webinar::findOrFail($data['webinar_id']);
        if(!empty($data['bundle_id'])) return \App\Models\Bundle::findOrFail($data['bundle_id']);
        if(!empty($data['subscribe_id'])) return \App\Models\Subscribe::findOrFail($data['subscribe_id']);
        if(!empty($data['registration_package_id'])) return \App\Models\RegistrationPackage::findOrFail($data['registration_package_id']);
        return null;
    }
    // — Referrals — مطابق Admin\ReferralController:15/59
    public function referralsHistory(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $q=\App\Models\Affiliate::query(); $cnt=(clone $q)->groupBy('affiliate_user_id')->get()->count(); $amt=\App\Models\Accounting::where('is_affiliate_amount',true)->where('system',false)->sum('amount'); $comm=\App\Models\Accounting::where('is_affiliate_commission',true)->where('system',false)->sum('amount'); $p=$q->with(['affiliateUser'=>fn($qq)=>$qq->select('id','full_name','role_id','role_name'),'referredUser'=>fn($qq)=>$qq->select('id','full_name')])->orderBy('created_at','desc')->paginate(15)->withQueryString(); return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.section-real','سجل الإحالات',array_merge(AdminMockData::shell('marketing','referrals'),['affiliates'=>$p,'paginator'=>$p,'affiliateUsersCount'=>$cnt,'allAffiliateAmounts'=>$amt,'allAffiliateCommissionAmounts'=>$comm,'stubTitle'=>'سجل الإحالات']));}
    public function referralsUsers(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $p=\App\Models\Affiliate::with(['affiliateUser'=>fn($qq)=>$qq->select('id','full_name','role_id','role_name')->with(['affiliateCode','userGroup'])])->groupBy('affiliate_user_id')->orderBy('created_at','desc')->paginate(15)->withQueryString(); return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.section-real','المحالون',array_merge(AdminMockData::shell('marketing','referrals'),['affiliates'=>$p,'paginator'=>$p,'stubTitle'=>'المحالون']));}
    public function exportReferrals(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $type=$request->get('type','history'); if($type=='users'){ $data=$this->referralsUsers($request); } else { $data=$this->referralsHistory($request); } return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ReferralHistoryExport($data->getData()['affiliates'] ?? collect()), 'referrals_'.$type.'.xlsx');}
    // — Promotions — مطابق Admin\PromotionsController:18/131
    public function createPromotion(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.promotion-form','ترقية جديدة',array_merge(AdminMockData::shell('marketing','promotions'),['promotion'=>null,'formAction'=>route('panel.v1.admin.marketing.promotions.store')]));}
    public function storePromotion(Request $request){
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u;
        $request->validate(['title'=>'required|string','days'=>'required|numeric','price'=>'required|numeric','icon'=>'required|string','description'=>'required|string']);
        $data=$request->all(); $p=\App\Models\Promotion::create(['days'=>$data['days'],'price'=>$data['price'],'icon'=>$data['icon'],'is_popular'=>$data['is_popular']??0,'created_at'=>time()]);
        \App\Models\Translation\PromotionTranslation::updateOrCreate(['promotion_id'=>$p->id,'locale'=>mb_strtolower($data['locale']??app()->getLocale())],['title'=>$data['title'],'description'=>$data['description']]);
        return redirect()->route('panel.v1.admin.marketing.promotions.edit',['id'=>$p->id])->with('toast',['title'=>'تم','msg'=>'تم إنشاء الترقية','type'=>'success']);
    }
    public function editPromotion(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $p=\App\Models\Promotion::findOrFail($id); return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.promotion-form','تعديل ترقية',array_merge(AdminMockData::shell('marketing','promotions'),['promotion'=>$p,'formAction'=>route('panel.v1.admin.marketing.promotions.update',['id'=>$p->id])]));}
    public function updatePromotion(Request $request,int $id){
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $p=\App\Models\Promotion::findOrFail($id);
        $request->validate(['title'=>'required|string','days'=>'required|numeric','price'=>'required|numeric','icon'=>'required|string','description'=>'required|string']);
        $data=$request->all(); $p->update(['days'=>$data['days'],'price'=>$data['price'],'icon'=>$data['icon'],'is_popular'=>$data['is_popular']??0]);
        \App\Models\Translation\PromotionTranslation::updateOrCreate(['promotion_id'=>$p->id,'locale'=>mb_strtolower($data['locale']??app()->getLocale())],['title'=>$data['title'],'description'=>$data['description']]);
        return redirect()->route('panel.v1.admin.marketing.promotions.edit',['id'=>$p->id])->with('toast',['title'=>'تم','msg'=>'تم التحديث','type'=>'success']);
    }
    public function deletePromotion(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\Promotion::findOrFail($id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    public function promotionSales(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $p=\App\Models\Sale::where('type',\App\Models\Sale::$promotion)->whereNull('refund_at')->orderBy('created_at','desc')->paginate(15)->withQueryString(); return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.section-real','مبيعات الترقيات',array_merge(AdminMockData::shell('marketing','promotions'),['promotionSales'=>$p,'paginator'=>$p,'stubTitle'=>'مبيعات الترقيات']));}
    // — Newsletters — مطابق Admin\NewslettersController:15/42
    public function sendNewsletterForm(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.newsletter-send','إرسال نشرة',array_merge(AdminMockData::shell('marketing','newsletters'),['formAction'=>route('panel.v1.admin.marketing.newsletters.send')]));}
    public function sendNewsletter(Request $request){
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u;
        $request->validate(['title'=>'required|string','description'=>'required|string','send_method'=>'required|in:send_to_all,send_to_bcc,send_to_excel','bcc_email'=>'required_if:send_method,send_to_bcc','excel'=>'required_if:send_method,send_to_excel|mimes:xlsx']);
        $data=$request->all(); $title=$data['title']; $desc=$data['description']; $send=0;
        try{
            if($data['send_method']=='send_to_bcc'){ $ccs=\App\Models\Newsletter::pluck('email')->toArray(); \Illuminate\Support\Facades\Mail::to($data['bcc_email'])->send(new \App\Mail\SendNotifications(['title'=>$title,'message'=>$desc,'cc'=>$ccs])); $send=count($ccs); }
            elseif($data['send_method']=='send_to_excel'){ $rows=\Maatwebsite\Excel\Facades\Excel::toArray(null,$request->file('excel')); foreach($rows[0]??[] as $row) if(!empty($row[0])) \Illuminate\Support\Facades\Mail::to($row[0])->send(new \App\Mail\SendNotifications(['title'=>$title,'message'=>$desc])); $send=count($rows[0]??[]); }
            else { $news=\App\Models\Newsletter::all(); foreach($news as $n) \Illuminate\Support\Facades\Mail::to($n->email)->send(new \App\Mail\SendNotifications(['title'=>$title,'message'=>$desc])); $send=count($news); }
        }catch(\Exception $e){ session()->put('send_email_error',$e->getMessage()); return back()->withInput($data)->with('toast',['title'=>'فشل','msg'=>$e->getMessage(),'type'=>'error']); }
        \App\Models\NewsletterHistory::create(['title'=>$title,'description'=>$desc,'send_method'=>$data['send_method'],'bcc_email'=>$data['bcc_email']??null,'email_count'=>$send,'created_at'=>time()]);
        return redirect()->route('panel.v1.admin.marketing.section',['section'=>'newsletters'])->with('toast',['title'=>'تم','msg'=>'تم الإرسال إلى '.$send.' بريد','type'=>'success']);
    }
    public function deleteNewsletter(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\Newsletter::findOrFail($id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    public function exportNewsletters(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $news=\App\Models\Newsletter::orderBy('created_at','desc')->get(); return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\NewslettersExport($news), 'newsletters.xlsx');}
    // — Abandoned Cart Rules — مطابق Admin\AbandonedCartRulesController:90/141
    public function abandonedCartRules(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $q=\App\Models\AbandonedCartRule::query()->orderBy('created_at','desc'); $p=$q->paginate(15)->withQueryString(); return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.section-real','قواعد السلة المتروكة',array_merge(AdminMockData::shell('marketing','abandoned_cart'),['rules'=>$p,'paginator'=>$p,'stubTitle'=>'السلة المتروكة']));}
    public function createAbandonedCartRule(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $discounts=\App\Models\Discount::whereHas('creator',fn($q)=>$q->whereHas('role',fn($qq)=>$qq->where('is_admin',true)))->where(fn($qq)=>$qq->whereNull('expired_at')->orWhere('expired_at','>',time()))->get(); $groups=\App\Models\Group::where('status','active')->get(); $cats=\App\Models\Category::getCategories(); return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.abandoned-cart-form','قاعدة جديدة',array_merge(AdminMockData::shell('marketing','abandoned_cart'),['discounts'=>$discounts,'userGroups'=>$groups,'categories'=>$cats,'rule'=>null,'formAction'=>route('panel.v1.admin.marketing.abandoned-cart.store')]));}
    public function storeAbandonedCartRule(Request $request){
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u;
        $request->validate(['title'=>'required|string|max:255','target_type'=>'required','action_cycle'=>'required|numeric']);
        $data=$request->all(); $s=!empty($data['start_at'])?convertTimeToUTCzone($data['start_at'],getTimezone())->getTimestamp():null; $e=!empty($data['end_at'])?convertTimeToUTCzone($data['end_at'],getTimezone())->getTimestamp():null;
        $rule=\App\Models\AbandonedCartRule::create(['target_type'=>$data['target_type'],'target'=>$data['target']??null,'action'=>$data['action']??'send_coupon','discount_id'=>$data['discount_id']??null,'action_cycle'=>$data['action_cycle'],'repeat_action'=>!empty($data['repeat_action'])&&$data['repeat_action']=='1','repeat_action_count'=>!empty($data['repeat_action'])&&!empty($data['repeat_action_count'])?$data['repeat_action_count']:null,'minimum_cart_amount'=>!empty($data['minimum_cart_amount'])?convertPriceToDefaultCurrency($data['minimum_cart_amount']):null,'maximum_cart_amount'=>!empty($data['maximum_cart_amount'])?convertPriceToDefaultCurrency($data['maximum_cart_amount']):null,'start_at'=>$s,'end_at'=>$e,'enable'=>!empty($data['enable'])&&$data['enable']=='1','created_at'=>time()]);
        \App\Models\Translation\AbandonedCartRuleTranslation::updateOrCreate(['abandoned_cart_rule_id'=>$rule->id,'locale'=>mb_strtolower($data['locale']??app()->getLocale())],['title'=>$data['title']]);
        foreach(['category_ids'=>'category_id','instructor_ids'=>'instructor_id','seller_ids'=>'seller_id','webinar_ids'=>'webinar_id','product_ids'=>'product_id','bundle_ids'=>'bundle_id'] as $k=>$col) if(!empty($data[$k])){ $ins=[]; foreach($data[$k] as $it) $ins[]=['abandoned_cart_rule_id'=>$rule->id,$col=>$it]; if(!empty($ins)) \App\Models\AbandonedCartRuleSpecificationItem::insert($ins); }
        foreach(($data['group_ids']??[]) as $gid) if(!empty($gid)) \App\Models\AbandonedCartRuleUserGroup::create(['abandoned_cart_rule_id'=>$rule->id,'group_id'=>$gid]);
        foreach(($data['users_ids']??[]) as $uid) if(!empty($uid)) \App\Models\AbandonedCartRuleUserGroup::create(['abandoned_cart_rule_id'=>$rule->id,'user_id'=>$uid]);
        return redirect()->route('panel.v1.admin.marketing.abandoned-cart.edit',['id'=>$rule->id])->with('toast',['title'=>'تم','msg'=>'تم إنشاء القاعدة','type'=>'success']);
    }
    public function editAbandonedCartRule(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $rule=\App\Models\AbandonedCartRule::findOrFail($id); $discounts=\App\Models\Discount::whereHas('creator',fn($q)=>$q->whereHas('role',fn($qq)=>$qq->where('is_admin',true)))->where(fn($qq)=>$qq->whereNull('expired_at')->orWhere('expired_at','>',time()))->get(); $groups=\App\Models\Group::where('status','active')->get(); $cats=\App\Models\Category::getCategories(); return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.abandoned-cart-form','تعديل قاعدة',array_merge(AdminMockData::shell('marketing','abandoned_cart'),['discounts'=>$discounts,'userGroups'=>$groups,'categories'=>$cats,'rule'=>$rule,'formAction'=>route('panel.v1.admin.marketing.abandoned-cart.update',['id'=>$rule->id])]));}
    public function updateAbandonedCartRule(Request $request,int $id){
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $rule=\App\Models\AbandonedCartRule::findOrFail($id);
        $request->validate(['title'=>'required|string|max:255','target_type'=>'required','action_cycle'=>'required|numeric']);
        $data=$request->all(); $s=!empty($data['start_at'])?convertTimeToUTCzone($data['start_at'],getTimezone())->getTimestamp():null; $e=!empty($data['end_at'])?convertTimeToUTCzone($data['end_at'],getTimezone())->getTimestamp():null;
        $rule->update(['target_type'=>$data['target_type'],'target'=>$data['target']??null,'action'=>$data['action']??'send_coupon','discount_id'=>$data['discount_id']??null,'action_cycle'=>$data['action_cycle'],'repeat_action'=>!empty($data['repeat_action'])&&$data['repeat_action']=='1','repeat_action_count'=>!empty($data['repeat_action'])&&!empty($data['repeat_action_count'])?$data['repeat_action_count']:null,'minimum_cart_amount'=>!empty($data['minimum_cart_amount'])?convertPriceToDefaultCurrency($data['minimum_cart_amount']):null,'maximum_cart_amount'=>!empty($data['maximum_cart_amount'])?convertPriceToDefaultCurrency($data['maximum_cart_amount']):null,'start_at'=>$s,'end_at'=>$e,'enable'=>!empty($data['enable'])&&$data['enable']=='1']);
        \App\Models\Translation\AbandonedCartRuleTranslation::updateOrCreate(['abandoned_cart_rule_id'=>$rule->id,'locale'=>mb_strtolower($data['locale']??app()->getLocale())],['title'=>$data['title']]);
        \App\Models\AbandonedCartRuleSpecificationItem::where('abandoned_cart_rule_id',$rule->id)->delete(); foreach(['category_ids'=>'category_id','instructor_ids'=>'instructor_id','seller_ids'=>'seller_id','webinar_ids'=>'webinar_id','product_ids'=>'product_id','bundle_ids'=>'bundle_id'] as $k=>$col) if(!empty($data[$k])){ $ins=[]; foreach($data[$k] as $it) $ins[]=['abandoned_cart_rule_id'=>$rule->id,$col=>$it]; if(!empty($ins)) \App\Models\AbandonedCartRuleSpecificationItem::insert($ins); }
        \App\Models\AbandonedCartRuleUserGroup::where('abandoned_cart_rule_id',$rule->id)->delete(); foreach(($data['group_ids']??[]) as $gid) if(!empty($gid)) \App\Models\AbandonedCartRuleUserGroup::create(['abandoned_cart_rule_id'=>$rule->id,'group_id'=>$gid]); foreach(($data['users_ids']??[]) as $uid) if(!empty($uid)) \App\Models\AbandonedCartRuleUserGroup::create(['abandoned_cart_rule_id'=>$rule->id,'user_id'=>$uid]);
        return redirect()->route('panel.v1.admin.marketing.abandoned-cart.edit',['id'=>$rule->id])->with('toast',['title'=>'تم','msg'=>'تم التحديث','type'=>'success']);
    }
    public function deleteAbandonedCartRule(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\AbandonedCartRule::findOrFail($id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    // — Cart Discount — مطابق Admin\CartDiscountController:43
    public function cartDiscountIndex(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $discounts=\App\Models\Discount::whereHas('creator',fn($q)=>$q->whereHas('role',fn($qq)=>$qq->where('is_admin',true)))->where(fn($q)=>$q->whereNull('expired_at')->orWhere('expired_at','>',time()))->orderBy('created_at','desc')->get(); $cd=\App\Models\CartDiscount::first(); return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.cart-discount','خصم السلة',array_merge(AdminMockData::shell('marketing','cart_discount'),['discounts'=>$discounts,'cartDiscount'=>$cd,'stubTitle'=>'خصم السلة']));}
    public function storeCartDiscount(Request $request){
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u;
        $request->validate(['title'=>'required|string|max:255','subtitle'=>'required|string','discount_id'=>'required|exists:discounts,id']);
        $data=$request->all(); \App\Models\CartDiscount::query()->delete(); $cd=\App\Models\CartDiscount::create(['discount_id'=>$data['discount_id'],'show_only_on_empty_cart'=>!empty($data['show_only_on_empty_cart'])&&$data['show_only_on_empty_cart']=='1','enable'=>!empty($data['enable'])&&$data['enable']=='1','created_at'=>time()]);
        \App\Models\Translation\CartDiscountTranslation::updateOrCreate(['cart_discount_id'=>$cd->id,'locale'=>mb_strtolower($data['locale']??app()->getLocale())],['title'=>$data['title'],'subtitle'=>$data['subtitle']]);
        return redirect()->route('panel.v1.admin.marketing.cart-discount.index')->with('toast',['title'=>'تم','msg'=>'تم حفظ خصم السلة','type'=>'success']);
    }
    // — Registration Bonus — مطابق Admin\RegistrationBonusController:20/206
    public function registrationBonusHistory(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $settings=getRegistrationBonusSettings(); $q=\App\User::where('status',\App\User::$active)->where('enable_registration_bonus',true)->with(['role'])->withCount(['affiliates']); $ach=(clone $q)->count(); $unQ=\App\Models\Accounting::where('is_registration_bonus',true)->where('system',false); $unCnt=(clone $unQ)->count(); $tot=(clone $q)->sum('registration_bonus_amount'); $unAmt=(clone $unQ)->sum('amount'); $q=$this->registrationBonusFilters($request,$q); $p=$q->paginate(15)->withQueryString(); foreach($p as $user){ $ba=\App\Models\Accounting::where('user_id',$user->id)->where('is_registration_bonus',true)->where('system',false)->first(); $user->bonus_status=!empty($ba)?trans('update.unlock'):trans('update.lock'); } return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.section-real','سجل مكافأة التسجيل',array_merge(AdminMockData::shell('marketing','registration-bonus'),['users'=>$p,'paginator'=>$p,'achievedUsers'=>$ach,'unlockedBonusUsers'=>$unCnt,'totalBonus'=>$tot,'unlockedBonus'=>$unAmt,'registrationBonusSettings'=>$settings,'stubTitle'=>'مكافأة التسجيل']));}
    private function registrationBonusFilters($request,$query){ $from=$request->get('from'); $to=$request->get('to'); $title=$request->get('title'); $userIds=$request->get('user_ids'); $roleId=$request->get('role_id'); $bonusStatus=$request->get('bonus_status'); $sort=$request->get('sort'); $query=fromAndToDateFilter($from,$to,$query,'created_at'); if(!empty($title)) $query->where('full_name','like',"%{$title}%"); if(!empty($userIds)) $query->whereIn('id',(array)$userIds); if(!empty($roleId)) $query->where('role_id',$roleId); if(!empty($bonusStatus)){ if($bonusStatus=='locked') $query->whereDoesntHave('accounting',fn($qq)=>$qq->where('is_registration_bonus',true)->where('system',false)); elseif($bonusStatus=='unlocked') $query->whereHas('accounting',fn($qq)=>$qq->where('is_registration_bonus',true)->where('system',false)); } if(!empty($sort)){ switch($sort){ case 'registration_date_asc': $query->orderBy('created_at','asc'); break; case 'registration_date_desc': $query->orderBy('created_at','desc'); break; case 'referred_users_asc': $query->orderBy('affiliates_count','asc'); break; case 'referred_users_desc': $query->orderBy('affiliates_count','desc'); break; case 'bonus_asc': $query->orderBy('registration_bonus_amount','asc'); break; case 'bonus_desc': $query->orderBy('registration_bonus_amount','desc'); break; }} else $query->orderBy('created_at','desc'); return $query; }
    public function exportRegistrationBonus(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $q=\App\User::where('status',\App\User::$active)->where('enable_registration_bonus',true)->withCount(['affiliates']); $q=$this->registrationBonusFilters($request,$q); $users=$q->get(); return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\RegistrationBonusExport($users), 'registration_bonus.xlsx');}
    public function deleteAffiliate(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; if(class_exists(\App\Models\Affiliate::class)) \App\Models\Affiliate::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    public function deleteCashback(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; if(class_exists(\App\Models\CashbackRule::class)) \App\Models\CashbackRule::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    public function deletePoint(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\RewardAccounting::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    public function deleteGift(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; if(class_exists(\App\Models\Gift::class)) \App\Models\Gift::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }

    // ===== Abandoned Users Cart — parity Admin\AbandonedUsersCartController =====
    public function abandonedUsersCart(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $q=\App\Models\Cart::with(['creator','webinar','bundle','product'])->orderBy('created_at','desc'); $p=$q->paginate(15)->withQueryString(); return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.section-real','سلال المستخدمين المتروكة',array_merge(AdminMockData::shell('marketing','abandoned_cart'),['abandonedUsersCarts'=>$p,'paginator'=>$p,'stubTitle'=>'سلال المتروكة'])); }
    public function deleteAbandonedCart(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\Cart::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }

    // ===== Cashback Transactions/History =====
    public function cashbackTransactions(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $q=\App\Models\Accounting::where('accounting.is_cashback',true)->where('accounting.system',false)->where('accounting.type',\App\Models\Accounting::$addiction)->leftJoin('order_items','order_items.id','=','accounting.order_item_id')->select('accounting.*','order_items.total_amount as purchase_amount')->with(['user'])->orderBy('accounting.created_at','desc'); $p=$q->paginate(15)->withQueryString(); return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.section-real','معاملات الكاش باك',array_merge(AdminMockData::shell('marketing','cashback'),['cashbackTransactions'=>$p,'paginator'=>$p,'stubTitle'=>'معاملات الكاش باك'])); }
    public function cashbackHistory(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $q=\App\Models\Accounting::where('accounting.is_cashback',true)->where('accounting.system',false)->where('accounting.type',\App\Models\Accounting::$addiction)->leftJoin('order_items','accounting.order_item_id','=','order_items.id')->select('accounting.user_id',\Illuminate\Support\Facades\DB::raw('sum(accounting.amount) as total_cashback'),\Illuminate\Support\Facades\DB::raw('max(accounting.created_at) as last_cashback'),\Illuminate\Support\Facades\DB::raw('sum(order_items.total_amount) as purchase_amount'))->with(['user'])->groupBy('accounting.user_id')->orderBy('last_cashback','desc'); $p=$q->paginate(15)->withQueryString(); return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.section-real','سجل الكاش باك',array_merge(AdminMockData::shell('marketing','cashback'),['cashbackHistory'=>$p,'paginator'=>$p,'stubTitle'=>'سجل الكاش باك'])); }
    public function exportCashbackTransactions(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $rows=\App\Models\Accounting::where('accounting.is_cashback',true)->where('accounting.system',false)->where('accounting.type',\App\Models\Accounting::$addiction)->leftJoin('order_items','order_items.id','=','accounting.order_item_id')->select('accounting.*','order_items.total_amount as purchase_amount')->with(['user','webinar','bundle','product'])->orderBy('accounting.created_at','desc')->get(); return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\CashbackTransactionsExport($rows),'cashback_transactions.xlsx'); }
    public function exportCashbackHistory(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $rows=\App\Models\Accounting::where('accounting.is_cashback',true)->where('accounting.system',false)->where('accounting.type',\App\Models\Accounting::$addiction)->leftJoin('order_items','accounting.order_item_id','=','order_items.id')->select('accounting.user_id',\Illuminate\Support\Facades\DB::raw('sum(accounting.amount) as total_cashback'),\Illuminate\Support\Facades\DB::raw('max(accounting.created_at) as last_cashback'),\Illuminate\Support\Facades\DB::raw('sum(order_items.total_amount) as purchase_amount'))->with(['user'])->groupBy('accounting.user_id')->orderBy('last_cashback','desc')->get(); return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\CashbackHistoryExport($rows),'cashback_history.xlsx'); }
    public function refundCashback(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $t=\App\Models\Accounting::findOrFail($id); $cols=\Illuminate\Support\Facades\Schema::getColumnListing((new \App\Models\Accounting)->getTable()); $ins=['is_cashback'=>true,'system'=>true,'amount'=>$t->amount,'type_account'=>\App\Models\Accounting::$asset,'type'=>\App\Models\Accounting::$addiction,'description'=>'Refund '.$t->description,'created_at'=>time()]; foreach(['user_id','order_item_id','webinar_id','bundle_id','meeting_id','meeting_package_id','subscribe_id','promotion_id','registration_package_id','product_id','product_order_id','installment_payment_id','gift_id'] as $fk){ if(in_array($fk,$cols)) $ins[$fk]=$t->$fk; } \App\Models\Accounting::create($ins); $ins['type']=\App\Models\Accounting::$deduction; $ins['system']=false; \App\Models\Accounting::create($ins); return back()->with('toast',['title'=>'تم','msg'=>'تم الاسترجاع','type'=>'success']); }

    // ===== Forms Fields/Submissions =====
    public function formFields(Request $request,int $formId){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $form=\App\Models\Form::findOrFail($formId); $fields=\App\Models\FormField::where('form_id',$formId)->orderBy('order')->paginate(15)->withQueryString(); return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.section-real','حقول النموذج: '.$form->title,array_merge(AdminMockData::shell('marketing','forms'),['formM'=>$form,'formFields'=>$fields,'paginator'=>$fields,'stubTitle'=>'حقول النموذج'])); }
    public function createFormField(Request $request,int $formId){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $form=\App\Models\Form::findOrFail($formId); return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.form-field-form','حقل جديد',array_merge(AdminMockData::shell('marketing','forms'),['formM'=>$form,'field'=>null,'formAction'=>route('panel.v1.admin.marketing.form-fields.store',['formId'=>$formId])])); }
    public function storeFormField(Request $request,int $formId){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $request->validate(['title'=>'required','type'=>'required']); $data=$request->all(); $f=\App\Models\FormField::create(['form_id'=>$formId,'type'=>$data['type'],'required'=>!empty($data['required'])&&$data['required']=='on','order'=>\App\Models\FormField::where('form_id',$formId)->max('order')+1]); \App\Models\Translation\FormFieldTranslation::updateOrCreate(['form_field_id'=>$f->id,'locale'=>mb_strtolower($data['locale']??app()->getLocale())],['title'=>$data['title'],'placeholder'=>$data['placeholder']??null]); return redirect()->route('panel.v1.admin.marketing.form-fields',['formId'=>$formId])->with('toast',['title'=>'تم','msg'=>'تم الإنشاء','type'=>'success']); }
    public function editFormField(Request $request,int $formId,int $fieldId){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $form=\App\Models\Form::findOrFail($formId); $field=\App\Models\FormField::where('form_id',$formId)->where('id',$fieldId)->firstOrFail(); return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.form-field-form','تعديل حقل',array_merge(AdminMockData::shell('marketing','forms'),['formM'=>$form,'field'=>$field,'formAction'=>route('panel.v1.admin.marketing.form-fields.update',['formId'=>$formId,'fieldId'=>$fieldId])])); }
    public function updateFormField(Request $request,int $formId,int $fieldId){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $request->validate(['title'=>'required','type'=>'required']); $data=$request->all(); $f=\App\Models\FormField::where('form_id',$formId)->where('id',$fieldId)->firstOrFail(); $f->update(['type'=>$data['type'],'required'=>!empty($data['required'])&&$data['required']=='on']); \App\Models\Translation\FormFieldTranslation::updateOrCreate(['form_field_id'=>$f->id,'locale'=>mb_strtolower($data['locale']??app()->getLocale())],['title'=>$data['title'],'placeholder'=>$data['placeholder']??null]); return redirect()->route('panel.v1.admin.marketing.form-fields',['formId'=>$formId])->with('toast',['title'=>'تم','msg'=>'تم التحديث','type'=>'success']); }
    public function deleteFormField(Request $request,int $formId,int $fieldId){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\FormField::where('id',$fieldId)->where('form_id',$formId)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    public function formSubmissions(Request $request,int $formId){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $form=\App\Models\Form::findOrFail($formId); $q=\App\Models\FormSubmission::where('form_id',$formId)->with(['user','form'])->orderBy('created_at','desc'); $p=$q->paginate(15)->withQueryString(); return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.section-real','إرساليات: '.$form->title,array_merge(AdminMockData::shell('marketing','forms'),['formM'=>$form,'submissions'=>$p,'paginator'=>$p,'stubTitle'=>'الإرساليات'])); }
    public function deleteFormSubmission(Request $request,int $formId,int $submissionId){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\FormSubmission::where('id',$submissionId)->where('form_id',$formId)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    public function updateFormSubmission(Request $request,int $formId,int $submissionId){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $s=\App\Models\FormSubmission::where('id',$submissionId)->where('form_id',$formId)->firstOrFail(); foreach((array)$request->get('items',[]) as $itemId=>$value){ \App\Models\FormSubmissionItem::where('id',$itemId)->where('submission_id',$s->id)->update(['value'=>$value]); } return redirect()->route('panel.v1.admin.marketing.form-submissions.show',['formId'=>$formId,'submissionId'=>$s->id])->with('toast',['title'=>'تم','msg'=>'تم حفظ الإرسالية','type'=>'success']); }

    // ===== Blog Categories / Featured =====
    public function blogCategories(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $q=\App\Models\BlogCategory::orderBy('id','desc'); $p=$q->paginate(15)->withQueryString(); return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.section-real','تصنيفات المدونة',array_merge(AdminMockData::shell('marketing','blog'),['blogCategories'=>$p,'paginator'=>$p,'stubTitle'=>'تصنيفات المدونة'])); }
    public function createBlogCategory(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.blog-category-form','تصنيف مدونة جديد',array_merge(AdminMockData::shell('marketing','blog'),['category'=>null,'formAction'=>route('panel.v1.admin.marketing.blog-categories.store')])); }
    public function storeBlogCategory(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $request->validate(['title'=>'required']); $data=$request->all(); $slug=!empty($data['slug'])?\Illuminate\Support\Str::slug($data['slug']):\Illuminate\Support\Str::slug($data['title']).'-'.time(); $cat=\App\Models\BlogCategory::create(['slug'=>$slug]); \App\Models\Translation\BlogCategoryTranslation::updateOrCreate(['blog_category_id'=>$cat->id,'locale'=>mb_strtolower($data['locale']??app()->getLocale())],['title'=>$data['title'],'subtitle'=>$data['subtitle']??null]); return redirect()->route('panel.v1.admin.marketing.blog-categories')->with('toast',['title'=>'تم','msg'=>'تم الإنشاء','type'=>'success']); }
    public function editBlogCategory(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $cat=\App\Models\BlogCategory::findOrFail($id); return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.blog-category-form','تعديل تصنيف مدونة',array_merge(AdminMockData::shell('marketing','blog'),['category'=>$cat,'formAction'=>route('panel.v1.admin.marketing.blog-categories.update',['id'=>$cat->id])])); }
    public function updateBlogCategory(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $request->validate(['title'=>'required']); $data=$request->all(); $cat=\App\Models\BlogCategory::findOrFail($id); $cat->update(['slug'=>!empty($data['slug'])?\Illuminate\Support\Str::slug($data['slug']):$cat->slug]); \App\Models\Translation\BlogCategoryTranslation::updateOrCreate(['blog_category_id'=>$cat->id,'locale'=>mb_strtolower($data['locale']??app()->getLocale())],['title'=>$data['title'],'subtitle'=>$data['subtitle']??null]); return redirect()->route('panel.v1.admin.marketing.blog-categories')->with('toast',['title'=>'تم','msg'=>'تم التحديث','type'=>'success']); }
    public function showFormSubmission(Request $request,int $formId,int $submissionId){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $form=\App\Models\Form::findOrFail($formId); $s=\App\Models\FormSubmission::where('id',$submissionId)->where('form_id',$formId)->with(['user','items.field'])->firstOrFail(); return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.section-real','إرسالية #'.$s->id.' — '.$form->title,array_merge(AdminMockData::shell('marketing','forms'),['formM'=>$form,'submission'=>$s,'stubTitle'=>'تفاصيل الإرسالية'])); }
    public function deleteBlogCategory(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\BlogCategory::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    public function newsletterHistory(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $q=\App\Models\NewsletterHistory::orderBy('created_at','desc'); $p=$q->paginate(15)->withQueryString(); return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.section-real','سجل النشرات',array_merge(AdminMockData::shell('marketing','newsletters'),['newsletterHistories'=>$p,'paginator'=>$p,'stubTitle'=>'سجل النشرات'])); }

    // ===== Additional Pages / Blog Featured / Testimonials / ProductBadgeContents =====
    public function additionalPages(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $q=class_exists(\App\Models\AdditionalPage::class)?\App\Models\AdditionalPage::orderBy('id','desc'):\App\Models\Page::orderBy('id','desc'); $p=$q->paginate(15)->withQueryString(); return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.section-real','صفحات إضافية',array_merge(AdminMockData::shell('marketing','pages'),['additionalPages'=>$p,'paginator'=>$p,'stubTitle'=>'صفحات إضافية'])); }
    public function blogFeatured(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $q=class_exists(\App\Models\BlogFeatured::class)?\App\Models\BlogFeatured::orderBy('id','desc'):\App\Models\Blog::orderBy('id','desc'); $p=$q->paginate(15)->withQueryString(); return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.section-real','المدونة المميزة',array_merge(AdminMockData::shell('marketing','blog'),['blogFeatured'=>$p,'paginator'=>$p,'stubTitle'=>'المدونة المميزة'])); }
    public function testimonials(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $q=class_exists(\App\Models\Testimonial::class)?\App\Models\Testimonial::orderBy('id','desc'):\App\Models\Comment::orderBy('id','desc'); $p=$q->paginate(15)->withQueryString(); return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.section-real','الشهادات والآراء',array_merge(AdminMockData::shell('marketing','testimonials'),['testimonials'=>$p,'paginator'=>$p,'stubTitle'=>'الشهادات'])); }
    public function createTestimonial(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.testimonial-form','رأي جديد',array_merge(AdminMockData::shell('marketing','testimonials'),['testimonial'=>null,'formAction'=>route('panel.v1.admin.marketing.testimonials.store')])); }
    public function storeTestimonial(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $request->validate(['user_avatar'=>'required|string','user_name'=>'required|string','user_bio'=>'required|string','rate'=>'required|integer|between:0,5','comment'=>'required|string']); $data=$request->all(); if(class_exists(\App\Models\Testimonial::class)){ $t=\App\Models\Testimonial::create(['user_avatar'=>$data['user_avatar'],'rate'=>$data['rate'],'status'=>$data['status'] ?? 'active','created_at'=>time()]); \App\Models\Translation\TestimonialTranslation::updateOrCreate(['testimonial_id'=>$t->id,'locale'=>mb_strtolower($data['locale'] ?? app()->getLocale())],['user_name'=>$data['user_name'],'user_bio'=>$data['user_bio'],'comment'=>$data['comment']]); } return redirect()->route('panel.v1.admin.marketing.testimonials')->with('toast',['title'=>'تم','msg'=>'تم الإنشاء','type'=>'success']); }
    public function editTestimonial(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $t=\App\Models\Testimonial::findOrFail($id); return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.testimonial-form','تعديل رأي',array_merge(AdminMockData::shell('marketing','testimonials'),['testimonial'=>$t,'formAction'=>route('panel.v1.admin.marketing.testimonials.update',['id'=>$t->id])])); }
    public function updateTestimonial(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $request->validate(['user_avatar'=>'required|string','user_name'=>'required|string','user_bio'=>'required|string','rate'=>'required|integer|between:0,5','comment'=>'required|string']); $data=$request->all(); $t=\App\Models\Testimonial::findOrFail($id); $t->update(['user_avatar'=>$data['user_avatar'],'rate'=>$data['rate'],'status'=>$data['status'] ?? $t->status]); \App\Models\Translation\TestimonialTranslation::updateOrCreate(['testimonial_id'=>$t->id,'locale'=>mb_strtolower($data['locale'] ?? app()->getLocale())],['user_name'=>$data['user_name'],'user_bio'=>$data['user_bio'],'comment'=>$data['comment']]); return redirect()->route('panel.v1.admin.marketing.testimonials')->with('toast',['title'=>'تم','msg'=>'تم التحديث','type'=>'success']); }
    public function deleteTestimonial(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; if(class_exists(\App\Models\Testimonial::class)) \App\Models\Testimonial::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    // — Pages CRUD — مطابق Admin\PagesController:40/88
    public function createPage(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.page-form','إنشاء صفحة',array_merge(AdminMockData::shell('marketing','pages'),['page'=>null,'formAction'=>route('panel.v1.admin.marketing.pages.store')]));}
    public function storePage(Request $request){
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u;
        $request->validate(['locale'=>'required','name'=>'required','link'=>'required|unique:pages,link','title'=>'required|string','subtitle'=>'required|string','icon'=>'required|string','cover'=>'required|string','header_icon'=>'required|string','content'=>'required']);
        $link=$request->input('link'); if(substr($link,0,1)!=='/') $link='/'.$link;
        $page=\App\Models\Page::create(['link'=>$link,'name'=>$request->input('name'),'icon'=>$request->input('icon'),'cover'=>$request->input('cover'),'header_icon'=>$request->input('header_icon'),'robot'=>$request->input('robot')=='1','status'=>$request->input('status','draft'),'created_at'=>time()]);
        \App\Models\Translation\PageTranslation::updateOrCreate(['page_id'=>$page->id,'locale'=>mb_strtolower($request->input('locale'))],['title'=>$request->input('title'),'subtitle'=>$request->input('subtitle'),'seo_description'=>$request->input('seo_description'),'content'=>$request->input('content')]);
        return redirect()->route('panel.v1.admin.marketing.pages.edit',['id'=>$page->id])->with('toast',['title'=>'تم','msg'=>'تم إنشاء الصفحة','type'=>'success']);
    }
    public function editPage(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $page=\App\Models\Page::findOrFail($id); return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.page-form','تعديل صفحة: '.$page->name,array_merge(AdminMockData::shell('marketing','pages'),['page'=>$page,'formAction'=>route('panel.v1.admin.marketing.pages.update',['id'=>$page->id])]));}
    public function updatePage(Request $request,int $id){
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $page=\App\Models\Page::findOrFail($id);
        $request->validate(['locale'=>'required','name'=>'required','link'=>'required|unique:pages,link,'.$page->id,'title'=>'required|string','subtitle'=>'required|string','icon'=>'required|string','cover'=>'required|string','header_icon'=>'required|string','content'=>'required']);
        $link=$request->input('link'); if(substr($link,0,1)!=='/') $link='/'.$link;
        $page->update(['link'=>$link,'name'=>$request->input('name'),'icon'=>$request->input('icon'),'cover'=>$request->input('cover'),'header_icon'=>$request->input('header_icon'),'robot'=>$request->input('robot')=='1','status'=>$request->input('status','draft')]);
        \App\Models\Translation\PageTranslation::updateOrCreate(['page_id'=>$page->id,'locale'=>mb_strtolower($request->input('locale'))],['title'=>$request->input('title'),'subtitle'=>$request->input('subtitle'),'seo_description'=>$request->input('seo_description'),'content'=>$request->input('content')]);
        return redirect()->route('panel.v1.admin.marketing.pages.edit',['id'=>$page->id])->with('toast',['title'=>'تم','msg'=>'تم تحديث الصفحة','type'=>'success']);
    }
    // — Blog CRUD — مطابق Admin\BlogController:94/167
    public function createBlog(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $cats=\App\Models\BlogCategory::all(); return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.blog-form','إنشاء مقال',array_merge(AdminMockData::shell('marketing','blog'),['categories'=>$cats,'post'=>null,'formAction'=>route('panel.v1.admin.marketing.blog.store')]));}
    public function storeBlog(Request $request){
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u;
        $request->validate(['locale'=>'required','title'=>'required|string|max:255','subtitle'=>'required|string','category_id'=>'required|numeric','image'=>'required|string','description'=>'required|string','content'=>'required|string']);
        $blog=\App\Models\Blog::create(['slug'=>\App\Models\Blog::makeSlug($request->input('title')),'category_id'=>$request->input('category_id'),'author_id'=>$request->input('author_id')??$u->id,'study_time'=>$request->input('study_time'),'image'=>$request->input('image'),'enable_comment'=>($request->input('enable_comment')=='on'),'status'=>($request->input('status')=='on'?'publish':'pending'),'created_at'=>time(),'updated_at'=>time()]);
        \App\Models\Translation\BlogTranslation::updateOrCreate(['blog_id'=>$blog->id,'locale'=>mb_strtolower($request->input('locale'))],['title'=>$request->input('title'),'subtitle'=>$request->input('subtitle'),'description'=>$request->input('description'),'meta_description'=>$request->input('meta_description'),'content'=>$request->input('content')]);
        if($blog->status=='publish' && $blog->author_id!=$u->id) try{sendNotification('publish_instructor_blog_post',['[blog_title]'=>$blog->title],$blog->author_id);}catch(\Throwable $e){}
        return redirect()->route('panel.v1.admin.marketing.blog.edit',['id'=>$blog->id])->with('toast',['title'=>'تم','msg'=>'تم إنشاء المقال','type'=>'success']);
    }
    public function editBlog(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $post=\App\Models\Blog::findOrFail($id); $cats=\App\Models\BlogCategory::all(); return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.blog-form','تعديل مقال',array_merge(AdminMockData::shell('marketing','blog'),['categories'=>$cats,'post'=>$post,'formAction'=>route('panel.v1.admin.marketing.blog.update',['id'=>$post->id])]));}
    public function updateBlog(Request $request,int $id){
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $post=\App\Models\Blog::findOrFail($id);
        $request->validate(['title'=>'required|string|max:255','subtitle'=>'required|string','category_id'=>'required|numeric','image'=>'required|string','description'=>'required|string','content'=>'required|string']);
        $post->update(['category_id'=>$request->input('category_id'),'author_id'=>$request->input('author_id')??$post->author_id,'study_time'=>$request->input('study_time'),'image'=>$request->input('image'),'enable_comment'=>($request->input('enable_comment')=='on'),'status'=>($request->input('status')=='on'?'publish':'pending'),'updated_at'=>time()]);
        \App\Models\Translation\BlogTranslation::updateOrCreate(['blog_id'=>$post->id,'locale'=>mb_strtolower($request->input('locale')??app()->getLocale())],['title'=>$request->input('title'),'subtitle'=>$request->input('subtitle'),'description'=>$request->input('description'),'meta_description'=>$request->input('meta_description'),'content'=>$request->input('content')]);
        return redirect()->route('panel.v1.admin.marketing.blog.edit',['id'=>$post->id])->with('toast',['title'=>'تم','msg'=>'تم تحديث المقال','type'=>'success']);
    }
    // — Products CRUD — مطابق Admin\Store\ProductsController:325/401 (مبسط)
    public function createProduct(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.product-form','إنشاء منتج',array_merge(AdminMockData::shell('marketing','products'),['product'=>null,'formAction'=>route('panel.v1.admin.marketing.products.store')]));}
    public function storeProduct(Request $request){
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u;
        $request->validate(['creator_id'=>'required|exists:users,id','type'=>'required|in:'.implode(',',\App\Models\Product::$productTypes),'title'=>'required|max:255','slug'=>'nullable|max:255|unique:products,slug','seo_description'=>'required|max:255','summary'=>'required','description'=>'required']);
        $slug=$request->input('slug')?:\App\Models\Product::makeSlug($request->input('title'));
        $comm=$request->input('commission'); if(!empty($comm) && $request->input('commission_type')=='fixed_amount') $comm=convertPriceToDefaultCurrency($comm);
        $product=\App\Models\Product::create(['creator_id'=>$request->input('creator_id'),'type'=>$request->input('type'),'slug'=>$slug,'price'=>null,'status'=>\App\Models\Product::$pending,'commission_type'=>$request->input('commission_type')??'percent','commission'=>$comm,'point'=>$request->input('point'),'tax'=>$request->input('tax'),'created_at'=>time(),'updated_at'=>time()]);
        \App\Models\Translation\ProductTranslation::updateOrCreate(['product_id'=>$product->id,'locale'=>mb_strtolower($request->input('locale')??app()->getLocale())],['title'=>$request->input('title'),'seo_description'=>$request->input('seo_description'),'summary'=>$request->input('summary'),'description'=>$request->input('description')]);
        return redirect()->route('panel.v1.admin.marketing.products.edit',['id'=>$product->id])->with('toast',['title'=>'تم','msg'=>'تم إنشاء المنتج','type'=>'success']);
    }
    public function editProduct(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $product=\App\Models\Product::where('id',$id)->with(['creator'])->firstOrFail(); return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.product-form','تعديل منتج',array_merge(AdminMockData::shell('marketing','products'),['product'=>$product,'formAction'=>route('panel.v1.admin.marketing.products.update',['id'=>$product->id])]));}
    public function updateProduct(Request $request,int $id){
        $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $p=\App\Models\Product::findOrFail($id);
        $request->validate(['creator_id'=>'required|exists:users,id','type'=>'required|in:'.implode(',',\App\Models\Product::$productTypes),'title'=>'required|max:255','slug'=>'nullable|max:255|unique:products,slug,'.$p->id,'seo_description'=>'required|max:255','summary'=>'required','description'=>'required']);
        $slug=$request->input('slug')?:\App\Models\Product::makeSlug($request->input('title')); $comm=$request->input('commission'); if(!empty($comm) && $request->input('commission_type')=='fixed_amount') $comm=convertPriceToDefaultCurrency($comm);
        $p->update(['creator_id'=>$request->input('creator_id'),'type'=>$request->input('type'),'slug'=>$slug,'commission_type'=>$request->input('commission_type')??'percent','commission'=>$comm,'point'=>$request->input('point'),'tax'=>$request->input('tax'),'status'=>$request->input('status', $p->status),'updated_at'=>time()]);
        \App\Models\Translation\ProductTranslation::updateOrCreate(['product_id'=>$p->id,'locale'=>mb_strtolower($request->input('locale')??app()->getLocale())],['title'=>$request->input('title'),'seo_description'=>$request->input('seo_description'),'summary'=>$request->input('summary'),'description'=>$request->input('description')]);
        return redirect()->route('panel.v1.admin.marketing.products.edit',['id'=>$p->id])->with('toast',['title'=>'تم','msg'=>'تم تحديث المنتج','type'=>'success']);
    }
    public function deleteProduct(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\Product::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم حذف المنتج','type'=>'success']); }
    public function deleteBlog(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\Blog::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم حذف المقال','type'=>'success']); }
    public function deletePage(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\Page::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم حذف الصفحة','type'=>'success']); }
    // ===== Banners — parity مع Admin\AdvertisingBannersController =====
    public function createBanner(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.banner-form','بانر جديد',array_merge(AdminMockData::shell('marketing','banners'),['banner'=>null,'formAction'=>route('panel.v1.admin.marketing.banners.store')]));}
    public function storeBanner(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $request->validate(['title'=>'required','position'=>'required','image'=>'required','size'=>'required','link'=>'required']); $data=$request->all(); $b=\App\Models\AdvertisingBanner::create(['position'=>$data['position'],'size'=>$data['size'],'link'=>$data['link'],'published'=>$data['published'] ?? 0,'created_at'=>time()]); \App\Models\Translation\AdvertisingBannerTranslation::updateOrCreate(['advertising_banner_id'=>$b->id,'locale'=>mb_strtolower($data['locale'] ?? app()->getLocale())],['title'=>$data['title'],'image'=>$data['image']]); return redirect()->route('panel.v1.admin.marketing.banners.edit',['id'=>$b->id])->with('toast',['title'=>'تم','msg'=>'تم إنشاء البانر','type'=>'success']); }
    public function editBanner(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $b=\App\Models\AdvertisingBanner::findOrFail($id); return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.banner-form','تعديل بانر',array_merge(AdminMockData::shell('marketing','banners'),['banner'=>$b,'formAction'=>route('panel.v1.admin.marketing.banners.update',['id'=>$b->id])]));}
    public function updateBanner(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $b=\App\Models\AdvertisingBanner::findOrFail($id); $request->validate(['title'=>'required','position'=>'required','image'=>'required','size'=>'required','link'=>'required']); $data=$request->all(); $b->update(['position'=>$data['position'],'size'=>$data['size'],'link'=>$data['link'],'published'=>$data['published'] ?? 0]); \App\Models\Translation\AdvertisingBannerTranslation::updateOrCreate(['advertising_banner_id'=>$b->id,'locale'=>mb_strtolower($data['locale'] ?? app()->getLocale())],['title'=>$data['title'],'image'=>$data['image']]); return redirect()->route('panel.v1.admin.marketing.banners.edit',['id'=>$b->id])->with('toast',['title'=>'تم','msg'=>'تم التحديث','type'=>'success']); }
    public function deleteBanner(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\AdvertisingBanner::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    // ===== Floating Bars — parity مع Admin\FloatingBarController =====
    public function createFloatingBar(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.floating-bar-form','شريط عائم جديد',array_merge(AdminMockData::shell('marketing','floating_bars'),['bar'=>null,'formAction'=>route('panel.v1.admin.marketing.floating-bars.store')]));}
    public function storeFloatingBar(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $request->validate(['title'=>'required']); $data=$request->all(); $b=\App\Models\FloatingBar::create(['position'=>$data['position'] ?? 'top','enable'=>!empty($data['enable']) && $data['enable']=='on','start_at'=>null,'end_at'=>null]); \App\Models\Translation\FloatingBarTranslation::updateOrCreate(['floating_bar_id'=>$b->id,'locale'=>mb_strtolower($data['locale'] ?? app()->getLocale())],['title'=>$data['title']]); return redirect()->route('panel.v1.admin.marketing.floating-bars.edit',['id'=>$b->id])->with('toast',['title'=>'تم','msg'=>'تم إنشاء الشريط','type'=>'success']); }
    public function editFloatingBar(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $b=\App\Models\FloatingBar::findOrFail($id); return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.floating-bar-form','تعديل شريط',array_merge(AdminMockData::shell('marketing','floating_bars'),['bar'=>$b,'formAction'=>route('panel.v1.admin.marketing.floating-bars.update',['id'=>$b->id])]));}
    public function updateFloatingBar(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $b=\App\Models\FloatingBar::findOrFail($id); $request->validate(['title'=>'required']); $data=$request->all(); $b->update(['position'=>$data['position'] ?? $b->position,'enable'=>!empty($data['enable']) && $data['enable']=='on']); \App\Models\Translation\FloatingBarTranslation::updateOrCreate(['floating_bar_id'=>$b->id,'locale'=>mb_strtolower($data['locale'] ?? app()->getLocale())],['title'=>$data['title']]); return redirect()->route('panel.v1.admin.marketing.floating-bars.edit',['id'=>$b->id])->with('toast',['title'=>'تم','msg'=>'تم التحديث','type'=>'success']); }
    public function deleteFloatingBar(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\FloatingBar::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    // ===== Purchase Notifications — parity =====
    public function createPurchaseNotification(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.purchase-notification-form','إشعار شراء جديد',array_merge(AdminMockData::shell('marketing','purchase_notifications'),['notification'=>null,'formAction'=>route('panel.v1.admin.marketing.purchase-notifications.store')]));}
    public function storePurchaseNotification(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $request->validate(['title'=>'required','popup_title'=>'required','popup_duration'=>'required|numeric','popup_delay'=>'required|numeric']); $data=$request->all(); $n=\App\Models\PurchaseNotification::create(['popup_duration'=>$data['popup_duration'],'popup_delay'=>$data['popup_delay'],'display_time'=>$data['display_time'] ?? 10,'enable'=>!empty($data['enable']) && $data['enable']=='on','created_at'=>time()]); \App\Models\Translation\PurchaseNotificationTranslation::updateOrCreate(['purchase_notification_id'=>$n->id,'locale'=>mb_strtolower($data['locale'] ?? app()->getLocale())],['title'=>$data['title'],'popup_title'=>$data['popup_title'],'popup_subtitle'=>$data['popup_subtitle'] ?? null]); return redirect()->route('panel.v1.admin.marketing.purchase-notifications.edit',['id'=>$n->id])->with('toast',['title'=>'تم','msg'=>'تم إنشاء الإشعار','type'=>'success']); }
    public function editPurchaseNotification(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $n=\App\Models\PurchaseNotification::findOrFail($id); return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.purchase-notification-form','تعديل إشعار',array_merge(AdminMockData::shell('marketing','purchase_notifications'),['notification'=>$n,'formAction'=>route('panel.v1.admin.marketing.purchase-notifications.update',['id'=>$n->id])]));}
    public function updatePurchaseNotification(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $n=\App\Models\PurchaseNotification::findOrFail($id); $request->validate(['title'=>'required','popup_title'=>'required','popup_duration'=>'required|numeric','popup_delay'=>'required|numeric']); $data=$request->all(); $n->update(['popup_duration'=>$data['popup_duration'],'popup_delay'=>$data['popup_delay'],'display_time'=>$data['display_time'] ?? $n->display_time,'enable'=>!empty($data['enable']) && $data['enable']=='on']); \App\Models\Translation\PurchaseNotificationTranslation::updateOrCreate(['purchase_notification_id'=>$n->id,'locale'=>mb_strtolower($data['locale'] ?? app()->getLocale())],['title'=>$data['title'],'popup_title'=>$data['popup_title'],'popup_subtitle'=>$data['popup_subtitle'] ?? null]); return redirect()->route('panel.v1.admin.marketing.purchase-notifications.edit',['id'=>$n->id])->with('toast',['title'=>'تم','msg'=>'تم التحديث','type'=>'success']); }
    public function deletePurchaseNotification(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\PurchaseNotification::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    // ===== Product Badges — parity مع Admin\ProductBadgesController =====
    public function createProductBadge(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.product-badge-form','شارة جديدة',array_merge(AdminMockData::shell('marketing','product_badges'),['badge'=>null,'formAction'=>route('panel.v1.admin.marketing.product-badges.store')]));}
    public function storeProductBadge(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $request->validate(['title'=>'required']); $data=$request->all(); $b=\App\Models\ProductBadge::create(['enable'=>!empty($data['enable']) && $data['enable']=='on','background'=>$data['background'] ?? null,'color'=>$data['color'] ?? null,'icon'=>$data['icon'] ?? null,'start_at'=>null,'end_at'=>null]); \App\Models\Translation\ProductBadgeTranslation::updateOrCreate(['product_badge_id'=>$b->id,'locale'=>mb_strtolower($data['locale'] ?? app()->getLocale())],['title'=>$data['title']]); return redirect()->route('panel.v1.admin.marketing.product-badges.edit',['id'=>$b->id])->with('toast',['title'=>'تم','msg'=>'تم إنشاء الشارة','type'=>'success']); }
    public function editProductBadge(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $b=\App\Models\ProductBadge::findOrFail($id); return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.product-badge-form','تعديل شارة',array_merge(AdminMockData::shell('marketing','product_badges'),['badge'=>$b,'formAction'=>route('panel.v1.admin.marketing.product-badges.update',['id'=>$b->id])]));}
    public function updateProductBadge(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $b=\App\Models\ProductBadge::findOrFail($id); $request->validate(['title'=>'required']); $data=$request->all(); $b->update(['enable'=>!empty($data['enable']) && $data['enable']=='on','background'=>$data['background'] ?? $b->background,'color'=>$data['color'] ?? $b->color]); \App\Models\Translation\ProductBadgeTranslation::updateOrCreate(['product_badge_id'=>$b->id,'locale'=>mb_strtolower($data['locale'] ?? app()->getLocale())],['title'=>$data['title']]); return redirect()->route('panel.v1.admin.marketing.product-badges.edit',['id'=>$b->id])->with('toast',['title'=>'تم','msg'=>'تم التحديث','type'=>'success']); }
    public function deleteProductBadge(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\ProductBadge::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }
    // ===== Forms — parity مع Admin\FormsController =====
    public function createForm(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.form-form','نموذج جديد',array_merge(AdminMockData::shell('marketing','forms'),['formM'=>null,'formAction'=>route('panel.v1.admin.marketing.forms.store')]));}
    public function storeForm(Request $request){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $request->validate(['title'=>'required']); $data=$request->all(); $f=\App\Models\Form::create(['enable'=>false,'created_at'=>time()]); \App\Models\Translation\FormTranslation::updateOrCreate(['form_id'=>$f->id,'locale'=>mb_strtolower($data['locale'] ?? app()->getLocale())],['title'=>$data['title']]); if(!empty($data['enable'])) $f->update(['enable'=>true]); return redirect()->route('panel.v1.admin.marketing.forms.edit',['id'=>$f->id])->with('toast',['title'=>'تم','msg'=>'تم إنشاء النموذج','type'=>'success']); }
    public function editForm(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $f=\App\Models\Form::findOrFail($id); return $this->renderAdmin($request,'panel_v1.admin.pages.marketing.form-form','تعديل نموذج',array_merge(AdminMockData::shell('marketing','forms'),['formM'=>$f,'formAction'=>route('panel.v1.admin.marketing.forms.update',['id'=>$f->id])]));}
    public function updateForm(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; $f=\App\Models\Form::findOrFail($id); $request->validate(['title'=>'required']); $data=$request->all(); $f->update(['enable'=>!empty($data['enable']) && $data['enable']=='on','enable_login'=>!empty($data['enable_login']) && $data['enable_login']=='on']); \App\Models\Translation\FormTranslation::updateOrCreate(['form_id'=>$f->id,'locale'=>mb_strtolower($data['locale'] ?? app()->getLocale())],['title'=>$data['title']]); return redirect()->route('panel.v1.admin.marketing.forms.edit',['id'=>$f->id])->with('toast',['title'=>'تم','msg'=>'تم التحديث','type'=>'success']); }
    public function deleteForm(Request $request,int $id){ $u=$this->resolveAdmin($request); if($u instanceof \Illuminate\Http\RedirectResponse) return $u; \App\Models\Form::where('id',$id)->delete(); return back()->with('toast',['title'=>'تم','msg'=>'تم الحذف','type'=>'success']); }

    public function section(Request $request,string $section)
    {
        $shell = AdminMockData::shell('marketing',$section);
        $real = []; $title = $section;
        $search = trim((string)$request->input('search',''));
        switch($section){
            case 'discounts':
                $title='القسائم';
                $q = \App\Models\Discount::with(['creator'])->orderBy('created_at','desc');
                $from = $request->get('from'); $to = $request->get('to');
                $q = fromAndToDateFilter($from,$to,$q,'expired_at');
                if($search!=='') $q->where(function($qq)use($search){ $qq->where('title','like',"%{$search}%")->orWhere('code','like',"%{$search}%"); });
                if($status=$request->get('status')){ $t=time(); if($status=='expired') $q->where('expired_at','<',$t); elseif($status=='active') $q->where('expired_at','>',$t); }
                if($sort=$request->get('sort')){ switch($sort){ case 'percent_asc': $q->orderBy('percent','asc'); break; case 'percent_desc': $q->orderBy('percent','desc'); break; case 'created_at_asc': $q->orderBy('created_at','asc'); break; case 'created_at_desc': $q->orderBy('created_at','desc'); break; case 'expire_at_asc': $q->orderBy('expired_at','asc'); break; case 'expire_at_desc': $q->orderBy('expired_at','desc'); break; } }
                $real['discounts'] = $q->paginate(10)->withQueryString();
                $real['paginator'] = $real['discounts'];
                $real['stats'] = [['label'=>'الإجمالي','value'=>(string)\App\Models\Discount::count()], ['label'=>'نشط','value'=>(string)\App\Models\Discount::where('expired_at','>',time())->count()], ['label'=>'منتهي','value'=>(string)\App\Models\Discount::where('expired_at','<',time())->count()]];
                break;
            case 'affiliate':
                $title='التسويق بالعمولة';
                if(class_exists(\App\Models\Affiliate::class)){
                    $q = \App\Models\Affiliate::with(['affiliateUser','referredUser'])->orderBy('id','desc');
                    if($search!=='') $q->where('id',$search);
                    $q = fromAndToDateFilter($request->get('from'),$request->get('to'),$q,'created_at');
                    $real['affiliates'] = $q->paginate(10)->withQueryString();
                    $real['paginator'] = $real['affiliates'];
                    $real['stats'] = [['label'=>'إجمالي الإحالات','value'=>(string)\App\Models\Affiliate::count()], ['label'=>'عمولة مستحقة','value'=>handlePrice(\App\Models\Accounting::where('is_affiliate_commission',true)->where('system',false)->sum('amount'))]];
                } else { $real['affiliates']=collect(); }
                break;
            case 'registration-bonus':
                $title='مكافأة التسجيل';
                $q = \App\User::where('enable_registration_bonus',true)->orderBy('created_at','desc');
                if($search!=='') $q->where(function($qq)use($search){ $qq->where('full_name','like',"%{$search}%")->orWhere('email','like',"%{$search}%"); });
                $real['bonuses'] = $q->paginate(10)->withQueryString();
                $real['paginator'] = $real['bonuses'];
                $real['stats'] = [['label'=>'مستحقو المكافأة','value'=>(string)\App\User::where('enable_registration_bonus',true)->count()]];
                break;
            case 'cashback':
                $title='الاسترداد النقدي';
                if(class_exists(\App\Models\CashbackRule::class)){
                    $q = \App\Models\CashbackRule::orderBy('id','desc');
                    if($search!=='') $q->where('title','like',"%{$search}%");
                    $q = fromAndToDateFilter($request->get('from'),$request->get('to'),$q,'created_at');
                    if($t=$request->get('target_type')) $q->where('target_type',$t);
                    if($st=$request->get('status')) $q->where('enable',$st=='active');
                    $real['cashbacks'] = $q->paginate(10)->withQueryString();
                    $real['paginator'] = $real['cashbacks'];
                    $real['stats'] = [['label'=>'القواعد','value'=>(string)\App\Models\CashbackRule::count()], ['label'=>'نشط','value'=>(string)\App\Models\CashbackRule::where('enable',true)->count()]];
                } else { $real['cashbacks']=collect(); }
                break;
            case 'points':
                $title='النقاط';
                $q = \App\Models\RewardAccounting::with(['user'])->orderBy('id','desc');
                $q = fromAndToDateFilter($request->get('from'),$request->get('to'),$q,'created_at');
                if($search!=='') $q->whereHas('user',fn($u)=>$u->where('full_name','like',"%{$search}%"));
                $real['points'] = $q->paginate(10)->withQueryString();
                $real['paginator'] = $real['points'];
                break;
            case 'gifts':
                $title='الهدايا';
                if(class_exists(\App\Models\Gift::class)){
                    $q = \App\Models\Gift::with(['sale','user','receipt'])->where('status','!=','pending')->orderBy('id','desc');
                    if($search!=='') $q->where('id',$search)->orWhere('name','like',"%{$search}%");
                    $q = fromAndToDateFilter($request->get('from'),$request->get('to'),$q,'created_at');
                    $real['gifts'] = $q->paginate(10)->withQueryString();
                    $real['paginator'] = $real['gifts'];
                    // إحصائيات حرفية من GiftsController::getTopStats
                    $real['stats'] = [
                        ['label'=>'إجمالي الهدايا','value'=>(string)\App\Models\Gift::where('status','!=','pending')->count()],
                        ['label'=>'إجمالي المبلغ','value'=>handlePrice(\App\Models\Gift::where('status','!=','pending')->whereHas('sale')->with('sale')->get()->sum(fn($g)=>$g->sale->total_amount ?? 0))],
                        ['label'=>'المرسلون','value'=>(string)\App\Models\Gift::where('status','!=','pending')->distinct('user_id')->count('user_id')],
                        ['label'=>'المستلمون','value'=>(string)\App\Models\Gift::where('status','!=','pending')->distinct('email')->count('email')],
                    ];
                } else { $real['gifts']=collect(); }
                break;
            case 'products':
                $title='منتجات المتجر';
                $q = \App\Models\Product::with(['creator'])->orderBy('id','desc');
                if($search!=='') $q->where('id',$search)->orWhereHas('translations',fn($t)=>$t->where('title','like',"%{$search}%"));
                $q = fromAndToDateFilter($request->get('from'),$request->get('to'),$q,'created_at');
                if($cat=$request->get('category_id')) $q->where('category_id',$cat);
                if($st=$request->get('status')) $q->where('status',$st);
                $real['products'] = $q->paginate(10)->withQueryString();
                $real['paginator'] = $real['products'];
                break;
            case 'blog':
                $title='المدونة';
                $q = \App\Models\Blog::with(['category','author'])->orderBy('created_at','desc');
                if($search!=='') $q->whereHas('translations',fn($t)=>$t->where('title','like',"%{$search}%"));
                $q = fromAndToDateFilter($request->get('from'),$request->get('to'),$q,'created_at');
                if($cat=$request->get('category_id')) $q->where('category_id',$cat);
                if($st=$request->get('status')) $q->where('status',$st);
                $real['blogs'] = $q->paginate(10)->withQueryString();
                $real['paginator'] = $real['blogs'];
                $real['filterCategories'] = \App\Models\BlogCategory::all()->map(fn($c)=>['id'=>$c->id,'title'=>$c->title])->all();
                break;
            case 'pages':
                $title='الصفحات';
                $q = \App\Models\Page::orderBy('id','desc');
                if($search!=='') $q->where('id',$search)->orWhereHas('translations',fn($t)=>$t->where('title','like',"%{$search}%"));
                $real['pages'] = $q->paginate(10)->withQueryString();
                $real['paginator'] = $real['pages'];
                break;
            case 'dashboard':
                $title='لوحة التسويق';
                $real['stats'] = [
                    ['label'=>'القسائم','value'=>(string)\App\Models\Discount::count()],
                    ['label'=>'المدونة','value'=>(string)\App\Models\Blog::count()],
                    ['label'=>'الصفحات','value'=>(string)\App\Models\Page::count()],
                    ['label'=>'المنتجات','value'=>(string)\App\Models\Product::count()],
                    ['label'=>'الهدايا','value'=>class_exists(\App\Models\Gift::class)?(string)\App\Models\Gift::count():'0'],
                ];
                break;
            case 'tools':
                $title='أدوات التسويق';
                // أدوات حقيقية — كل أداة مربوطة بجدولها وعدّادها الفعلي
                $toolsStats = [];
                try { $toolsStats['forms'] = \App\Models\Form::count(); } catch (\Throwable $e) { $toolsStats['forms'] = 0; }
                try { $toolsStats['banners'] = \App\Models\AdvertisingBanner::count(); } catch (\Throwable $e) { $toolsStats['banners'] = 0; }
                try { $toolsStats['floating'] = \App\Models\FloatingBar::count(); } catch (\Throwable $e) { $toolsStats['floating'] = 0; }
                try { $toolsStats['purchase_notifications'] = \App\Models\PurchaseNotification::count(); } catch (\Throwable $e) { $toolsStats['purchase_notifications'] = 0; }
                $real['tools'] = collect([
                    ['title'=>'النماذج','key'=>'forms','desc'=>'إدارة النماذج وحقولها — Form + fields','count'=>$toolsStats['forms'],'icon'=>'icon-[tabler--forms]','section'=>'forms'],
                    ['title'=>'البانرات الإعلانية','key'=>'banners','desc'=>'AdvertisingBanners — position/size/link','count'=>$toolsStats['banners'],'icon'=>'icon-[tabler--photo]','section'=>'banners'],
                    ['title'=>'النوافذ المنبثقة','key'=>'modals','desc'=>'AdvertisingModal — Setting advertising_modal','count'=>1,'icon'=>'icon-[tabler--window]','section'=>'advertising_modal'],
                    ['title'=>'الشريط العائم','key'=>'floating','desc'=>'FloatingBar — start/end/background/btn','count'=>$toolsStats['floating'],'icon'=>'icon-[tabler--layout-navbar]','section'=>'floating_bars'],
                    ['title'=>'إشعارات الشراء','key'=>'purchase_notifications','desc'=>'PurchaseNotification — popup_delay/contents','count'=>$toolsStats['purchase_notifications'],'icon'=>'icon-[tabler--bell-ringing]','section'=>'purchase_notifications'],
                    ['title'=>'سلة التسوق المتروكة','key'=>'abandoned_cart','desc'=>'AbandonedCartRules + Cart','count'=>0,'icon'=>'icon-[tabler--shopping-cart-off]','section'=>'abandoned_cart'],
                    ['title'=>'خصم السلة','key'=>'cart_discount','desc'=>'CartDiscount — singleton','count'=>0,'icon'=>'icon-[tabler--discount-2]','section'=>'cart_discount'],
                    ['title'=>'الإعلانات','key'=>'advertise','desc'=>'General advertise settings','count'=>0,'icon'=>'icon-[tabler--speakerphone]','section'=>'advertise'],
                ]);
                break;
            // — أدوات فرعية — كل واحدة بمنطقها الحقيقي
            case 'forms':
                $title='النماذج';
                $q = \App\Models\Form::withCount(['fields','submissions'])->orderBy('created_at','desc');
                if($search!=='') $q->where('title','like',"%{$search}%")->orWhere('url','like',"%{$search}%");
                $real['forms'] = $q->paginate(10)->withQueryString();
                $real['paginator'] = $real['forms'];
                $real['stats'] = [['label'=>'النماذج','value'=>(string)\App\Models\Form::count()], ['label'=>'الحقول','value'=>(string)\App\Models\FormField::count()], ['label'=>'التقديمات','value'=>(string)\App\Models\FormSubmission::count()]];
                break;
            case 'banners':
                $title='البانرات الإعلانية';
                $q = \App\Models\AdvertisingBanner::orderBy('created_at','desc');
                if($search!=='') $q->where('title','like',"%{$search}%");
                $real['banners'] = $q->paginate(10)->withQueryString();
                $real['paginator'] = $real['banners'];
                break;
            case 'floating_bars':
                $title='الشريط العائم';
                $q = \App\Models\FloatingBar::orderBy('id','desc');
                $real['floatingBars'] = $q->paginate(10)->withQueryString();
                $real['paginator'] = $real['floatingBars'];
                break;
            case 'purchase_notifications':
                $title='إشعارات الشراء';
                $q = \App\Models\PurchaseNotification::withCount(['webinars','bundles','products'])->orderBy('created_at','desc');
                if($search!=='') $q->where('title','like',"%{$search}%");
                $real['purchaseNotifications'] = $q->paginate(10)->withQueryString();
                $real['paginator'] = $real['purchaseNotifications'];
                break;
            case 'special_offers':
                $title='العروض الخاصة';
                $q = \App\Models\SpecialOffer::with(['webinar'])->orderBy('id','desc');
                if($search!=='') $q->where('id',$search);
                $real['specialOffers'] = $q->paginate(10)->withQueryString();
                $real['paginator'] = $real['specialOffers'];
                break;
            case 'cart_discount':
                $title='خصم السلة';
                $q = \App\Models\CartDiscount::with(['discount'])->orderBy('id','desc');
                $real['cartDiscounts'] = $q->paginate(10)->withQueryString();
                $real['paginator'] = $real['cartDiscounts'];
                $real['stats'] = [['label'=>'قواعد خصم السلة','value'=>(string)\App\Models\CartDiscount::count()]];
                break;
            case 'abandoned_cart':
                $title='السلة المتروكة';
                $q = \App\Models\Cart::with(['creator'])->select('creator_id', \DB::raw('count(*) as total_items'))->groupBy('creator_id')->orderBy('creator_id','desc');
                if($search!=='') $q->having('creator_id',$search);
                $real['abandonedCarts'] = $q->paginate(10)->withQueryString();
                $real['paginator'] = $real['abandonedCarts'];
                $real['stats'] = [['label'=>'سلال متروكة','value'=>(string)\App\Models\Cart::distinct('creator_id')->count('creator_id')]];
                break;
            case 'newsletters':
                $title='النشرات البريدية';
                $q = \App\Models\Newsletter::orderBy('created_at','desc');
                if($search!=='') $q->where('email','like',"%{$search}%");
                $real['newsletters'] = $q->paginate(10)->withQueryString();
                $real['paginator'] = $real['newsletters'];
                $real['stats'] = [['label'=>'المشتركون','value'=>(string)\App\Models\Newsletter::count()]];
                break;
            case 'product_badges':
                $title='شارات المنتجات';
                $q = \App\Models\ProductBadge::withCount(['contents'])->orderBy('id','desc');
                if($search!=='') $q->where('title','like',"%{$search}%");
                $real['productBadges'] = $q->paginate(10)->withQueryString();
                $real['paginator'] = $real['productBadges'];
                break;
            case 'advertising_modal':
                $title='النافذة الإعلانية';
                $setting = \App\Models\Setting::where('name','advertising_modal')->first();
                $real['advertisingModal'] = $setting ?? new \App\Models\Setting();
                $real['stats'] = [['label'=>'الحالة','value'=>$setting ? 'مفعّل' : 'غير مُعد']];
                break;
            case 'abandoned_users':
                $title='سلال المستخدمين المتروكة'; $q=\App\Models\Cart::with(['creator','webinar','product','bundle'])->orderBy('created_at','desc'); if($search!=='') $q->where('creator_id',$search); $real['abandonedUsers']=$q->paginate(15)->withQueryString(); $real['paginator']=$real['abandonedUsers']; break;
            case 'cashback_transactions':
                $title='معاملات الكاش باك'; $q=\App\Models\Accounting::where('is_cashback',true)->where('system',false)->where('type',\App\Models\Accounting::$addiction)->orderBy('created_at','desc'); $real['cashbackTransactions']=$q->paginate(15)->withQueryString(); $real['paginator']=$real['cashbackTransactions']; break;
            case 'cashback_history':
                $title='سجل الكاش باك'; $q=\App\Models\Accounting::where('is_cashback',true)->where('system',false)->where('type',\App\Models\Accounting::$addiction)->selectRaw('user_id, sum(amount) as total_cashback, max(created_at) as last_cashback')->groupBy('user_id')->orderBy('last_cashback','desc'); $real['cashbackHistory']=$q->paginate(15)->withQueryString(); $real['paginator']=$real['cashbackHistory']; break;
            case 'form_fields':
                $title='حقول النماذج'; $q=\App\Models\FormField::with(['form'])->orderBy('id','desc'); $real['formFields']=$q->paginate(15)->withQueryString(); $real['paginator']=$real['formFields']; break;
            case 'form_submissions':
                $title='إرساليات النماذج'; $q=\App\Models\FormSubmission::with(['form','user'])->orderBy('created_at','desc'); $real['formSubmissions']=$q->paginate(15)->withQueryString(); $real['paginator']=$real['formSubmissions']; break;
            case 'newsletter_history':
                $title='سجل النشرات'; $q=\App\Models\NewsletterHistory::orderBy('created_at','desc'); $real['newsletterHistory']=$q->paginate(15)->withQueryString(); $real['paginator']=$real['newsletterHistory']; break;
            default:
                $meta = AdminMockData::stubMeta('marketing',$section);
                $title = $meta['stubTitle'] ?? $section;
                $real = $meta;
                break;
        }
        $data = array_merge($shell,$real,['stubTitle'=>$title,'pageTitle'=>$title]);

        $realSections = ['discounts','affiliate','registration-bonus','cashback','points','gifts','dashboard','tools','products','blog','pages','special_offers','forms','banners','floating_bars','purchase_notifications','cart_discount','abandoned_cart','abandoned_users','cashback_transactions','cashback_history','form_fields','form_submissions','newsletter_history','newsletters','product_badges','advertising_modal'];
        $view = in_array($section,$realSections) ? 'panel_v1.admin.pages.marketing.section-real' : 'panel_v1.admin.pages.marketing.stub';
        if(!view()->exists($view)) $view='panel_v1.admin.pages.marketing.stub';
        return $this->renderAdmin($request,$view,$data['stubTitle'],$data);
    }
}
