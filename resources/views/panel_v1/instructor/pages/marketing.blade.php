@extends('panel_v1.instructor.layouts.app')

@section('content')
@php
    $activeTab = request('tab', 'coupons');
    if (!in_array($activeTab, ['coupons', 'offers', 'promos'], true)) {
        $activeTab = 'coupons';
    }
@endphp

<div class="space-y-8 sm:space-y-10 pb-10">
    <div class="min-w-0">
        <h1 class="font-semibold text-28px sm:text-32px text-primary mb-2">إدارة التسويق والعروض</h1>
        <p class="font-medium text-16px sm:text-18px text-gray leading-relaxed max-w-3xl">
            إنشاء قسائم الخصم، متابعة العروض الترويجية، وزيادة مبيعات دوراتك.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-5">
        <button type="button"
            class="rounded-14px bg-[#EAF6F2] border border-transparent px-5 py-7 sm:py-8 center flex-col text-center hover:border-primary/20 hover:shadow-sm transition"
            aria-haspopup="dialog" aria-controls="instructor-discount-modal"
            data-overlay="#instructor-discount-modal">
            <span class="size-12 rounded-12px bg-primary center mb-4 shrink-0">
                <span class="icon-[tabler--ticket] size-6 text-white"></span>
            </span>
            <h2 class="font-bold text-18px sm:text-20px text-primary leading-snug mb-2">إنشاء قسيمة خصم جديدة</h2>
            <p class="font-medium text-14px sm:text-15px text-gray leading-relaxed">إنشاء كوبون خصم فوري لطلابك</p>
        </button>

        <button type="button"
            class="rounded-14px bg-[#EAF6F2] border border-transparent px-5 py-7 sm:py-8 center flex-col text-center hover:border-primary/20 hover:shadow-sm transition"
            aria-haspopup="dialog" aria-controls="instructor-offer-modal"
            data-overlay="#instructor-offer-modal">
            <span class="size-12 rounded-12px bg-primary center mb-4 shrink-0">
                <span class="icon-[tabler--receipt] size-6 text-white"></span>
            </span>
            <h2 class="font-bold text-18px sm:text-20px text-primary leading-snug mb-2">إنشاء تخفيض لدورتك</h2>
            <p class="font-medium text-14px sm:text-15px text-gray leading-relaxed">تخفيض سعري مباشر على دورة محددة</p>
        </button>

        <button type="button"
            class="rounded-14px bg-[#EAF6F2] border border-transparent px-5 py-7 sm:py-8 center flex-col text-center hover:border-primary/20 hover:shadow-sm transition"
            aria-haspopup="dialog" aria-controls="instructor-promo-modal"
            data-overlay="#instructor-promo-modal">
            <span class="size-12 rounded-12px bg-primary center mb-4 shrink-0">
                <span class="icon-[tabler--ticket] size-6 text-white"></span>
            </span>
            <h2 class="font-bold text-18px sm:text-20px text-primary leading-snug mb-2">إنشاء خطط ترويجية</h2>
            <p class="font-medium text-14px sm:text-15px text-gray leading-relaxed">فتح طلب جديد وتوجيهه للفريق المختص</p>
        </button>
    </div>

    <div>
        <nav class="tabs tabs-bordered flex w-full overflow-x-auto mb-6 sm:mb-8 border-b border-d9" role="tablist">
            <button type="button"
                class="tab {{ $activeTab === 'coupons' ? 'active' : '' }} justify-center whitespace-nowrap font-semibold text-16px sm:text-18px text-gray pb-5 active-tab:text-primary active-tab:border-b-primary"
                data-v1-tab="#mkt-panel-1" role="tab" aria-selected="{{ $activeTab === 'coupons' ? 'true' : 'false' }}">
                قائمة قسائم الخصم المفعلة
                <span class="ms-2 font-medium text-13px text-gray">({{ (int) ($couponCount ?? 0) }})</span>
            </button>
            <button type="button"
                class="tab {{ $activeTab === 'offers' ? 'active' : '' }} justify-center whitespace-nowrap font-semibold text-16px sm:text-18px text-gray pb-5 active-tab:text-primary active-tab:border-b-primary"
                data-v1-tab="#mkt-panel-2" role="tab" aria-selected="{{ $activeTab === 'offers' ? 'true' : 'false' }}">
                قائمة التخفيضات
                <span class="ms-2 font-medium text-13px text-gray">({{ (int) ($offerCount ?? 0) }})</span>
            </button>
            <button type="button"
                class="tab {{ $activeTab === 'promos' ? 'active' : '' }} justify-center whitespace-nowrap font-semibold text-16px sm:text-18px text-gray pb-5 active-tab:text-primary active-tab:border-b-primary"
                data-v1-tab="#mkt-panel-3" role="tab" aria-selected="{{ $activeTab === 'promos' ? 'true' : 'false' }}">
                الخطط الترويجية الحالية
                <span class="ms-2 font-medium text-13px text-gray">({{ (int) ($promoCount ?? 0) }})</span>
            </button>
        </nav>

        <div id="mkt-panel-1" role="tabpanel" class="{{ $activeTab === 'coupons' ? '' : 'hidden' }} bg-white border border-d9 rounded-14px overflow-x-auto shadow-sm">
            @include('panel_v1.instructor.pages.partials.marketing-table', [
                'rows' => $couponRows ?? [],
                'emptyText' => 'لا توجد قسائم خصم بعد — أنشئ قسيمة من البطاقة أعلاه',
            ])
        </div>

        <div id="mkt-panel-2" class="{{ $activeTab === 'offers' ? '' : 'hidden' }} bg-white border border-d9 rounded-14px overflow-x-auto shadow-sm" role="tabpanel">
            @include('panel_v1.instructor.pages.partials.marketing-table', [
                'rows' => $discountRows ?? [],
                'emptyText' => 'لا توجد تخفيضات دورات بعد — أنشئ تخفيضاً من البطاقة أعلاه',
            ])
        </div>

        <div id="mkt-panel-3" class="{{ $activeTab === 'promos' ? '' : 'hidden' }} bg-white border border-d9 rounded-14px overflow-x-auto shadow-sm" role="tabpanel">
            @include('panel_v1.instructor.pages.partials.marketing-table', [
                'rows' => $promoRows ?? [],
                'emptyText' => 'لا توجد خطط ترويجية حالياً',
            ])
        </div>
    </div>
</div>

@include('panel_v1.instructor.components.marketing-modals')
@endsection
