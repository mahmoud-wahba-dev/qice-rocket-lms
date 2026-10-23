@extends('panel_v1.student.course-player.layouts.app')

@section('content')
@php
    $courseSlug = $slug ?? ($webinar->slug ?? 'demo');
    $items = $reviewItems ?? [];
    $status = $quizResult->status ?? '';
    $statusBorder = match ($status) {
        'passed' => '#0FC787',
        'waiting' => '#F59E0B',
        default => '#DC2626',
    };
    $statusTone = match ($status) {
        'passed' => 'text-[#059669]',
        'waiting' => 'text-[#D97706]',
        default => 'text-[#DC2626]',
    };
@endphp

<div class="space-y-6 pb-12 pt-2 sm:pt-4 px-1 sm:px-0">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <p class="font-medium text-13px text-gray mb-1">نتيجة الاختبار والحلول</p>
            <h1 class="font-extrabold text-22px sm:text-26px text-primary leading-snug">{{ $quizTitle ?? 'اختبار' }}</h1>
            @if (!empty($courseTitle))
                <p class="font-medium text-14px text-gray mt-1">{{ $courseTitle }}</p>
            @endif
        </div>
        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <a href="{{ route('panel.v1.student.course.quiz', ['slug' => $courseSlug, 'quiz' => $quizResult->quiz_id ?? null]) }}"
                class="inline-flex items-center gap-2 h-11 px-4 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition">
                العودة للاختبار
            </a>
            <a href="{{ route('panel.v1.student.course.watch', ['slug' => $courseSlug]) }}"
                class="inline-flex items-center gap-2 h-11 px-4 rounded-12px bg-primary text-white font-semibold text-14px hover:opacity-95 transition">
                متابعة الدورة
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="rounded-14px bg-white border border-d9 px-5 py-4" style="border-inline-start:4px solid #0f4c45">
            <p class="font-medium text-13px text-gray mb-1">درجتك</p>
            <p class="font-semibold text-24px text-primary">{{ (int) ($quizResult->user_grade ?? 0) }} / {{ $maxGrade ?? 0 }}</p>
            @if (!empty($passMark))
                <p class="font-medium text-12px text-gray mt-1">درجة النجاح: {{ $passMark }}</p>
            @endif
        </div>
        <div class="rounded-14px bg-white border border-d9 px-5 py-4" style="border-inline-start:4px solid {{ $statusBorder }}">
            <p class="font-medium text-13px text-gray mb-1">الحالة</p>
            <p class="font-semibold text-20px {{ $statusTone }}">{{ $statusLabel ?? '—' }}</p>
        </div>
        <div class="rounded-14px bg-white border border-d9 px-5 py-4" style="border-inline-start:4px solid #0FC787">
            <p class="font-medium text-13px text-gray mb-1">عدد الأسئلة</p>
            <p class="font-semibold text-24px text-[#0FC787]">{{ count($items) }}</p>
        </div>
    </div>

    @if ($status === 'waiting')
        <div class="rounded-14px border border-[#FDE68A] bg-[#FFFBEB] px-4 py-3.5 flex items-start gap-3">
            <span class="icon-[tabler--hourglass] size-5 text-[#D97706] shrink-0 mt-0.5"></span>
            <p class="font-medium text-14px text-[#92400E] leading-relaxed">
                بعض الإجابات الوصفية بانتظار تصحيح المدرب.
            </p>
        </div>
    @endif

    @if (!empty($canViewSolutions))
        <div class="space-y-4">
            <h2 class="font-bold text-18px text-primary">مراجعة الإجابات والحلول</h2>

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
                                إجابتك
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
    @else
        <div class="rounded-20px border border-d9 bg-white px-5 sm:px-8 py-8 text-center shadow-sm">
            <div class="size-14 rounded-16px bg-[#FFF7ED] center mx-auto mb-4">
                <span class="icon-[tabler--lock] size-7 text-[#D97706]"></span>
            </div>
            <h2 class="font-bold text-18px text-primary mb-2">الحلول مخفية حالياً</h2>
            <p class="font-medium text-14px text-gray leading-relaxed max-w-xl mx-auto">
                تُعرض مراجعة الإجابات والحلول بعد استنفاد جميع المحاولات المتاحة، لمنع الاطلاع على الحلول قبل إعادة المحاولة.
            </p>
            @if ($attemptsLeft !== null)
                <p class="font-semibold text-14px text-[#D97706] mt-4">
                    المحاولات المتبقية: {{ $attemptsLeft }}
                    @if (!empty($attemptsLimit))
                        <span class="font-medium text-gray">من أصل {{ $attemptsLimit }}</span>
                    @endif
                </p>
            @else
                <p class="font-medium text-13px text-gray mt-4">هذا الاختبار بعدد محاولات غير محدود، لذلك لا تُعرض الحلول.</p>
            @endif
            @if (($attemptsLeft ?? 0) > 0)
                <a href="{{ route('panel.v1.student.course.quiz.take', ['slug' => $courseSlug, 'quiz' => $quizResult->quiz_id ?? null]) }}"
                    class="inline-flex items-center gap-2 mt-6 h-12 px-6 rounded-12px bg-primary text-white font-bold text-14px hover:opacity-95 transition">
                    إعادة المحاولة
                    <span class="icon-[tabler--arrow-left] size-4"></span>
                </a>
            @endif
        </div>
    @endif
</div>
@endsection
