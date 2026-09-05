@extends('panel_v1.student.course-player.layouts.app')

@section('content')
@php
    $page = $assignmentPage ?? [];
    $wordLimit = $page['word_limit'] ?? 500;
    $points = $page['points'] ?? [];
@endphp

<div class="pb-10">
    <div class="border border-d9 rounded-20px bg-white px-5 sm:px-8 lg:px-10 py-8 sm:py-10">
        {{-- Header --}}
        <div class="mb-7 text-start">
            <h1 class="font-extrabold text-28px sm:text-32px text-[#0F172A] mb-3 leading-snug">
                {{ $page['title'] ?? '' }}
            </h1>
            <p class="font-medium text-15px sm:text-16px text-[#64748B] leading-relaxed">
                {{ $page['subtitle'] ?? '' }}
            </p>
        </div>

        {{-- Assignment details --}}
        <div class="rounded-14px bg-[#F1F5F9] px-5 sm:px-6 py-5 mb-8 text-start">
            <div class="flex items-start gap-2.5 mb-3">
                <span class="icon-[tabler--notebook] size-5 text-[#0F172A] shrink-0 mt-0.5"></span>
                <h2 class="font-bold text-16px sm:text-17px text-[#0F172A] leading-snug">
                    {{ $page['details_title'] ?? '' }}
                </h2>
            </div>

            <p class="font-medium text-14px sm:text-15px text-[#64748B] leading-relaxed mb-5 pe-7">
                {{ $page['details_body'] ?? '' }}
            </p>

            <div class="flex items-start gap-2.5 mb-3">
                <span class="icon-[tabler--pinned] size-5 text-[#E11D48] shrink-0 mt-0.5"></span>
                <p class="font-bold text-15px sm:text-16px text-[#0F172A] leading-snug">
                    {{ $page['points_title'] ?? '' }}
                </p>
            </div>

            <p class="font-medium text-14px sm:text-15px text-[#64748B] leading-relaxed pe-7">
                @foreach ($points as $index => $point)
                    <span>{{ $point }}</span>@if (!$loop->last)<span class="mx-2 text-[#94A3B8]">▪</span>@endif
                @endforeach
            </p>
        </div>

        {{-- Dotted divider --}}
        <div class="border-t border-dashed border-d9 mb-8" aria-hidden="true"></div>

        {{-- Submit section --}}
        <div class="text-start">
            <h2 class="font-bold text-20px sm:text-22px text-[#0F172A] mb-6">
                {{ $page['form_title'] ?? 'إجابة التكليف والتسليم' }}
            </h2>

            <label for="assignment-answer" class="font-medium text-14px text-[#64748B] mb-2.5 block">
                كتابة وصف
            </label>

            <div class="border border-[#E2E8F0] rounded-14px overflow-hidden mb-2 bg-white shadow-[inset_0_1px_0_rgba(15,23,42,0.04)]">
                <textarea id="assignment-answer"
                    class="w-full border-0 rounded-none px-4 pt-4 pb-2 font-medium text-15px text-[#0F172A] placeholder:text-[#94A3B8] focus:outline-none min-h-44 resize-y bg-transparent"
                    placeholder="ابدأ كتابة نص المقال هنا..."
                    data-word-limit="{{ $wordLimit }}"></textarea>
                <p class="px-4 pb-3 font-medium text-13px text-[#94A3B8] text-end" data-word-count>
                    عدد الكلمات: 0 / {{ $wordLimit }}
                </p>
            </div>

            <label class="font-medium text-14px text-[#64748B] mb-2.5 mt-6 block">
                إرفاق المقال كملف خارجي (اختياري)
            </label>

            <label
                class="flex items-center justify-center gap-2.5 rounded-12px border border-dashed border-[#0FC787]/60 bg-[#ECFDF5] px-5 py-4 cursor-pointer hover:bg-[#D1FAE5]/50 transition mb-8">
                <span class="icon-[tabler--paperclip] size-5 text-primary shrink-0"></span>
                <span class="font-semibold text-14px sm:text-15px text-primary text-center">
                    اضغط هنا لرفع الملف بصيغة (PDF أو DOCX)
                </span>
                <input type="file" class="hidden" accept=".pdf,.doc,.docx" disabled>
            </label>

            <div class="flex justify-end">
                <button type="button"
                    class="btn btn-primary rounded-12px h-12 px-8 font-bold text-16px shadow-[0_6px_20px_rgba(15,76,69,0.2)]">
                    أرسل الآن
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
