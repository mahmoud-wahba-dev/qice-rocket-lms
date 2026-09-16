@extends('panel_v1.instructor.layouts.app')

@section('content')
@php
    $items = $reviewItems ?? [];
@endphp

<div class="space-y-6 pb-10">
    @component('panel_v1.instructor.components.page-header', [
        'title' => 'تصحيح نتيجة اختبار',
        'subtitle' => ($studentName ?? '') . ' — ' . ($quizTitle ?? '') . (!empty($courseTitle) ? (' • ' . $courseTitle) : ''),
    ])
        @slot('actions')
            <a href="{{ route('panel.v1.instructor.quizzes') }}"
                class="inline-flex items-center gap-2 rounded-12px border border-d9 px-5 h-12 font-semibold text-15px text-primary bg-white hover:bg-fa transition">
                العودة للاختبارات
            </a>
        @endslot
    @endcomponent

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="rounded-14px bg-white border border-d9 px-5 py-4" style="border-inline-start:4px solid #0f4c45">
            <p class="font-medium text-13px text-gray mb-1">درجة الطالب الحالية</p>
            <p class="font-semibold text-24px text-primary">{{ (int) ($quizResult->user_grade ?? 0) }} / {{ $maxGrade ?? 0 }}</p>
        </div>
        <div class="rounded-14px bg-white border border-d9 px-5 py-4" style="border-inline-start:4px solid #F59E0B">
            <p class="font-medium text-13px text-gray mb-1">الحالة</p>
            <p class="font-semibold text-20px text-[#D97706]">
                {{ $quizResult->status === 'waiting' ? 'بانتظار التصحيح' : ($quizResult->status === 'passed' ? 'ناجح' : 'راسب') }}
            </p>
        </div>
        <div class="rounded-14px bg-white border border-d9 px-5 py-4" style="border-inline-start:4px solid #0FC787">
            <p class="font-medium text-13px text-gray mb-1">عدد الأسئلة</p>
            <p class="font-semibold text-24px text-[#0FC787]">{{ count($items) }}</p>
        </div>
    </div>

    <div class="space-y-4">
        @forelse ($items as $index => $item)
            <div class="rounded-20px border border-d9 bg-white px-5 sm:px-7 py-6 shadow-sm">
                <p class="font-bold text-16px sm:text-18px text-primary leading-relaxed mb-4">
                    <span class="text-gray font-semibold">سؤال {{ $index + 1 }}:</span>
                    {{ $item['question'] }}
                </p>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
                    <div class="rounded-14px border border-[#BFDBFE] bg-[#EFF6FF] px-4 py-4">
                        <span class="inline-flex rounded-full bg-[#DBEAFE] px-3 py-1 font-semibold text-12px text-[#1D4ED8] mb-3">
                            الإجابة النموذجية
                        </span>
                        <p class="font-medium text-14px sm:text-15px text-primary leading-relaxed">{{ $item['model_answer'] }}</p>
                    </div>
                    <div class="rounded-14px border border-[#A7F3D0] bg-[#ECFDF5] px-4 py-4">
                        <span class="inline-flex rounded-full bg-[#D1FAE5] px-3 py-1 font-semibold text-12px text-[#059669] mb-3">
                            إجابة الطالب
                        </span>
                        <p class="font-medium text-14px sm:text-15px text-primary leading-relaxed">{{ $item['student_answer'] }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <span class="font-semibold text-14px text-primary">{{ $item['grade'] }} درجة</span>
                    @if ($item['is_correct'] === true)
                        <span class="inline-flex rounded-full bg-[#ECFDF5] px-3 py-1 font-semibold text-12px text-[#059669]">صحيحة</span>
                    @elseif ($item['is_correct'] === false)
                        <span class="inline-flex rounded-full bg-[#FEF2F2] px-3 py-1 font-semibold text-12px text-[#DC2626]">خاطئة</span>
                    @else
                        <span class="inline-flex rounded-full bg-[#FFFBEB] px-3 py-1 font-semibold text-12px text-[#D97706]">يحتاج تقييم يدوي</span>
                    @endif
                    @if (($item['type'] ?? '') === 'descriptive')
                        <span class="inline-flex rounded-full bg-primary/10 px-3 py-1 font-semibold text-12px text-primary">سؤال وصفي</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-14px border border-d9 bg-white p-8 text-center">
                <p class="font-medium text-15px text-gray">لا توجد إجابات مسجلة لهذه المحاولة.</p>
            </div>
        @endforelse
    </div>

    <form method="POST" action="{{ route('panel.v1.instructor.quiz-results.grade.store', ['resultId' => $quizResult->id]) }}"
        class="rounded-20px border border-d9 bg-white px-5 sm:px-8 py-7 space-y-6 shadow-sm">
        @csrf
        <h2 class="font-bold text-20px text-primary">اعتماد وتقييم درجة الطالب</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div class="relative">
                <label class="absolute -top-2.5 start-4 z-[1] bg-white px-2 font-medium text-13px text-primary">الدرجة *</label>
                <div class="flex items-center border-2 border-primary rounded-12px overflow-hidden">
                    <input type="number" name="user_grade" value="{{ old('user_grade', $quizResult->user_grade ?? 0) }}"
                        min="0" max="{{ max(0, (int) ($maxGrade ?? 0)) }}"
                        class="input border-0 w-full h-14 font-bold text-22px text-primary focus:outline-none">
                    <span class="px-4 font-medium text-13px text-gray whitespace-nowrap">من {{ $maxGrade ?? 0 }}</span>
                </div>
            </div>
            <div class="relative">
                <label class="absolute -top-2.5 start-4 z-[1] bg-white px-2 font-medium text-13px text-primary">الحالة *</label>
                <select name="status"
                    class="select select-bordered w-full h-14 rounded-12px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary">
                    @foreach (['passed' => 'ناجح', 'failed' => 'راسب', 'waiting' => 'بانتظار التصحيح'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $quizResult->status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="flex justify-end">
            <button type="submit" class="btn btn-primary rounded-12px h-12 px-8 font-bold text-15px">
                إرسال الدرجة والاعتماد
            </button>
        </div>
    </form>
</div>
@endsection
