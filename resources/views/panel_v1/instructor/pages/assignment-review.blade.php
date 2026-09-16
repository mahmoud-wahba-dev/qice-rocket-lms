@extends('panel_v1.instructor.layouts.course')

@section('content')
@php
    $answerParagraphs = $studentAnswerParagraphs ?? [];
    $answerPoints = $studentAnswerPoints ?? [];
    $hasAttachment = !empty($attachmentName) || !empty($attachmentUrl);
    $hasAnswer = count($answerParagraphs) > 0 || count($answerPoints) > 0 || $hasAttachment;
@endphp

<div class="pb-10 space-y-5">
    @if (!empty($studentProgressCards))
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
            @foreach ($studentProgressCards as $card)
                @php
                    $tone = $card['tone'] ?? 'primary';
                    $edge = match ($tone) {
                        'green' => '#0FC787',
                        'amber' => '#F59E0B',
                        default => '#0f4c45',
                    };
                    $valueClass = match ($tone) {
                        'green' => 'text-[#0FC787]',
                        'amber' => 'text-[#D97706]',
                        default => 'text-primary',
                    };
                @endphp
                <div class="rounded-14px bg-white border border-d9 px-4 sm:px-5 py-4"
                    style="border-inline-start: 4px solid {{ $edge }}">
                    <p class="font-medium text-13px sm:text-14px text-gray mb-2">{{ $card['label'] }}</p>
                    <p class="font-semibold text-22px sm:text-24px leading-none {{ $valueClass }}">{{ $card['value'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="border border-d9 rounded-20px bg-white px-5 sm:px-6 py-5 shadow-[0_1px_3px_rgba(15,23,42,0.04)]">
            <div class="flex items-center justify-between gap-3 mb-3">
                <h2 class="font-bold text-16px sm:text-17px text-primary">تقدم الطالب في الدورة</h2>
                <span class="font-bold text-15px text-primary">{{ $course['progress'] ?? 0 }}%</span>
            </div>
            <div class="h-2.5 rounded-full bg-[#EFEFEF] overflow-hidden mb-3" role="progressbar"
                aria-valuenow="{{ $course['progress'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                <div class="h-full rounded-full bg-[#0FC787]" style="width: {{ (int) ($course['progress'] ?? 0) }}%"></div>
            </div>
            <p class="font-medium text-13px text-gray">
                نفس مسار المحاضرات الظاهر للطالب أثناء مشاهدة الدورة — يمكنك متابعة ما أُكمل وما تبقّى من الشريط الجانبي.
            </p>
        </div>
    @endif

    <div class="border border-d9 rounded-20px bg-white px-5 sm:px-8 lg:px-10 py-7 sm:py-9 shadow-[0_1px_3px_rgba(15,23,42,0.04)]">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-7 sm:mb-9">
            <h1 class="font-bold text-20px sm:text-24px lg:text-26px text-primary leading-snug text-start">
                {{ $reviewTitle ?? '' }}
            </h1>
            <div class="flex flex-wrap items-center gap-2">
                @if (!empty($reviewStudentName))
                    <span class="inline-flex rounded-full bg-primary/10 px-3 py-1.5 font-semibold text-13px text-primary">
                        {{ $reviewStudentName }}
                    </span>
                @endif
                @if (!empty($historyStatusLabel))
                    <span class="inline-flex rounded-full bg-[#FFFBEB] px-3 py-1.5 font-semibold text-13px text-[#D97706]">
                        {{ $historyStatusLabel }}
                    </span>
                @endif
                @if ($historyGrade !== null && $historyGrade !== '')
                    <span class="inline-flex rounded-full bg-[#ECFDF5] px-3 py-1.5 font-semibold text-13px text-[#059669]">
                        الدرجة: {{ $historyGrade }} / {{ $maxGrade ?? '—' }}
                    </span>
                @endif
            </div>
        </div>

        <div class="mb-8 text-start">
            <h2 class="font-bold text-17px sm:text-19px text-primary mb-4 inline-flex items-center gap-2">
                نص الواجب والمطلوب
                <span class="text-[#E11D48]">*</span>
            </h2>

            <div class="rounded-14px bg-[#EFF6FF] border border-[#BFDBFE] border-s-[4px] border-s-[#3B82F6] px-5 sm:px-6 py-5 sm:py-6">
                <div class="flex items-start gap-2.5 mb-3">
                    <span class="icon-[tabler--notes] size-5 text-[#1D4ED8] shrink-0 mt-0.5"></span>
                    <p class="font-bold text-16px text-primary leading-snug">{{ $detailsTitle ?? '' }}</p>
                </div>
                <p class="font-medium text-14px sm:text-15px text-[#475569] leading-relaxed mb-5">
                    {{ $detailsBody ?? '' }}
                </p>
                <div class="flex items-start gap-2.5 mb-3">
                    <span class="icon-[tabler--list-check] size-5 text-[#1D4ED8] shrink-0 mt-0.5"></span>
                    <p class="font-bold text-15px text-primary leading-snug">{{ $pointsTitle ?? '' }}</p>
                </div>
                <ul class="space-y-2 font-medium text-14px sm:text-15px text-[#475569] leading-relaxed list-disc list-inside">
                    @foreach ($points ?? [] as $point)
                        <li>{{ $point }}</li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="mb-8 text-start">
            <h2 class="font-bold text-17px sm:text-19px text-primary mb-4 inline-flex items-center gap-2">
                <span class="icon-[tabler--pencil] size-5 text-primary shrink-0"></span>
                إجابة الطالب
            </h2>

            <div class="rounded-14px bg-[#ECFDF5] border border-[#A7F3D0]/70 border-s-[4px] border-s-[#10B981] px-5 sm:px-6 py-5 sm:py-6">
                @if (!$hasAnswer)
                    <p class="font-medium text-15px text-gray leading-relaxed">لم يُرسل الطالب نصاً أو مرفقاً بعد — يمكنك الاعتماد بعد مراجعة التسليم.</p>
                @else
                    @foreach ($answerParagraphs as $paragraph)
                        <p class="font-medium text-15px text-primary leading-relaxed mb-4">{{ $paragraph }}</p>
                    @endforeach

                    @if (count($answerPoints))
                        <ol class="list-decimal list-inside space-y-2 font-medium text-15px text-primary leading-relaxed mb-5">
                            @foreach ($answerPoints as $point)
                                <li>{{ $point }}</li>
                            @endforeach
                        </ol>
                    @endif

                    @if ($hasAttachment)
                        <div class="rounded-12px bg-white border border-d9 shadow-sm px-4 py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="size-11 rounded-10px bg-[#FEF2F2] center shrink-0">
                                    <span class="icon-[tabler--file-type-pdf] size-6 text-[#DC2626]"></span>
                                </span>
                                <div class="min-w-0">
                                    <p class="font-semibold text-14px sm:text-15px text-primary truncate">{{ $attachmentName ?: 'مرفق التسليم' }}</p>
                                    <p class="font-medium text-12px text-gray mt-0.5">
                                        {{ $attachmentSize ?? 'ملف مرفق' }}
                                        @if (!empty($attachmentScan))
                                            <span class="mx-1.5 text-d9">·</span>
                                            <span class="text-[#059669]">{{ $attachmentScan }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            @if (!empty($attachmentUrl))
                                <a href="{{ $attachmentUrl }}" target="_blank" rel="noopener"
                                    class="inline-flex items-center justify-center gap-1.5 font-bold text-14px text-primary shrink-0 hover:opacity-80 transition">
                                    تحميل / معاينة
                                </a>
                            @endif
                        </div>
                    @endif
                @endif
            </div>
        </div>

        @if (!empty($canGrade))
            <div class="flex justify-end">
                <button type="button"
                    class="inline-flex items-center justify-center gap-2 rounded-12px bg-primary h-12 px-6 sm:px-8 font-bold text-15px sm:text-16px text-white hover:opacity-95 transition shadow-[0_6px_20px_rgba(15,76,69,0.18)]"
                    aria-haspopup="dialog" aria-expanded="false" aria-controls="instructor-grade-modal"
                    data-overlay="#instructor-grade-modal">
                    تقييم التكليف ورصد الدرجة
                    <span class="icon-[tabler--arrow-narrow-left] size-5"></span>
                </button>
            </div>
        @endif
    </div>
</div>
@endsection
