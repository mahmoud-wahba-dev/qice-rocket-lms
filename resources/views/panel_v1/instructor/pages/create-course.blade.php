@extends('panel_v1.instructor.layouts.app')

@section('content')
@php
    $step = (int) ($wizardStep ?? 1);
    $meta = ($wizardSteps ?? [])[$step] ?? [];
@endphp

<div class="pb-4" data-create-course>
    @include('panel_v1.instructor.components.create-course.draft-bar')
    @include('panel_v1.instructor.components.create-course.stepper')

    <div class="mb-5 sm:mb-6 text-start">
        <p class="font-medium text-14px sm:text-15px text-gray mb-1">الخطوة {{ $step }} من 5</p>
        <h2 class="font-semibold text-22px sm:text-26px text-primary">{{ $meta['title'] ?? '' }}</h2>
    </div>

    <div class="space-y-5 sm:space-y-6">
        @include('panel_v1.instructor.pages.create-course.step-'.$step)
    </div>

    @include('panel_v1.instructor.components.create-course.footer')
</div>
@endsection
