@extends('panel_v1.admin.layouts.app')

@section('content')
<div class="space-y-6 pb-8">
    {{-- طبق الأصل للصورة — نفس العنوان والوصف والشبكة 4 أعمدة بألوانك الحالية --}}
    @include('panel_v1.admin.components.page-header', [
        'title' => $pageTitleText ?? $pageTitle ?? 'إدارة أدوات التسويق',
        'subtitle' => $pageSubtitle ?? 'مركز التحكم بإعدادات الحملات الترويجية وتفعيل أدوات الخصم والتسويق لزيادة المبيعات.',
    ])

    {{-- شبكة 9 كروت — 4 في الصف (lg) كما في الصورة — نفس الألوان الحالية --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        @foreach ($contentCards ?? [] as $card)
            @php
                $section = $card['section'] ?? 'discounts';
                $href = isset($card['route']) && str_contains($card['route'], '.')
                    ? route($card['route'], $card['params'] ?? [])
                    : route('panel.v1.admin.marketing.section', ['section' => $section]);
            @endphp
            <a href="{{ $href }}"
               class="group flex flex-col items-center justify-center text-center rounded-[12px] border border-[#0F3D36]/10 bg-[#EEF6F1] px-6 py-8 min-h-[172px] shadow-[0_1px_2px_rgba(15,61,54,0.04)] hover:border-[#0F3D36]/15 hover:shadow-[0_8px_24px_rgba(15,61,54,0.08)] hover:bg-[#E6F2EC] transition-all duration-200">
                {{-- أيقونة علوية — تبقى كاملة بدون قص — وزن خفيف كما في الصورة --}}
                <span class="{{ $card['icon'] }} size-9 text-[#0F3D36] mb-3.5 shrink-0 leading-none block transition-transform duration-200 group-hover:scale-[1.03]" aria-hidden="true"></span>

                {{-- عنوان الكرت — سطرين كحد أقصى كما في الصورة --}}
                <h3 class="font-bold text-[16px] leading-[1.5] tracking-[-0.01em] text-[#0F3D36] line-clamp-2 min-h-[48px] flex items-center justify-center px-1">
                    {{ $card['title'] }}
                </h3>

                {{-- رابط التفاصيل — أسفل الكرت --}}
                <span class="inline-flex items-center gap-1.5 mt-4 font-semibold text-[13px] text-[#0F3D36]/80 group-hover:text-[#0F3D36] group-hover:gap-2 transition-all">
                    عرض التفاصيل
                    <span class="icon-[tabler--arrow-narrow-left] size-[16px] shrink-0" aria-hidden="true"></span>
                </span>
            </a>
        @endforeach
    </div>
</div>
@endsection
