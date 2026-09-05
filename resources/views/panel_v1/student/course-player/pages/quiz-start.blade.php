@extends('panel_v1.student.course-player.layouts.app')

@section('content')
@php
    $courseSlug = $slug ?? 'demo';
    $quizData = $quiz ?? [];
@endphp

<div class="flex flex-col items-center justify-center pb-12 pt-4 sm:pt-8 min-h-[70vh]">
    <div class="w-full max-w-3xl text-center px-2">
        {{-- Header icon --}}
        <div class="size-[4.5rem] sm:size-20 rounded-[1.25rem] bg-primary center mx-auto mb-7 shadow-sm">
            <span class="icon-[tabler--clipboard-list] size-9 text-white"></span>
        </div>

        <h1 class="font-extrabold text-28px sm:text-[2rem] text-[#0F172A] mb-3 leading-snug">
            {{ $quizData['title'] ?? '' }}
        </h1>
        <p class="font-medium text-16px sm:text-18px text-[#64748B] leading-relaxed mb-10 max-w-2xl mx-auto">
            {{ $quizData['description'] ?? '' }}
        </p>

        {{-- Meta cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-8">
            <div class="rounded-16px bg-[#F8FAFC] border border-[#E2E8F0] px-3 sm:px-4 py-5 text-center">
                <p class="font-medium text-13px sm:text-14px text-[#64748B] mb-2">وقت الاختبار</p>
                <p class="font-bold text-18px sm:text-20px text-[#0F172A]">{{ $quizData['duration'] ?? '' }}</p>
            </div>
            <div class="rounded-16px bg-[#F8FAFC] border border-[#E2E8F0] px-3 sm:px-4 py-5 text-center">
                <p class="font-medium text-13px sm:text-14px text-[#64748B] mb-2">عدد الأسئلة</p>
                <p class="font-bold text-18px sm:text-20px text-[#0F172A]">{{ $quizData['questions_count'] ?? '' }}</p>
            </div>
            <div class="rounded-16px bg-[#F8FAFC] border border-[#E2E8F0] px-3 sm:px-4 py-5 text-center">
                <p class="font-medium text-13px sm:text-14px text-[#64748B] mb-2">درجة النجاح</p>
                <p class="font-bold text-18px sm:text-20px text-[#0F172A]">{{ $quizData['pass_score'] ?? '' }}</p>
            </div>
            <div class="rounded-16px bg-[#F8FAFC] border border-[#E2E8F0] px-3 sm:px-4 py-5 text-center">
                <p class="font-medium text-13px sm:text-14px text-[#64748B] mb-2">المحاولات</p>
                <p class="font-bold text-18px sm:text-20px text-[#0F172A]">{{ $quizData['attempts'] ?? '' }}</p>
            </div>
        </div>

        {{-- Deadline alert --}}
        <div
            class="rounded-14px border border-dashed border-[#FCA5A5] bg-[#FEF2F2] px-4 sm:px-5 py-3.5 mb-10 flex items-center justify-center gap-2.5 text-center">
            <span class="icon-[tabler--clock] size-5 text-[#DC2626] shrink-0"></span>
            <p class="font-semibold text-14px sm:text-15px text-[#DC2626] leading-snug">
                {{ $quizData['deadline'] ?? '' }}
            </p>
        </div>

        <a href="{{ route('panel.v1.student.course.quiz.take', ['slug' => $courseSlug]) }}"
            class="btn btn-primary rounded-12px h-12 sm:h-14 px-8 sm:px-10 font-bold text-16px gap-2 inline-flex shadow-[0_8px_24px_rgba(15,76,69,0.25)]">
            ابدأ الاختبار الآن
            <span class="icon-[tabler--arrow-left] size-5"></span>
        </a>
    </div>
</div>
@endsection
