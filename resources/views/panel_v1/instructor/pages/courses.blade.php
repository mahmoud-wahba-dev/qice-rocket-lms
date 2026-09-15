@extends('panel_v1.instructor.layouts.app')

@section('content')
@php
    $demoSlug = $demoSlug ?? 'demo';
    $courseCards = $courseCards ?? [];
    $liveCards = $liveCards ?? [];
    $recordedCards = $recordedCards ?? [];
    $draftCards = $draftCards ?? [];
@endphp

<div class="space-y-6 pb-8">
    @component('panel_v1.instructor.components.page-header', [
        'title' => 'إدارة الدورات',
        'subtitle' => 'إليك ملخص أداء دوراتك، نشاط الطلاب، والأرباح لهذا اليوم.',
    ])
        @slot('actions')
            <a href="{{ route('panel.v1.instructor.courses.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-12px bg-color2 px-5 h-12 font-semibold text-16px text-white shrink-0 hover:opacity-95 transition">
                <span class="icon-[tabler--plus] size-5"></span>
                إنشاء دورة جديدة
            </a>
        @endslot
    @endcomponent

    <nav class="tabs tabs-bordered flex w-full overflow-x-auto border-b border-d9"
        aria-label="فلتر الدورات" role="tablist">
        <button type="button"
            class="tab active justify-center whitespace-nowrap font-semibold text-16px sm:text-18px text-gray pb-4 active-tab:text-primary active-tab:border-b-primary"
            id="c-tabs-1" data-v1-tab="#c-panel-1" role="tab" aria-selected="true">الكل ({{ count($courseCards) }})</button>
        <button type="button"
            class="tab justify-center whitespace-nowrap font-semibold text-16px sm:text-18px text-gray pb-4 active-tab:text-primary active-tab:border-b-primary"
            id="c-tabs-2" data-v1-tab="#c-panel-2" role="tab" aria-selected="false">المحاضرات المباشرة ({{ count($liveCards) }})</button>
        <button type="button"
            class="tab justify-center whitespace-nowrap font-semibold text-16px sm:text-18px text-gray pb-4 active-tab:text-primary active-tab:border-b-primary"
            id="c-tabs-3" data-v1-tab="#c-panel-3" role="tab" aria-selected="false">الدورات المسجلة ({{ count($recordedCards) }})</button>
        <button type="button"
            class="tab justify-center whitespace-nowrap font-semibold text-16px sm:text-18px text-gray pb-4 active-tab:text-primary active-tab:border-b-primary"
            id="c-tabs-4" data-v1-tab="#c-panel-4" role="tab" aria-selected="false">المسودات ({{ count($draftCards) }})</button>
    </nav>

    <div id="c-panel-1" role="tabpanel">
        @if (!empty($courseCards))
            <div class="space-y-3">
                @foreach ($courseCards as $index => $course)
                    @include('panel_v1.instructor.components.course-card', [
                        'course' => $course,
                        'index' => 'all-' . $index,
                        'demoSlug' => $demoSlug,
                    ])
                @endforeach
            </div>
        @else
            @include('panel_v1.instructor.components.empty-state', ['title' => 'لا توجد دورات حالياً'])
        @endif
    </div>

    <div id="c-panel-2" class="hidden" role="tabpanel">
        @if (!empty($liveCards))
            <div class="space-y-3">
                @foreach ($liveCards as $index => $course)
                    @include('panel_v1.instructor.components.course-card', [
                        'course' => $course,
                        'index' => 'live-' . $index,
                        'demoSlug' => $demoSlug,
                    ])
                @endforeach
            </div>
        @else
            @include('panel_v1.instructor.components.empty-state', ['title' => 'لا توجد محاضرات مباشرة حالياً'])
        @endif
    </div>

    <div id="c-panel-3" class="hidden" role="tabpanel">
        @if (!empty($recordedCards))
            <div class="space-y-3">
                @foreach ($recordedCards as $index => $course)
                    @include('panel_v1.instructor.components.course-card', [
                        'course' => $course,
                        'index' => 'recorded-' . $index,
                        'demoSlug' => $demoSlug,
                    ])
                @endforeach
            </div>
        @else
            @include('panel_v1.instructor.components.empty-state', ['title' => 'لا توجد دورات مسجلة حالياً'])
        @endif
    </div>

    <div id="c-panel-4" class="hidden" role="tabpanel">
        @if (!empty($draftCards))
            <div class="space-y-3">
                @foreach ($draftCards as $index => $course)
                    @include('panel_v1.instructor.components.course-card', [
                        'course' => $course,
                        'index' => 'draft-' . $index,
                        'demoSlug' => $demoSlug,
                    ])
                @endforeach
            </div>
        @else
            @include('panel_v1.instructor.components.empty-state', ['title' => 'لا توجد مسودات'])
        @endif
    </div>
</div>
@endsection
