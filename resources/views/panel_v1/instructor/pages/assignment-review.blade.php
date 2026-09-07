@extends('panel_v1.instructor.layouts.course')

@section('content')
@php
    $answerParagraphs = $studentAnswerParagraphs ?? [];
    $answerPoints = $studentAnswerPoints ?? [];
@endphp

<div class="pb-10">
    <div class="border border-d9 rounded-20px bg-white px-5 sm:px-8 lg:px-10 py-7 sm:py-9 shadow-[0_1px_3px_rgba(15,23,42,0.04)]">
        <h1 class="font-bold text-20px sm:text-24px lg:text-26px text-primary mb-7 sm:mb-9 leading-snug text-start">
            {{ $reviewTitle ?? '' }}
        </h1>

        {{-- Requirements --}}
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

        {{-- Student answer --}}
        <div class="mb-8 text-start">
            <h2 class="font-bold text-17px sm:text-19px text-primary mb-4 inline-flex items-center gap-2">
                <span class="icon-[tabler--pencil] size-5 text-primary shrink-0"></span>
                إجابة الطالب
            </h2>

            <div class="rounded-14px bg-[#ECFDF5] border border-[#A7F3D0]/70 border-s-[4px] border-s-[#10B981] px-5 sm:px-6 py-5 sm:py-6">
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

                <div class="rounded-12px bg-white border border-d9 shadow-sm px-4 py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="size-11 rounded-10px bg-[#FEF2F2] center shrink-0">
                            <span class="icon-[tabler--file-type-pdf] size-6 text-[#DC2626]"></span>
                        </span>
                        <div class="min-w-0">
                            <p class="font-semibold text-14px sm:text-15px text-primary truncate">{{ $attachmentName ?? '' }}</p>
                            <p class="font-medium text-12px text-gray mt-0.5">
                                {{ $attachmentSize ?? '2.4 MB' }}
                                @if (!empty($attachmentScan))
                                    <span class="mx-1.5 text-d9">·</span>
                                    <span class="text-[#059669]">{{ $attachmentScan }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <a href="#"
                        class="inline-flex items-center justify-center gap-1.5 font-bold text-14px text-primary shrink-0 hover:opacity-80 transition">
                        تحميل / معاينة
                    </a>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="button"
                class="inline-flex items-center justify-center gap-2 rounded-12px bg-primary h-12 px-6 sm:px-8 font-bold text-15px sm:text-16px text-white hover:opacity-95 transition shadow-[0_6px_20px_rgba(15,76,69,0.18)]"
                aria-haspopup="dialog" aria-expanded="false" aria-controls="instructor-grade-modal"
                data-overlay="#instructor-grade-modal">
                تقييم التكليف ورصد الدرجة
                <span class="icon-[tabler--arrow-narrow-left] size-5"></span>
            </button>
        </div>
    </div>
</div>
@endsection
