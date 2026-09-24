@extends('panel_v1.admin.layouts.app')

@section('content')
<div class="space-y-6 sm:space-y-8 pb-8">
    @include('panel_v1.admin.components.page-header', [
        'title' => $pageTitleText ?? 'الإعدادات',
        'subtitle' => 'إدارة إعدادات المنصة من البطاقات أدناه',
    ])

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-5">
        @foreach ($settingsCards ?? [] as $card)
            <article class="rounded-14px border border-d9 bg-white shadow-sm overflow-hidden flex flex-row items-stretch min-h-[9.5rem]">
                <div class="w-[5.5rem] sm:w-28 shrink-0 bg-primary/10 center">
                    <span class="{{ $card['icon'] }} size-10 sm:size-12 text-primary"></span>
                </div>
                <div class="flex-1 min-w-0 p-4 sm:p-5 flex flex-col text-start">
                    <h2 class="font-bold text-18px sm:text-20px text-primary mb-1.5 leading-snug">
                        {{ $card['title'] }}
                    </h2>
                    <p class="font-medium text-13px sm:text-14px text-gray leading-relaxed flex-1 mb-3">
                        {{ $card['hint'] }}
                    </p>
                    <a href="{{ $card['url'] }}"
                        class="inline-flex items-center gap-1.5 font-semibold text-14px text-primary hover:opacity-80 transition self-start">
                        {{ trans('admin/main.change_setting') }}
                        <span class="icon-[tabler--chevron-left] size-4 rtl:rotate-180"></span>
                    </a>
                </div>
            </article>
        @endforeach
    </div>
</div>
@endsection
