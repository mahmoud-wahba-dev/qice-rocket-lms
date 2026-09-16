@extends('panel_v1.admin.layouts.app')

@section('content')
<div class="space-y-6 sm:space-y-8 pb-8">
    @include('panel_v1.admin.components.page-header', [
        'title' => $pageTitleText ?? 'إدارة المحتوى والمظهر',
        'subtitle' => $pageSubtitle ?? '',
    ])

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4 sm:gap-5">
        @foreach ($contentCards ?? [] as $card)
            @php
                $section = $card['section'] ?? $card['route'] ?? 'products';
                $href = isset($card['route']) && str_contains($card['route'], '.') ? route($card['route'], $card['params'] ?? []) : route('panel.v1.admin.marketing.section', ['section' => $section]);
            @endphp
            <article class="rounded-14px border border-primary/10 bg-[#E8F5F1] px-4 py-6 flex flex-col items-center text-center min-h-44 hover:shadow-md hover:border-primary/20 transition">
                <span class="size-12 rounded-12px bg-white/70 center mb-4">
                    <span class="{{ $card['icon'] }} size-6 text-primary"></span>
                </span>
                <h3 class="font-bold text-16px text-primary mb-auto leading-snug">{{ $card['title'] }}</h3>
                <a href="{{ $href }}"
                    class="inline-flex items-center gap-1.5 mt-5 font-semibold text-14px text-primary hover:opacity-80 transition">
                    عرض التفاصيل
                    <span class="icon-[tabler--arrow-narrow-left] size-4"></span>
                </a>
            </article>
        @endforeach
    </div>

    @include('panel_v1.admin.components.stats-cards', ['stats' => $stats ?? []])

    @if (!empty($discounts))
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex items-center justify-between">
                <h2 class="font-bold text-16px text-primary">أحدث القسائم</h2>
                <a href="{{ route('panel.v1.admin.marketing.section',['section'=>'discounts']) }}" class="font-semibold text-13px text-primary hover:underline">عرض الكل</a>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">الكود</th><th class="px-4 py-3 text-start">العنوان</th><th class="px-4 py-3 text-center">النسبة</th><th class="px-4 py-3 text-center">العدد</th><th class="px-4 py-3 text-center">الحالة</th></tr></thead>
                    <tbody>
                    @foreach ($discounts as $d)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">{{ $d['code'] ?? $d->code ?? '—' }}</td>
                            <td class="px-4 py-3 font-medium text-primary">{{ $d['title'] ?? $d->title ?? '—' }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-primary">{{ $d['percent'] ?? $d->percent ?? '—' }}%</td>
                            <td class="px-4 py-3 text-center font-medium text-gray">{{ $d['count'] ?? '—' }}</td>
                            <td class="px-4 py-3 text-center"><span class="inline-flex rounded-full bg-[#D1FAE5] text-[#059669] px-3 py-1 font-semibold text-11px">{{ $d['status'] ?? 'نشط' }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
