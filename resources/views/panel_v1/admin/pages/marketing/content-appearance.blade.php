@extends('panel_v1.admin.layouts.app')

@section('content')
<div class="space-y-6 sm:space-y-8 pb-8">
    @include('panel_v1.admin.components.page-header', [
        'title' => $pageTitleText ?? 'إدارة المحتوى والمظهر',
        'subtitle' => $pageSubtitle ?? '',
    ])

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4 sm:gap-5">
        @foreach ($contentCards ?? [] as $card)
            <article class="rounded-14px border border-primary/10 bg-[#E8F5F1] px-4 py-6 flex flex-col items-center text-center min-h-44">
                <span class="size-12 rounded-12px bg-white/70 center mb-4">
                    <span class="{{ $card['icon'] }} size-6 text-primary"></span>
                </span>
                <h3 class="font-bold text-16px text-primary mb-auto leading-snug">{{ $card['title'] }}</h3>
                <a href="{{ route('panel.v1.admin.marketing.section', ['section' => $card['route'] ?? 'content']) }}"
                    class="inline-flex items-center gap-1.5 mt-5 font-semibold text-14px text-primary hover:opacity-80 transition">
                    عرض التفاصيل
                    <span class="icon-[tabler--arrow-narrow-left] size-4"></span>
                </a>
            </article>
        @endforeach
    </div>
</div>
@endsection
