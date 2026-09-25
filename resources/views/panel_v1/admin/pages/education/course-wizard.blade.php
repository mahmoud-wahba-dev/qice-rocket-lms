@extends('panel_v1.admin.layouts.app')

@section('content')
@php
    $errors = $errors ?? new \Illuminate\Support\ViewErrorBag(new \Illuminate\Support\MessageBag());
    $step = (int) ($wizardStep ?? 1);
    $meta = ($wizardSteps ?? [])[$step] ?? [];
    $wizardStoreUrl = $wizardStoreUrl ?? route('panel.v1.admin.education.courses.wizard.store', ['id' => $draftId]);
    $wizardCreateUrl = $wizardCreateUrl ?? route('panel.v1.admin.education.courses.edit', ['id' => $draftId]);
    $wizardCoursesUrl = $wizardCoursesUrl ?? route('panel.v1.admin.education.section', ['section' => 'courses']);
    $stepMetaJson = collect($wizardSteps ?? [])->map(fn ($item, $num) => [
        'num' => (int) $num,
        'label' => $item['label'] ?? '',
        'title' => $item['title'] ?? '',
        'next' => $item['next'] ?? 'التالي',
        'progress' => (int) ($item['progress'] ?? ($num * 20)),
    ])->values();
@endphp

<div class="pb-4" data-create-course data-spa-wizard
    data-draft-id="{{ $draftId ?? '' }}"
    data-current-step="{{ $step }}"
    data-store-url="{{ $wizardStoreUrl }}"
    data-create-url="{{ $wizardCreateUrl }}"
    data-courses-url="{{ $wizardCoursesUrl }}"
    data-csrf="{{ csrf_token() }}"
    data-steps='@json($stepMetaJson)'>
    @include('panel_v1.instructor.components.create-course.draft-bar')
    @include('panel_v1.instructor.components.create-course.stepper')

    <div class="mb-5 sm:mb-6 text-start">
        <p class="font-medium text-14px sm:text-15px text-gray mb-1" data-wizard-step-label>الخطوة {{ $step }} من 5</p>
        <h2 class="font-semibold text-22px sm:text-26px text-primary" data-wizard-step-title>{{ $meta['title'] ?? '' }}</h2>
    </div>

    <div class="mb-5 rounded-14px border border-[#FECACA] bg-[#FEF2F2] px-5 py-4 hidden" role="alert" data-wizard-errors>
        <p class="font-bold text-15px text-[#B91C1C] mb-2">تعذر الحفظ — راجع الحقول التالية:</p>
        <ul class="space-y-1.5 list-disc ps-5" data-wizard-errors-list></ul>
    </div>

    <form method="POST" action="{{ $wizardStoreUrl }}" enctype="multipart/form-data"
        data-wizard-form class="space-y-5 sm:space-y-6">
        @csrf
        <input type="hidden" name="wizard_step" value="{{ $step }}" data-wizard-step-input>
        <input type="hidden" name="draft_id" value="{{ $draftId ?? '' }}" data-draft-id-input>
        <input type="hidden" name="go_next" value="stay" data-go-next-input>
        <input type="hidden" name="autosave" value="0" data-autosave-input>
        <input type="hidden" name="save_only" value="0" data-save-only-input>

        <div data-wizard-panel="1" class="space-y-5 sm:space-y-6 {{ $step === 1 ? '' : 'hidden' }}">
            @include('panel_v1.instructor.pages.create-course.step-1')
        </div>
        <div data-wizard-panel="3" class="space-y-5 sm:space-y-6 {{ $step === 3 ? '' : 'hidden' }}">
            @include('panel_v1.instructor.pages.create-course.step-3')
        </div>
        <div data-wizard-panel="4" class="space-y-5 sm:space-y-6 {{ $step === 4 ? '' : 'hidden' }}">
            @include('panel_v1.instructor.pages.create-course.step-4')
        </div>
        <div data-wizard-panel="5" class="space-y-5 sm:space-y-6 {{ $step === 5 ? '' : 'hidden' }}">
            @include('panel_v1.instructor.pages.create-course.step-5')
        </div>
    </form>

    <div data-wizard-panel="2" class="space-y-5 sm:space-y-6 {{ $step === 2 ? '' : 'hidden' }}">
        @include('panel_v1.instructor.pages.create-course.step-2')
    </div>

    @include('panel_v1.instructor.components.create-course.footer')
</div>
@include('panel_v1.instructor.components.confirm-delete-modal')
@include('panel_v1.instructor.components.curriculum-file-preview-modal')
@endsection
