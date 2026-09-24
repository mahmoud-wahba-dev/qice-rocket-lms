@extends('panel_v1.admin.layouts.app')

@section('content')
<div class="space-y-6 pb-8">
    @include('panel_v1.admin.components.page-header', [
        'title' => $stubTitle ?? $pageTitle ?? 'التسويق',
        'subtitle' => $pageSubtitle ?? '',
    ])

    @include('panel_v1.admin.components.filter-bar')

    @if (!empty($stats))
        @include('panel_v1.admin.components.stats-cards', ['stats' => $stats])
    @endif

    {{-- القسائم — يظهر دائماً عند دخول /discounts حتى لو فارغ --}}
    @isset($discounts)
        @php $discountStatus = request('status'); $discountSort = request('sort'); @endphp
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex flex-col sm:flex-row gap-3 sm:items-center justify-between">
                <h2 class="font-bold text-16px text-primary">القسائم — {{ $discounts->total() }} قسيمة</h2>
                <a href="{{ route('panel.v1.admin.marketing.discounts.create') }}" class="h-10 px-4 rounded-12px bg-primary text-white font-bold text-13px center shrink-0">+ إضافة قسيمة</a>
            </div>
            <div class="px-4 sm:px-6 py-3 bg-white border-b border-d9 flex flex-wrap gap-2 items-center">
                <form method="GET" action="{{ url()->current() }}" class="flex flex-wrap gap-2 w-full">
                    @foreach(request()->except(['status','sort','from','to','page']) as $k=>$v) @if(!is_array($v))<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endif @endforeach
                    <input type="date" name="from" value="{{ request('from') }}" class="input input-bordered h-9 rounded-10px border-d9 text-12px bg-white" placeholder="من">
                    <input type="date" name="to" value="{{ request('to') }}" class="input input-bordered h-9 rounded-10px border-d9 text-12px bg-white" placeholder="إلى">
                    <select name="status" onchange="this.form.submit()" class="select select-bordered h-9 rounded-10px border-d9 text-12px bg-white"><option value="">كل الحالات</option><option value="active" @selected($discountStatus=='active')>نشط (غير منتهي)</option><option value="expired" @selected($discountStatus=='expired')>منتهي</option></select>
                    <select name="sort" onchange="this.form.submit()" class="select select-bordered h-9 rounded-10px border-d9 text-12px bg-white"><option value="">الترتيب</option><option value="percent_desc" @selected($discountSort=='percent_desc')>النسبة تنازلي</option><option value="percent_asc" @selected($discountSort=='percent_asc')>تصاعدي</option><option value="created_at_desc" @selected($discountSort=='created_at_desc')>الأحدث</option><option value="expire_at_desc" @selected($discountSort=='expire_at_desc')>الانتهاء الأقرب</option></select>
                    @if(request('from')||request('to')||request('status')||request('sort'))<a href="{{ url()->current() }}" class="h-9 px-3 rounded-10px border border-d9 bg-white text-12px font-semibold text-primary center">مسح</a>@endif
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-13px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-3 py-3 text-start">الكود</th><th class="px-3 py-3 text-start">العنوان</th><th class="px-3 py-3 text-center">النسبة</th><th class="px-3 py-3 text-center">المبلغ</th><th class="px-3 py-3 text-center">المتبقي</th><th class="px-3 py-3 text-center">الانتهاء</th><th class="px-3 py-3 text-center">الحالة</th><th class="px-3 py-3 text-center">إجراء</th></tr></thead>
                    <tbody>
                    @forelse ($discounts as $d)
                        @php $isExpired = $d->expired_at < time(); $statusLabel = $isExpired ? 'منتهي' : 'نشط'; $statusClass = $isExpired ? 'bg-[#FEE2E2] text-[#DC2626]' : 'bg-[#D1FAE5] text-[#059669]'; @endphp
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-3 py-3 font-mono font-bold text-primary text-12px">{{ $d->code }}</td>
                            <td class="px-3 py-3 font-medium text-primary truncate max-w-[14rem]">{{ $d->title }}</td>
                            <td class="px-3 py-3 text-center font-bold text-primary">{{ $d->percent }}%</td>
                            <td class="px-3 py-3 text-center font-medium text-gray">{{ $d->amount ? handlePrice($d->amount) : '—' }}</td>
                            <td class="px-3 py-3 text-center font-medium text-gray">{{ method_exists($d, 'discountRemain') ? $d->discountRemain() : $d->count }}</td>
                            <td class="px-3 py-3 text-center font-medium text-11px text-gray whitespace-nowrap">{{ date('Y/m/d', (int)$d->expired_at) }}</td>
                            <td class="px-3 py-3 text-center"><span class="inline-flex rounded-full px-2.5 py-1 font-bold text-11px {{ $statusClass }}">{{ $statusLabel }}</span></td>
                            <td class="px-3 py-3 text-center">@include('panel_v1.components.actions-dropdown', ['id' => 'mkt-discount-'.$d->id, 'items' => [['label' => 'تعديل', 'url' => route('panel.v1.admin.marketing.discounts.edit',['id'=>$d->id]), 'tone' => 'gray'], ['label' => 'حذف', 'action' => route('panel.v1.admin.marketing.discounts.delete',['id'=>$d->id]), 'confirm' => 'حذف القسيمة؟', 'tone' => 'danger']]])</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-14 text-center"><p class="font-semibold text-14px text-primary mb-2">لا توجد قسائم بعد</p><p class="font-medium text-13px text-gray mb-4">أنشئ أول قسيمة خصم وحدد النسبة والمصدر وتاريخ الانتهاء — المنطق مطابق لـ DiscountController</p><a href="{{ route('panel.v1.admin.marketing.discounts.create') }}" class="inline-flex h-9 px-4 rounded-10px bg-primary text-white font-bold text-12px center">+ إنشاء قسيمة</a></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endisset

    @isset($affiliates)
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex items-center justify-between"><h2 class="font-bold text-16px text-primary">التسويق بالعمولة — {{ $affiliates->total() }}</h2><span class="font-medium text-12px text-gray">سجل الإحالات (Affiliate)</span></div>
            <div class="overflow-x-auto"><table class="table w-full text-14px"><thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">#</th><th class="px-4 py-3 text-start">المُحيل</th><th class="px-4 py-3 text-start">المسجل</th><th class="px-4 py-3 text-center">العمولة</th><th class="px-4 py-3 text-center">التاريخ</th><th class="px-4 py-3 text-center">إجراء</th></tr></thead><tbody>
                @forelse($affiliates as $a)<tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">#{{ $a->id }}</td><td class="px-4 py-3 font-medium text-primary">{{ $a->affiliateUser->full_name ?? $a->affiliate_user_id }}</td><td class="px-4 py-3 font-medium text-primary">{{ $a->referredUser->full_name ?? $a->referred_user_id }}</td><td class="px-4 py-3 text-center font-semibold text-primary">{{ handlePrice($a->amount ?? 0) }}</td><td class="px-4 py-3 text-center font-medium text-gray">{{ date('Y/m/d', (int)$a->created_at) }}</td><td class="px-4 py-3 text-center">@include('panel_v1.components.actions-dropdown', ['id' => 'mkt-aff-'.$a->id, 'items' => [['label' => 'حذف', 'action' => route('panel.v1.admin.marketing.affiliates.delete',['id'=>$a->id]), 'confirm' => 'حذف؟', 'tone' => 'danger']]])</td></tr>@empty<tr><td colspan="6" class="px-4 py-14 text-center font-medium text-gray">لا توجد إحالات بعد — يسجل النظام تلقائياً عند تسجيل عميل عبر رابط الإحالة</td></tr>@endforelse
            </tbody></table></div>
        </div>
    @endisset

    @isset($cashbacks)
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]"><h2 class="font-bold text-16px text-primary">الاسترداد النقدي — قواعد CashbackRule</h2></div>
            <div class="overflow-x-auto"><table class="table w-full text-14px"><thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">#</th><th class="px-4 py-3 text-start">القاعدة</th><th class="px-4 py-3 text-center">النسبة/المبلغ</th><th class="px-4 py-3 text-center">الحالة</th><th class="px-4 py-3 text-center">إجراء</th></tr></thead><tbody>
                @forelse($cashbacks as $c)<tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">#{{ $c->id }}</td><td class="px-4 py-3 font-medium text-primary">{{ $c->title ?? 'قاعدة #'.$c->id }}</td><td class="px-4 py-3 text-center font-semibold text-primary">{{ $c->amount ?? '—' }} {{ $c->amount_type ?? '' }}</td><td class="px-4 py-3 text-center"><span class="inline-flex rounded-full px-3 py-1 font-semibold text-11px {{ $c->enable ? 'bg-[#D1FAE5] text-[#059669]' : 'bg-[#FEE2E2] text-[#DC2626]' }}">{{ $c->enable ? 'نشط' : 'معطل' }}</span></td><td class="px-4 py-3 text-center">@include('panel_v1.components.actions-dropdown', ['id' => 'mkt-cashback-'.$c->id, 'items' => [['label' => 'تفعيل/تعطيل', 'action' => route('panel.v1.admin.marketing.cashback.toggle',['id'=>$c->id]), 'tone' => 'gray'], ['label' => 'حذف', 'action' => route('panel.v1.admin.marketing.cashbacks.delete',['id'=>$c->id]), 'confirm' => 'حذف؟', 'tone' => 'danger']]])</td></tr>@empty<tr><td colspan="5" class="px-4 py-14 text-center font-medium text-gray">لا توجد قواعد استرداد — تُنشأ من لوحة CashbackRuleController القديمة (target_type/category/product...)</td></tr>@endforelse
            </tbody></table></div>
        </div>
    @endisset

    @isset($points)
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]"><h2 class="font-bold text-16px text-primary">نقاط المكافآت — RewardAccounting</h2></div>
            <div class="overflow-x-auto"><table class="table w-full text-14px"><thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">#</th><th class="px-4 py-3 text-start">المستخدم</th><th class="px-4 py-3 text-center">النقاط</th><th class="px-4 py-3 text-center">النوع</th><th class="px-4 py-3 text-center">التاريخ</th><th class="px-4 py-3 text-center">إجراء</th></tr></thead><tbody>
                @forelse($points as $p)<tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">#{{ $p->id }}</td><td class="px-4 py-3 font-medium text-primary">{{ $p->user->full_name ?? $p->user_id }}</td><td class="px-4 py-3 text-center font-bold text-primary">{{ $p->score }}</td><td class="px-4 py-3 text-center font-medium text-gray">{{ $p->item ?? $p->type }}</td><td class="px-4 py-3 text-center font-medium text-gray">{{ date('Y/m/d', (int)$p->created_at) }}</td><td class="px-4 py-3 text-center">@include('panel_v1.components.actions-dropdown', ['id' => 'mkt-points-'.$p->id, 'items' => [['label' => 'حذف', 'action' => route('panel.v1.admin.marketing.points.delete',['id'=>$p->id]), 'confirm' => 'حذف؟', 'tone' => 'danger']]])</td></tr>@empty<tr><td colspan="6" class="px-4 py-14 text-center font-medium text-gray">لا توجد حركات نقاط بعد — تُسجل تلقائياً عند الإنجاز/الشراء</td></tr>@endforelse
            </tbody></table></div>
        </div>
    @endisset

    @isset($gifts)
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]"><h2 class="font-bold text-16px text-primary">الهدايا — Gift (sale != pending)</h2></div>
            <div class="overflow-x-auto"><table class="table w-full text-14px"><thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">#</th><th class="px-4 py-3 text-start">المرسل</th><th class="px-4 py-3 text-center">المستلم</th><th class="px-4 py-3 text-center">الحالة</th><th class="px-4 py-3 text-center">التاريخ</th><th class="px-4 py-3 text-center">إجراء</th></tr></thead><tbody>
                @forelse($gifts as $g)<tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">#{{ $g->id }}</td><td class="px-4 py-3 font-medium text-primary">{{ $g->user->full_name ?? $g->user_id }}</td><td class="px-4 py-3 text-center font-medium text-primary">{{ $g->email ?? $g->receipt->email ?? '—' }}</td><td class="px-4 py-3 text-center"><span class="inline-flex rounded-full px-3 py-1 font-semibold text-11px bg-[#D1FAE5] text-[#059669]">{{ $g->status }}</span></td><td class="px-4 py-3 text-center font-medium text-gray">{{ date('Y/m/d', (int)$g->created_at) }}</td><td class="px-4 py-3 text-center">@include('panel_v1.components.actions-dropdown', ['id' => 'mkt-gift-'.$g->id, 'items' => [['label' => 'تذكير', 'action' => route('panel.v1.admin.marketing.gifts.reminder',['id'=>$g->id]), 'tone' => 'gray'], ['label' => 'إلغاء', 'action' => route('panel.v1.admin.marketing.gifts.cancel',['id'=>$g->id]), 'confirm' => 'إلغاء الهدية مع الاسترداد؟', 'tone' => 'danger'], ['label' => 'حذف', 'action' => route('panel.v1.admin.marketing.gifts.delete',['id'=>$g->id]), 'confirm' => 'حذف؟', 'tone' => 'danger']]])</td></tr>@empty<tr><td colspan="6" class="px-4 py-14 text-center font-medium text-gray">لا توجد هدايا — تُنشأ عند إهداء دورة/حزمة عبر GiftController</td></tr>@endforelse
            </tbody></table></div>
        </div>
    @endisset

    @isset($bonuses)
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]"><h2 class="font-bold text-16px text-primary">مكافآت التسجيل — enable_registration_bonus</h2></div>
            <div class="overflow-x-auto"><table class="table w-full text-14px"><thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">المستخدم</th><th class="px-4 py-3 text-center">الإيميل</th><th class="px-4 py-3 text-center">العضوية</th><th class="px-4 py-3 text-center">تاريخ التسجيل</th></tr></thead><tbody>
                @forelse($bonuses as $b)<tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $b->full_name }}</td><td class="px-4 py-3 text-center font-medium text-gray">{{ $b->email }}</td><td class="px-4 py-3 text-center font-medium text-gray">{{ $b->role_name }}</td><td class="px-4 py-3 text-center font-medium text-gray">{{ date('Y/m/d', (int)$b->created_at) }}</td></tr>@empty<tr><td colspan="4" class="px-4 py-14 text-center font-medium text-gray">لا يوجد مستحقون — يُفعّل عبر enable_registration_bonus</td></tr>@endforelse
            </tbody></table></div>
        </div>
    @endisset

    @isset($products)
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex items-center justify-between"><h2 class="font-bold text-16px text-primary">منتجات المتجر — {{ $products->total() }}</h2><span class="font-medium text-12px text-gray">منطق Store/ProductsController</span></div>
            <div class="overflow-x-auto"><table class="table w-full text-14px"><thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">المنتج</th><th class="px-4 py-3 text-center">السعر</th><th class="px-4 py-3 text-center">الحالة</th><th class="px-4 py-3 text-center">إجراء</th></tr></thead><tbody>
                @forelse($products as $pd)<tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $pd->title ?? $pd->slug }}</td><td class="px-4 py-3 text-center font-semibold text-primary">{{ $pd->price ? handlePrice($pd->price) : '—' }}</td><td class="px-4 py-3 text-center"><span class="inline-flex rounded-full bg-[#D1FAE5] text-[#059669] px-3 py-1 font-semibold text-11px">{{ $pd->status ?? 'نشط' }}</span></td><td class="px-4 py-3 text-center">@include('panel_v1.components.actions-dropdown', ['id' => 'mkt-product-'.$pd->id, 'items' => [['label' => 'حذف', 'action' => route('panel.v1.admin.marketing.products.delete',['id'=>$pd->id]), 'confirm' => 'حذف؟', 'tone' => 'danger']]])</td></tr>@empty<tr><td colspan="4" class="px-4 py-14 text-center font-medium text-gray">لا توجد منتجات — تُنشأ من المتجر</td></tr>@endforelse
            </tbody></table></div>
        </div>
    @endisset

    @isset($blogs)
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex items-center justify-between"><h2 class="font-bold text-16px text-primary">المدونة — {{ $blogs->total() }}</h2><span class="font-medium text-12px text-gray">BlogController حرفياً</span></div>
            <div class="overflow-x-auto"><table class="table w-full text-14px"><thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">المقال</th><th class="px-4 py-3 text-start">التصنيف</th><th class="px-4 py-3 text-center">الحالة</th><th class="px-4 py-3 text-center">التاريخ</th><th class="px-4 py-3 text-center">إجراء</th></tr></thead><tbody>
                @forelse($blogs as $bg)<tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $bg->title }}</td><td class="px-4 py-3 text-center font-medium text-gray">{{ $bg->category->title ?? '—' }}</td><td class="px-4 py-3 text-center"><span class="inline-flex rounded-full bg-[#D1FAE5] text-[#059669] px-3 py-1 font-semibold text-11px">{{ $bg->status }}</span></td><td class="px-4 py-3 text-center font-medium text-gray">{{ date('Y/m/d',(int)$bg->created_at) }}</td><td class="px-4 py-3 text-center">@include('panel_v1.components.actions-dropdown', ['id' => 'mkt-blog-'.$bg->id, 'items' => [['label' => 'حذف', 'action' => route('panel.v1.admin.marketing.blog.delete',['id'=>$bg->id]), 'confirm' => 'حذف؟', 'tone' => 'danger']]])</td></tr>@empty<tr><td colspan="5" class="px-4 py-14 text-center"><p class="font-semibold text-14px text-primary">لا توجد مقالات</p><p class="font-medium text-12px text-gray mt-1">المدونة تُدار عبر BlogController — الفلترة حسب التصنيف/الحالة/التاريخ</p></td></tr>@endforelse
            </tbody></table></div>
        </div>
    @endisset

    @isset($pages)
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]"><h2 class="font-bold text-16px text-primary">الصفحات — {{ $pages->total() }}</h2></div>
            <div class="overflow-x-auto"><table class="table w-full text-14px"><thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">الصفحة</th><th class="px-4 py-3 text-center">الرابط</th><th class="px-4 py-3 text-center">إجراء</th></tr></thead><tbody>
                @forelse($pages as $pg)<tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $pg->title ?? 'صفحة #'.$pg->id }}</td><td class="px-4 py-3 text-center font-medium text-gray" dir="ltr">{{ $pg->link ?? $pg->slug ?? '—' }}</td><td class="px-4 py-3 text-center">@include('panel_v1.components.actions-dropdown', ['id' => 'mkt-page-'.$pg->id, 'items' => [['label' => 'حذف', 'action' => route('panel.v1.admin.marketing.pages.delete',['id'=>$pg->id]), 'confirm' => 'حذف؟', 'tone' => 'danger']]])</td></tr>@empty<tr><td colspan="3" class="px-4 py-14 text-center font-medium text-gray">لا توجد صفحات — تُنشأ من PagesController</td></tr>@endforelse
            </tbody></table></div>
        </div>
    @endisset

    @isset($specialOffers)
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex items-center justify-between"><h2 class="font-bold text-16px text-primary">العروض الخاصة — SpecialOffer</h2><a href="{{ route('panel.v1.admin.marketing.special-offers.create') }}" class="h-10 px-4 rounded-12px bg-primary text-white font-bold text-13px center shrink-0">+ عرض جديد</a></div>
            <div class="overflow-x-auto"><table class="table w-full text-14px"><thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">الدورة</th><th class="px-4 py-3 text-center">النسبة</th><th class="px-4 py-3 text-center">الحالة</th><th class="px-4 py-3 text-center">التاريخ</th></tr></thead><tbody>
                @forelse($specialOffers as $so)<tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $so->webinar->title ?? 'عرض #'.$so->id }}</td><td class="px-4 py-3 text-center font-bold text-primary">{{ $so->percent }}%</td><td class="px-4 py-3 text-center"><span class="inline-flex rounded-full px-3 py-1 font-semibold text-11px {{ $so->status=='active' ? 'bg-[#D1FAE5] text-[#059669]' : 'bg-[#FEE2E2] text-[#DC2626]' }}">{{ $so->status }}</span></td><td class="px-4 py-3 text-center font-medium text-gray">{{ date('Y/m/d', (int)$so->created_at) }}</td></tr>@empty<tr><td colspan="4" class="px-4 py-14 text-center font-medium text-gray">لا توجد عروض خاصة — تُدار من SpecialOfferController</td></tr>@endforelse
            </tbody></table></div>
        </div>
    @endisset

    @isset($tools)
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]"><h2 class="font-bold text-16px text-primary">أدوات التسويق — {{ $tools->count() }} أداة حقيقية</h2><p class="font-medium text-12px text-gray">كل أداة مربوطة بجدولها الفعلي وعدادها الحي</p></div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 p-6">
                @foreach($tools as $tool)
                    <a href="{{ route('panel.v1.admin.marketing.section',['section'=>$tool['section']]) }}" class="rounded-14px border border-d9 bg-[#FAF8F4] p-5 text-center hover:shadow-md hover:border-primary/20 transition block">
                        <div class="size-12 rounded-12px bg-white border border-d9 center mx-auto mb-3"><span class="{{ $tool['icon'] }} size-6 text-primary"></span></div>
                        <p class="font-bold text-14px text-primary">{{ $tool['title'] }}</p>
                        <p class="font-medium text-12px text-gray mt-1">{{ $tool['desc'] }}</p>
                        <span class="inline-flex mt-3 rounded-full bg-primary text-white px-3 py-1 font-bold text-11px">{{ $tool['count'] }} سجل</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endisset

    @isset($forms)
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex items-center justify-between"><h2 class="font-bold text-16px text-primary">النماذج — {{ $forms->total() }}</h2><a href="{{ route('panel.v1.admin.marketing.forms.create') }}" class="h-10 px-4 rounded-12px bg-primary text-white font-bold text-13px center">+ نموذج جديد</a></div>
            <div class="overflow-x-auto"><table class="table w-full text-14px"><thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">النموذج</th><th class="px-4 py-3 text-center">الحقول</th><th class="px-4 py-3 text-center">التقديمات</th><th class="px-4 py-3 text-center">الرابط</th><th class="px-4 py-3 text-center">إجراءات</th></tr></thead><tbody>
                @forelse($forms as $f)<tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $f->title ?? 'نموذج #'.$f->id }}</td><td class="px-4 py-3 text-center font-semibold text-primary">{{ $f->fields_count ?? '—' }}</td><td class="px-4 py-3 text-center font-semibold text-primary">{{ $f->submissions_count ?? '—' }}</td><td class="px-4 py-3 text-center font-mono text-12px text-gray" dir="ltr">{{ $f->url }}</td><td class="px-4 py-3 text-center">@include('panel_v1.components.actions-dropdown', ['id' => 'mkt-form-'.$f->id, 'items' => [['label' => 'تعديل', 'url' => route('panel.v1.admin.marketing.forms.edit',['id'=>$f->id]), 'tone' => 'gray'], ['label' => 'حذف', 'action' => route('panel.v1.admin.marketing.forms.delete',['id'=>$f->id]), 'confirm' => 'حذف؟', 'tone' => 'danger']]])</td></tr>@empty<tr><td colspan="5" class="px-4 py-14 text-center font-medium text-gray">لا توجد نماذج — أنشئ أول نموذج</td></tr>@endforelse
            </tbody></table></div>
        </div>
    @endisset

    @isset($banners)
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex items-center justify-between"><h2 class="font-bold text-16px text-primary">البانرات الإعلانية — {{ $banners->total() }}</h2><a href="{{ route('panel.v1.admin.marketing.banners.create') }}" class="h-10 px-4 rounded-12px bg-primary text-white font-bold text-13px center">+ بانر جديد</a></div>
            <div class="overflow-x-auto"><table class="table w-full text-14px"><thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">العنوان</th><th class="px-4 py-3 text-center">الموضع</th><th class="px-4 py-3 text-center">الحجم</th><th class="px-4 py-3 text-center">الحالة</th><th class="px-4 py-3 text-center">إجراءات</th></tr></thead><tbody>
                @forelse($banners as $b)<tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $b->title ?? 'بانر #'.$b->id }}</td><td class="px-4 py-3 text-center font-medium text-gray">{{ $b->position }}</td><td class="px-4 py-3 text-center font-medium text-gray">{{ $b->size }}</td><td class="px-4 py-3 text-center"><span class="inline-flex rounded-full px-3 py-1 font-semibold text-11px {{ $b->published ? 'bg-[#D1FAE5] text-[#059669]' : 'bg-[#FEE2E2] text-[#DC2626]' }}">{{ $b->published ? 'منشور' : 'مسودة' }}</span></td><td class="px-4 py-3 text-center">@include('panel_v1.components.actions-dropdown', ['id' => 'mkt-banner-'.$b->id, 'items' => [['label' => 'تعديل', 'url' => route('panel.v1.admin.marketing.banners.edit',['id'=>$b->id]), 'tone' => 'gray'], ['label' => 'حذف', 'action' => route('panel.v1.admin.marketing.banners.delete',['id'=>$b->id]), 'confirm' => 'حذف؟', 'tone' => 'danger']]])</td></tr>@empty<tr><td colspan="5" class="px-4 py-14 text-center font-medium text-gray">لا توجد بانرات — أنشئ أول بانر إعلاني</td></tr>@endforelse
            </tbody></table></div>
        </div>
    @endisset

    @isset($floatingBars)
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex items-center justify-between"><h2 class="font-bold text-16px text-primary">الشريط العائم — {{ $floatingBars->total() }}</h2><a href="{{ route('panel.v1.admin.marketing.floating-bars.create') }}" class="h-10 px-4 rounded-12px bg-primary text-white font-bold text-13px center">+ شريط جديد</a></div>
            <div class="overflow-x-auto"><table class="table w-full text-14px"><thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">#</th><th class="px-4 py-3 text-start">العنوان</th><th class="px-4 py-3 text-center">الموضع</th><th class="px-4 py-3 text-center">مفعّل</th><th class="px-4 py-3 text-center">إجراءات</th></tr></thead><tbody>
                @forelse($floatingBars as $fb)<tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">#{{ $fb->id }}</td><td class="px-4 py-3 font-medium text-primary">{{ $fb->title ?? 'شريط #'.$fb->id }}</td><td class="px-4 py-3 text-center font-medium text-gray">{{ $fb->position }}</td><td class="px-4 py-3 text-center"><span class="inline-flex rounded-full px-3 py-1 font-semibold text-11px {{ $fb->enable ? 'bg-[#D1FAE5] text-[#059669]' : 'bg-gray-100 text-gray-500' }}">{{ $fb->enable ? 'مفعّل' : 'معطل' }}</span></td><td class="px-4 py-3 text-center">@include('panel_v1.components.actions-dropdown', ['id' => 'mkt-fbar-'.$fb->id, 'items' => [['label' => 'تعديل', 'url' => route('panel.v1.admin.marketing.floating-bars.edit',['id'=>$fb->id]), 'tone' => 'gray'], ['label' => 'حذف', 'action' => route('panel.v1.admin.marketing.floating-bars.delete',['id'=>$fb->id]), 'confirm' => 'حذف؟', 'tone' => 'danger']]])</td></tr>@empty<tr><td colspan="5" class="px-4 py-14 text-center font-medium text-gray">لا يوجد شريط عائم — أنشئ أول شريط</td></tr>@endforelse
            </tbody></table></div>
        </div>
    @endisset

    @isset($purchaseNotifications)
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex items-center justify-between"><h2 class="font-bold text-16px text-primary">إشعارات الشراء — {{ $purchaseNotifications->total() }}</h2><a href="{{ route('panel.v1.admin.marketing.purchase-notifications.create') }}" class="h-10 px-4 rounded-12px bg-primary text-white font-bold text-13px center">+ إشعار جديد</a></div>
            <div class="overflow-x-auto"><table class="table w-full text-14px"><thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">العنوان</th><th class="px-4 py-3 text-center">المدة</th><th class="px-4 py-3 text-center">التأخير</th><th class="px-4 py-3 text-center">مفعّل</th><th class="px-4 py-3 text-center">إجراءات</th></tr></thead><tbody>
                @forelse($purchaseNotifications as $pn)<tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $pn->title ?? 'إشعار #'.$pn->id }}</td><td class="px-4 py-3 text-center font-medium text-gray">{{ $pn->popup_duration }}ث</td><td class="px-4 py-3 text-center font-medium text-gray">{{ $pn->popup_delay }}ث</td><td class="px-4 py-3 text-center"><span class="inline-flex rounded-full px-3 py-1 font-semibold text-11px {{ $pn->enable ? 'bg-[#D1FAE5] text-[#059669]' : 'bg-[#FEE2E2] text-[#DC2626]' }}">{{ $pn->enable ? 'مفعّل' : 'معطل' }}</span></td><td class="px-4 py-3 text-center">@include('panel_v1.components.actions-dropdown', ['id' => 'mkt-pn-'.$pn->id, 'items' => [['label' => 'تعديل', 'url' => route('panel.v1.admin.marketing.purchase-notifications.edit',['id'=>$pn->id]), 'tone' => 'gray'], ['label' => 'حذف', 'action' => route('panel.v1.admin.marketing.purchase-notifications.delete',['id'=>$pn->id]), 'confirm' => 'حذف؟', 'tone' => 'danger']]])</td></tr>@empty<tr><td colspan="5" class="px-4 py-14 text-center font-medium text-gray">لا توجد إشعارات شراء — أنشئ أول إشعار</td></tr>@endforelse
            </tbody></table></div>
        </div>
    @endisset

    @isset($cartDiscounts)
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]"><h2 class="font-bold text-16px text-primary">خصم السلة — {{ $cartDiscounts->total() }}</h2></div>
            <div class="overflow-x-auto"><table class="table w-full text-14px"><thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">العنوان</th><th class="px-4 py-3 text-center">القسيمة</th><th class="px-4 py-3 text-center">للخالي فقط</th><th class="px-4 py-3 text-center">مفعّل</th></tr></thead><tbody>
                @forelse($cartDiscounts as $cd)<tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $cd->title ?? 'خصم #'.$cd->id }}</td><td class="px-4 py-3 text-center font-mono text-12px text-primary">{{ $cd->discount->code ?? $cd->discount_id }}</td><td class="px-4 py-3 text-center"><span class="inline-flex rounded-full px-3 py-1 font-semibold text-11px {{ $cd->show_only_on_empty_cart ? 'bg-[#D1FAE5] text-[#059669]' : 'bg-gray-100 text-gray-500' }}">{{ $cd->show_only_on_empty_cart ? 'نعم' : 'لا' }}</span></td><td class="px-4 py-3 text-center"><span class="inline-flex rounded-full px-3 py-1 font-semibold text-11px {{ $cd->enable ? 'bg-[#D1FAE5] text-[#059669]' : 'bg-[#FEE2E2] text-[#DC2626]' }}">{{ $cd->enable ? 'مفعّل' : 'معطل' }}</span></td></tr>@empty<tr><td colspan="4" class="px-4 py-14 text-center font-medium text-gray">لا يوجد خصم سلة — يُنشأ من CartDiscountController</td></tr>@endforelse
            </tbody></table></div>
        </div>
    @endisset

    @isset($abandonedCarts)
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]"><h2 class="font-bold text-16px text-primary">السلة المتروكة — {{ $abandonedCarts->total() }} مستخدم</h2></div>
            <div class="overflow-x-auto"><table class="table w-full text-14px"><thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">المستخدم</th><th class="px-4 py-3 text-center">عدد العناصر</th></tr></thead><tbody>
                @forelse($abandonedCarts as $ac)<tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">#{{ $ac->creator_id }}</td><td class="px-4 py-3 text-center font-semibold text-primary">{{ $ac->total_items }}</td></tr>@empty<tr><td colspan="2" class="px-4 py-14 text-center font-medium text-gray">لا توجد سلال متروكة</td></tr>@endforelse
            </tbody></table></div>
        </div>
    @endisset

    @isset($rules)
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex items-center justify-between"><h2 class="font-bold text-16px text-primary">قواعد السلة المتروكة — {{ $rules->total() }}</h2><a href="{{ route('panel.v1.admin.marketing.abandoned-cart.create') }}" class="h-10 px-4 rounded-12px bg-primary text-white font-bold text-13px center shrink-0">+ قاعدة جديدة</a></div>
            <div class="overflow-x-auto"><table class="table w-full text-14px"><thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">#</th><th class="px-4 py-3 text-start">القاعدة</th><th class="px-4 py-3 text-center">النوع المستهدف</th><th class="px-4 py-3 text-center">الحالة</th><th class="px-4 py-3 text-center">إجراء</th></tr></thead><tbody>
                @forelse($rules as $rl)<tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">#{{ $rl->id }}</td><td class="px-4 py-3 font-medium text-primary">{{ $rl->title ?? 'قاعدة #'.$rl->id }}</td><td class="px-4 py-3 text-center font-medium text-gray">{{ $rl->target_type ?? '—' }}</td><td class="px-4 py-3 text-center"><span class="inline-flex rounded-full px-3 py-1 font-semibold text-11px {{ $rl->enable ? 'bg-[#D1FAE5] text-[#059669]' : 'bg-[#FEE2E2] text-[#DC2626]' }}">{{ $rl->enable ? 'مفعّلة' : 'معطلة' }}</span></td><td class="px-4 py-3 text-center">@include('panel_v1.components.actions-dropdown', ['id' => 'mkt-acrule-'.$rl->id, 'items' => [['label' => 'تعديل', 'url' => route('panel.v1.admin.marketing.abandoned-cart.edit',['id'=>$rl->id]), 'tone' => 'gray'], ['label' => 'حذف', 'action' => route('panel.v1.admin.marketing.abandoned-cart.delete',['id'=>$rl->id]), 'confirm' => 'حذف؟', 'tone' => 'danger']]])</td></tr>@empty<tr><td colspan="5" class="px-4 py-14 text-center font-medium text-gray">لا توجد قواعد — تُنشأ لاستهداف السلال المتروكة بالكوبونات</td></tr>@endforelse
            </tbody></table></div>
        </div>
    @endisset

    @isset($newsletters)
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex items-center justify-between"><h2 class="font-bold text-16px text-primary">النشرات البريدية — {{ $newsletters->total() }}</h2><a href="{{ route('panel.v1.admin.marketing.newsletters.send-form') }}" class="h-10 px-4 rounded-12px bg-primary text-white font-bold text-13px center shrink-0">+ إرسال نشرة</a></div>
            <div class="overflow-x-auto"><table class="table w-full text-14px"><thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">الإيميل</th><th class="px-4 py-3 text-center">التاريخ</th></tr></thead><tbody>
                @forelse($newsletters as $nl)<tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-medium text-primary" dir="ltr">{{ $nl->email }}</td><td class="px-4 py-3 text-center font-medium text-gray">{{ date('Y/m/d', (int)$nl->created_at) }}</td></tr>@empty<tr><td colspan="2" class="px-4 py-14 text-center font-medium text-gray">لا يوجد مشتركون</td></tr>@endforelse
            </tbody></table></div>
        </div>
    @endisset

    @isset($productBadges)
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex items-center justify-between"><h2 class="font-bold text-16px text-primary">شارات المنتجات — {{ $productBadges->total() }}</h2><a href="{{ route('panel.v1.admin.marketing.product-badges.create') }}" class="h-10 px-4 rounded-12px bg-primary text-white font-bold text-13px center">+ شارة جديدة</a></div>
            <div class="overflow-x-auto"><table class="table w-full text-14px"><thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">الشارة</th><th class="px-4 py-3 text-center">المحتوى</th><th class="px-4 py-3 text-center">الحالة</th><th class="px-4 py-3 text-center">إجراءات</th></tr></thead><tbody>
                @forelse($productBadges as $pb)<tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $pb->title ?? 'شارة #'.$pb->id }}</td><td class="px-4 py-3 text-center font-semibold text-primary">{{ $pb->contents_count ?? '—' }}</td><td class="px-4 py-3 text-center"><span class="inline-flex rounded-full px-3 py-1 font-semibold text-11px {{ $pb->enable ? 'bg-[#D1FAE5] text-[#059669]' : 'bg-[#FEE2E2] text-[#DC2626]' }}">{{ $pb->enable ? 'نشط' : 'معطل' }}</span></td><td class="px-4 py-3 text-center">@include('panel_v1.components.actions-dropdown', ['id' => 'mkt-badge-'.$pb->id, 'items' => [['label' => 'تعديل', 'url' => route('panel.v1.admin.marketing.product-badges.edit',['id'=>$pb->id]), 'tone' => 'gray'], ['label' => 'حذف', 'action' => route('panel.v1.admin.marketing.product-badges.delete',['id'=>$pb->id]), 'confirm' => 'حذف؟', 'tone' => 'danger']]])</td></tr>@empty<tr><td colspan="4" class="px-4 py-14 text-center font-medium text-gray">لا توجد شارات — أنشئ أول شارة</td></tr>@endforelse
            </tbody></table></div>
        </div>
    @endisset

    @isset($advertisingModal)
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]"><h2 class="font-bold text-16px text-primary">النافذة الإعلانية</h2></div>
            <div class="p-6">
                @if($advertisingModal)
                    <div class="rounded-12px bg-[#FAF8F4] border border-d9 p-4">
                        <p class="font-mono text-12px text-gray break-all">{{ \Illuminate\Support\Str::limit($advertisingModal->value ?? '—', 200) }}</p>
                    </div>
                    <p class="font-medium text-12px text-gray mt-3">Setting `advertising_modal` — تُعدل من AdvertisingModalController</p>
                @else
                    <p class="font-medium text-14px text-gray text-center py-8">لا توجد نافذة إعلانية مُعدة — تُنشأ من الإعدادات</p>
                @endif
            </div>
        </div>
    @endisset

    @isset($abandonedUsersCarts)
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]"><h2 class="font-bold text-16px text-primary">سلال المستخدمين المتروكة — {{ $abandonedUsersCarts->total() }}</h2></div>
            <div class="overflow-x-auto"><table class="table w-full text-14px"><thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">المستخدم</th><th class="px-4 py-3 text-start">العنصر</th><th class="px-4 py-3 text-center">التاريخ</th><th class="px-4 py-3 text-center">إجراء</th></tr></thead><tbody>
                @forelse($abandonedUsersCarts as $c)<tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $c->creator->full_name ?? 'مستخدم #'.$c->creator_id }}</td><td class="px-4 py-3 font-medium text-primary truncate max-w-[16rem]">{{ $c->webinar->title ?? $c->bundle->title ?? $c->product->title ?? '—' }}</td><td class="px-4 py-3 text-center font-medium text-gray">{{ date('Y/m/d',(int)$c->created_at) }}</td><td class="px-4 py-3 text-center">@include('panel_v1.components.actions-dropdown', ['id' => 'mkt-abandoned-'.$c->id, 'items' => [['label' => 'حذف', 'action' => route('panel.v1.admin.marketing.abandoned.delete',['id'=>$c->id]), 'confirm' => 'حذف؟', 'tone' => 'danger']]])</td></tr>@empty<tr><td colspan="4" class="px-4 py-14 text-center font-medium text-gray">لا توجد سلال متروكة</td></tr>@endforelse
            </tbody></table></div>
        </div>
    @endisset

    @isset($cashbackTransactions)
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex flex-wrap items-center justify-between gap-3"><h2 class="font-bold text-16px text-primary">معاملات الكاش باك — {{ $cashbackTransactions->total() }}</h2><a href="{{ route('panel.v1.admin.marketing.cashback.transactions.export', request()->query()) }}" class="inline-flex items-center gap-2 h-10 px-4 rounded-12px border border-d9 bg-white font-semibold text-13px text-primary hover:bg-[#FAFAF4] transition"><span class="icon-[tabler--file-spreadsheet] size-4"></span> تصدير Excel</a></div>
            <div class="overflow-x-auto"><table class="table w-full text-14px"><thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">المستخدم</th><th class="px-4 py-3 text-center">المبلغ</th><th class="px-4 py-3 text-center">الوصف</th><th class="px-4 py-3 text-center">التاريخ</th><th class="px-4 py-3 text-center">إجراء</th></tr></thead><tbody>
                @forelse($cashbackTransactions as $t)<tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $t->user->full_name ?? 'مستخدم #'.$t->user_id }}</td><td class="px-4 py-3 text-center font-semibold text-primary">{{ handlePrice($t->amount ?? 0) }}</td><td class="px-4 py-3 text-center font-medium text-gray truncate max-w-[14rem]">{{ $t->description ?? '—' }}</td><td class="px-4 py-3 text-center font-medium text-gray">{{ date('Y/m/d',(int)$t->created_at) }}</td><td class="px-4 py-3 text-center">@include('panel_v1.components.actions-dropdown', ['id' => 'mkt-cb-txn-'.$t->id, 'items' => [['label' => 'استرجاع', 'action' => route('panel.v1.admin.marketing.cashback.refund',['id'=>$t->id]), 'confirm' => 'استرجاع؟', 'tone' => 'danger']]])</td></tr>@empty<tr><td colspan="5" class="px-4 py-14 text-center font-medium text-gray">لا توجد معاملات</td></tr>@endforelse
            </tbody></table></div>
        </div>
    @endisset

    @isset($cashbackHistory)
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex flex-wrap items-center justify-between gap-3"><h2 class="font-bold text-16px text-primary">سجل الكاش باك — {{ $cashbackHistory->total() }}</h2><a href="{{ route('panel.v1.admin.marketing.cashback.history.export', request()->query()) }}" class="inline-flex items-center gap-2 h-10 px-4 rounded-12px border border-d9 bg-white font-semibold text-13px text-primary hover:bg-[#FAFAF4] transition"><span class="icon-[tabler--file-spreadsheet] size-4"></span> تصدير Excel</a></div>
            <div class="overflow-x-auto"><table class="table w-full text-14px"><thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">المستخدم</th><th class="px-4 py-3 text-center">إجمالي الكاش باك</th><th class="px-4 py-3 text-center">آخر كاش باك</th></tr></thead><tbody>
                @forelse($cashbackHistory as $h)<tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">مستخدم #{{ $h->user_id }}</td><td class="px-4 py-3 text-center font-semibold text-primary">{{ handlePrice($h->total_cashback ?? 0) }}</td><td class="px-4 py-3 text-center font-medium text-gray">{{ !empty($h->last_cashback) ? date('Y/m/d',(int)$h->last_cashback) : '—' }}</td></tr>@empty<tr><td colspan="3" class="px-4 py-14 text-center font-medium text-gray">لا يوجد سجل</td></tr>@endforelse
            </tbody></table></div>
        </div>
    @endisset

    @isset($formFields)
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex items-center justify-between"><h2 class="font-bold text-16px text-primary">حقول النموذج: {{ $formM->title ?? '' }} — {{ $formFields->total() }}</h2><a href="{{ route('panel.v1.admin.marketing.form-fields.create',['formId'=>$formM->id ?? 0]) }}" class="h-10 px-4 rounded-12px bg-primary text-white font-bold text-13px center">+ حقل جديد</a></div>
            <div class="overflow-x-auto"><table class="table w-full text-14px"><thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">العنوان</th><th class="px-4 py-3 text-center">النوع</th><th class="px-4 py-3 text-center">إجباري</th><th class="px-4 py-3 text-center">إجراءات</th></tr></thead><tbody>
                @forelse($formFields as $f)<tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $f->title ?? 'حقل #'.$f->id }}</td><td class="px-4 py-3 text-center font-medium text-gray">{{ $f->type }}</td><td class="px-4 py-3 text-center"><span class="inline-flex rounded-full px-3 py-1 font-semibold text-11px {{ $f->required ? 'bg-[#D1FAE5] text-[#059669]' : 'bg-gray-100 text-gray-500' }}">{{ $f->required ? 'نعم' : 'لا' }}</span></td><td class="px-4 py-3 text-center">@include('panel_v1.components.actions-dropdown', ['id' => 'mkt-ffield-'.$f->id, 'items' => [['label' => 'تعديل', 'url' => route('panel.v1.admin.marketing.form-fields.edit',['formId'=>$formM->id ?? 0,'fieldId'=>$f->id]), 'tone' => 'gray'], ['label' => 'حذف', 'action' => route('panel.v1.admin.marketing.form-fields.delete',['formId'=>$formM->id ?? 0,'fieldId'=>$f->id]), 'confirm' => 'حذف؟', 'tone' => 'danger']]])</td></tr>@empty<tr><td colspan="4" class="px-4 py-14 text-center font-medium text-gray">لا توجد حقول — أنشئ أول حقل</td></tr>@endforelse
            </tbody></table></div>
        </div>
    @endisset

    @isset($submissions)
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]"><h2 class="font-bold text-16px text-primary">إرساليات: {{ $formM->title ?? '' }} — {{ $submissions->total() }}</h2></div>
            <div class="overflow-x-auto"><table class="table w-full text-14px"><thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">المستخدم</th><th class="px-4 py-3 text-center">التاريخ</th><th class="px-4 py-3 text-center">إجراءات</th></tr></thead><tbody>
                @forelse($submissions as $s)<tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $s->user->full_name ?? 'مستخدم #'.$s->user_id }}</td><td class="px-4 py-3 text-center font-medium text-gray">{{ date('Y/m/d',(int)$s->created_at) }}</td><td class="px-4 py-3 text-center">@include('panel_v1.components.actions-dropdown', ['id' => 'mkt-fsub-'.$s->id, 'items' => [['label' => 'عرض', 'url' => route('panel.v1.admin.marketing.form-submissions.show',['formId'=>$formM->id ?? 0,'submissionId'=>$s->id]), 'tone' => 'gray'], ['label' => 'حذف', 'action' => route('panel.v1.admin.marketing.form-submissions.delete',['formId'=>$formM->id ?? 0,'submissionId'=>$s->id]), 'confirm' => 'حذف؟', 'tone' => 'danger']]])</td></tr>@empty<tr><td colspan="3" class="px-4 py-14 text-center font-medium text-gray">لا توجد إرساليات</td></tr>@endforelse
            </tbody></table></div>
        </div>
    @endisset

    @isset($submission)
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]"><h2 class="font-bold text-16px text-primary">تفاصيل الإرسالية #{{ $submission->id }} — {{ $formM->title ?? '' }}</h2></div>
            <form method="POST" action="{{ route('panel.v1.admin.marketing.form-submissions.update',['formId'=>$formM->id ?? 0,'submissionId'=>$submission->id]) }}" class="p-4 sm:p-6 space-y-3">
                @csrf
                @forelse($submission->items ?? [] as $it)
                    <div class="rounded-12px border border-d9 p-4 bg-[#FAFAF4]">
                        <label class="font-bold text-13px text-primary mb-2 block">{{ $it->field->title ?? 'حقل #'.$it->form_field_id }}</label>
                        <input type="text" name="items[{{ $it->id }}]" value="{{ old('items.'.$it->id, $it->value) }}" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px bg-white">
                    </div>
                @empty
                    <p class="font-medium text-14px text-gray text-center py-8">لا توجد عناصر لهذه الإرسالية.</p>
                @endforelse
                @if(($submission->items ?? collect())->count())
                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px hover:opacity-95 transition">حفظ التعديلات</button>
                        <a href="{{ route('panel.v1.admin.marketing.form-submissions',['formId'=>$formM->id ?? 0]) }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition">عودة</a>
                    </div>
                @endif
            </form>
        </div>
    @endisset

    @isset($blogCategories)
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex items-center justify-between"><h2 class="font-bold text-16px text-primary">تصنيفات المدونة — {{ $blogCategories->total() }}</h2><a href="{{ route('panel.v1.admin.marketing.blog-categories.create') }}" class="h-10 px-4 rounded-12px bg-primary text-white font-bold text-13px center">+ تصنيف جديد</a></div>
            <div class="overflow-x-auto"><table class="table w-full text-14px"><thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">العنوان</th><th class="px-4 py-3 text-center">الرابط</th><th class="px-4 py-3 text-center">إجراءات</th></tr></thead><tbody>
                @forelse($blogCategories as $c)<tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $c->title ?? 'تصنيف #'.$c->id }}</td><td class="px-4 py-3 text-center font-mono text-12px text-gray" dir="ltr">{{ $c->slug ?? '—' }}</td><td class="px-4 py-3 text-center">@include('panel_v1.components.actions-dropdown', ['id' => 'mkt-bcat-'.$c->id, 'items' => [['label' => 'تعديل', 'url' => route('panel.v1.admin.marketing.blog-categories.edit',['id'=>$c->id]), 'tone' => 'gray'], ['label' => 'حذف', 'action' => route('panel.v1.admin.marketing.blog-categories.delete',['id'=>$c->id]), 'confirm' => 'حذف؟', 'tone' => 'danger']]])</td></tr>@empty<tr><td colspan="3" class="px-4 py-14 text-center font-medium text-gray">لا توجد تصنيفات</td></tr>@endforelse
            </tbody></table></div>
        </div>
    @endisset

    @isset($newsletterHistories)
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]"><h2 class="font-bold text-16px text-primary">سجل النشرات — {{ $newsletterHistories->total() }}</h2></div>
            <div class="overflow-x-auto"><table class="table w-full text-14px"><thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">العنوان</th><th class="px-4 py-3 text-center">طريقة الإرسال</th><th class="px-4 py-3 text-center">عدد الإيميلات</th><th class="px-4 py-3 text-center">التاريخ</th></tr></thead><tbody>
                @forelse($newsletterHistories as $h)<tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary truncate max-w-[16rem]">{{ $h->title }}</td><td class="px-4 py-3 text-center font-medium text-gray">{{ $h->send_method ?? '—' }}</td><td class="px-4 py-3 text-center font-semibold text-primary">{{ $h->email_count ?? 0 }}</td><td class="px-4 py-3 text-center font-medium text-gray">{{ date('Y/m/d',(int)$h->created_at) }}</td></tr>@empty<tr><td colspan="4" class="px-4 py-14 text-center font-medium text-gray">لا يوجد سجل</td></tr>@endforelse
            </tbody></table></div>
        </div>
    @endisset

    @isset($testimonials)
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex items-center justify-between"><h2 class="font-bold text-16px text-primary">الشهادات والآراء — {{ $testimonials->total() }}</h2><a href="{{ route('panel.v1.admin.marketing.testimonials.create') }}" class="h-10 px-4 rounded-12px bg-primary text-white font-bold text-13px center">+ رأي جديد</a></div>
            <div class="overflow-x-auto"><table class="table w-full text-14px"><thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">المستخدم</th><th class="px-4 py-3 text-center">التقييم</th><th class="px-4 py-3 text-start">التعليق</th><th class="px-4 py-3 text-center">الحالة</th><th class="px-4 py-3 text-center">إجراءات</th></tr></thead><tbody>
                @forelse($testimonials as $t)<tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $t->user_name ?? '—' }}</td><td class="px-4 py-3 text-center font-bold text-primary">{{ $t->rate ?? '—' }}/5</td><td class="px-4 py-3 font-medium text-gray truncate max-w-[16rem]">{{ \Illuminate\Support\Str::limit(strip_tags($t->comment ?? ''),60) }}</td><td class="px-4 py-3 text-center"><span class="inline-flex rounded-full px-3 py-1 font-semibold text-11px {{ ($t->status ?? '')=='active' ? 'bg-[#D1FAE5] text-[#059669]' : 'bg-[#FEE2E2] text-[#DC2626]' }}">{{ $t->status ?? '—' }}</span></td><td class="px-4 py-3 text-center">@include('panel_v1.components.actions-dropdown', ['id' => 'mkt-testimonial-'.$t->id, 'items' => [['label' => 'تعديل', 'url' => route('panel.v1.admin.marketing.testimonials.edit',['id'=>$t->id]), 'tone' => 'gray'], ['label' => 'حذف', 'action' => route('panel.v1.admin.marketing.testimonials.delete',['id'=>$t->id]), 'confirm' => 'حذف؟', 'tone' => 'danger']]])</td></tr>@empty<tr><td colspan="5" class="px-4 py-14 text-center font-medium text-gray">لا توجد آراء — أنشئ أول رأي</td></tr>@endforelse
            </tbody></table></div>
        </div>
    @endisset

    @isset($promotionSales)
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]"><h2 class="font-bold text-16px text-primary">مبيعات الترقيات — {{ $promotionSales->total() }}</h2></div>
            <div class="overflow-x-auto"><table class="table w-full text-14px"><thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">الترقية</th><th class="px-4 py-3 text-start">المشتري</th><th class="px-4 py-3 text-start">الدورة</th><th class="px-4 py-3 text-center">التاريخ</th></tr></thead><tbody>
                @forelse($promotionSales as $s)<tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $s->promotion->title ?? 'ترقية #'.$s->promotion_id }}</td><td class="px-4 py-3 font-medium text-primary">{{ $s->buyer->full_name ?? '—' }}</td><td class="px-4 py-3 font-medium text-primary truncate max-w-[14rem]">{{ $s->webinar->title ?? '—' }}</td><td class="px-4 py-3 text-center font-medium text-gray">{{ date('Y/m/d',(int)$s->created_at) }}</td></tr>@empty<tr><td colspan="4" class="px-4 py-14 text-center font-medium text-gray">لا توجد مبيعات ترقيات</td></tr>@endforelse
            </tbody></table></div>
        </div>
    @endisset

    @isset($users)
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]"><h2 class="font-bold text-16px text-primary">سجل مكافأة التسجيل — {{ $users->total() }}</h2></div>
            <div class="overflow-x-auto"><table class="table w-full text-14px"><thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">المستخدم</th><th class="px-4 py-3 text-center">الدور</th><th class="px-4 py-3 text-center">المكافأة</th><th class="px-4 py-3 text-center">المحالون</th><th class="px-4 py-3 text-center">التسجيل</th><th class="px-4 py-3 text-center">الحالة</th></tr></thead><tbody>
                @forelse($users as $b)<tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $b->full_name }}</td><td class="px-4 py-3 text-center font-medium text-gray">{{ $b->role->caption ?? $b->role_name }}</td><td class="px-4 py-3 text-center font-semibold text-primary">{{ handlePrice($b->registration_bonus_amount ?? 0) }}</td><td class="px-4 py-3 text-center font-medium text-primary">{{ $b->affiliates_count ?? 0 }}</td><td class="px-4 py-3 text-center font-medium text-gray">{{ date('Y/m/d',(int)$b->created_at) }}</td><td class="px-4 py-3 text-center"><span class="inline-flex rounded-full px-3 py-1 font-semibold text-11px {{ ($b->bonus_status ?? '')==trans('update.unlock') ? 'bg-[#D1FAE5] text-[#059669]' : 'bg-[#FEF3C7] text-[#D97706]' }}">{{ $b->bonus_status ?? '—' }}</span></td></tr>@empty<tr><td colspan="6" class="px-4 py-14 text-center font-medium text-gray">لا يوجد مستحقون</td></tr>@endforelse
            </tbody></table></div>
        </div>
    @endisset

    @if (!isset($discounts) && !isset($affiliates) && !isset($cashbacks) && !isset($points) && !isset($gifts) && !isset($bonuses) && !isset($products) && !isset($blogs) && !isset($pages) && !isset($specialOffers) && !isset($tools) && !isset($forms) && !isset($banners) && !isset($floatingBars) && !isset($purchaseNotifications) && !isset($cartDiscounts) && !isset($abandonedCarts) && !isset($newsletters) && !isset($productBadges) && !isset($advertisingModal) && !isset($rules) && !isset($abandonedUsersCarts) && !isset($cashbackTransactions) && !isset($cashbackHistory) && !isset($formFields) && !isset($submissions) && !isset($submission) && !isset($blogCategories) && !isset($newsletterHistories) && !isset($testimonials) && !isset($promotionSales) && !isset($users) && empty($stats))
        @include('panel_v1.admin.components.empty-stub', ['title' => $stubTitle ?? 'لا توجد بيانات', 'subtitle' => $stubSubtitle ?? 'لم يتم العثور على سجلات لهذا القسم.'])
    @endif

    @if (!empty($paginator))
        @include('panel_v1.admin.components.pagination', ['paginator' => $paginator])
    @endif
</div>
@endsection
