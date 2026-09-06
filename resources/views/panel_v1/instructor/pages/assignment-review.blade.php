@extends('panel_v1.instructor.layouts.course')

@section('content')
<div class="pb-10">
    <div class="border border-d9 rounded-20px bg-white px-5 sm:px-8 lg:px-10 py-8 sm:py-10">
        <h1 class="font-extrabold text-24px sm:text-28px text-[#0F172A] mb-8 leading-snug text-start">
            {{ $reviewTitle ?? '' }}
        </h1>

        <div class="mb-8 text-start">
            <h2 class="font-bold text-18px text-[#0F172A] mb-4">
                نص الواجب والمطلوب <span class="text-[#E11D48]">*</span>
            </h2>
            <div class="rounded-14px bg-[#F1F5F9] px-5 py-5">
                <div class="flex items-start gap-2.5 mb-3">
                    <span class="icon-[tabler--notebook] size-5 text-[#0F172A] shrink-0 mt-0.5"></span>
                    <p class="font-bold text-16px text-[#0F172A]">{{ $detailsTitle ?? '' }}</p>
                </div>
                <p class="font-medium text-14px text-[#64748B] leading-relaxed mb-5 pe-7">{{ $detailsBody ?? '' }}</p>
                <div class="flex items-start gap-2.5 mb-3">
                    <span class="icon-[tabler--pinned] size-5 text-[#E11D48] shrink-0 mt-0.5"></span>
                    <p class="font-bold text-15px text-[#0F172A]">{{ $pointsTitle ?? '' }}</p>
                </div>
                <ul class="list-disc list-inside space-y-1 font-medium text-14px text-[#64748B] pe-7">
                    @foreach ($points ?? [] as $point)
                        <li>{{ $point }}</li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="mb-8 text-start">
            <h2 class="font-bold text-18px text-[#0F172A] mb-4 inline-flex items-center gap-2">
                <span class="icon-[tabler--pencil] size-5"></span>
                إجابة الطالب
            </h2>
            <div class="rounded-14px bg-[#ECFDF5] border border-[#0FC787]/40 border-s-4 border-s-[#0FC787] px-5 py-5">
                <p class="font-medium text-15px text-[#0F172A] leading-relaxed mb-5">
                    {{ $studentAnswer ?? '' }}
                </p>
                <div class="rounded-12px bg-white border border-d9 px-4 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="icon-[tabler--file-type-pdf] size-6 text-primary shrink-0"></span>
                        <p class="font-semibold text-14px text-primary truncate">{{ $attachmentName ?? '' }}</p>
                    </div>
                    <a href="#" class="font-bold text-14px text-primary shrink-0">تحميل / معاينة</a>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="button"
                class="btn btn-primary rounded-12px h-12 px-6 font-bold text-15px"
                aria-haspopup="dialog" data-overlay="#instructor-grade-modal">
                تقييم التكليف ورصد الدرجة
            </button>
        </div>
    </div>
</div>
@endsection
