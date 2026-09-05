@extends('panel_v1.student.course-player.layouts.app')

@section('content')
@php
    $courseSlug = $slug ?? 'demo';
    $take = $quizTake ?? [];
    $options = $take['options'] ?? [];
@endphp

<div class="flex flex-col gap-8 pb-10 ">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="size-11 rounded-12px bg-primary/10 center shrink-0">
                <span class="icon-[tabler--clipboard-list] size-6 text-primary"></span>
            </span>
            <h1 class="font-bold text-22px sm:text-24px text-primary">{{ $take['quiz_title'] ?? '' }}</h1>
        </div>
        <p class="font-semibold text-15px text-gray">
            السؤال {{ $take['current'] ?? 1 }} من {{ $take['total'] ?? 1 }}
        </p>
    </div>

    <div class="border border-d9 rounded-20px bg-white px-5 sm:px-8 py-8">
        <h2 class="font-extrabold text-24px sm:text-28px text-primary mb-2">{{ $take['question_title'] ?? '' }}</h2>
        <p class="font-medium text-15px text-gray mb-8">{{ $take['instruction'] ?? '' }}</p>

        <div class="space-y-4" role="radiogroup" aria-label="خيارات الإجابة">
            @foreach ($options as $option)
                @php $selected = !empty($option['selected']); @endphp
                <label
                    class="flex items-center gap-4 rounded-14px border px-4 sm:px-5 py-4 cursor-pointer transition
                           {{ $selected ? 'border-primary bg-primary/5' : 'border-d9 bg-white hover:border-primary/40' }}">
                    <input type="radio" name="quiz_option" value="{{ $option['id'] }}"
                        class="radio radio-primary" {{ $selected ? 'checked' : '' }}>
                    <span class="font-medium text-15px sm:text-16px text-black leading-snug">{{ $option['text'] }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-between gap-3">
           <button type="button" class="btn btn-ghost rounded-12px h-12 px-6 font-semibold text-15px text-gray border border-d9"
            disabled>
            السابق
        </button>
        <a href="{{ route('panel.v1.student.course.quiz.take', ['slug' => $courseSlug]) }}"
            class="btn btn-primary rounded-12px h-12 px-6 font-bold text-15px gap-2">
            السؤال التالي 
            <span class="icon-[tabler--arrow-left] size-5"></span>
        </a>

    </div>
</div>
@endsection
