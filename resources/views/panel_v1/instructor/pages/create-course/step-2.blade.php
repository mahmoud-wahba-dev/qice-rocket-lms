@php
    $input = 'input input-bordered w-full h-12 sm:h-14 rounded-10px border-d9 font-medium text-15px sm:text-16px text-black focus:outline-none focus:border-primary';
    $card = 'border border-d9 rounded-14px bg-white px-5 sm:px-7 py-6 sm:py-8';
    $draftIdValue = $draftId ?? request('draft');
@endphp

{{-- Curriculum (standalone forms — not nested in wizard store form) --}}
<section class="{{ $card }}" data-curriculum-root>
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5 sm:mb-6">
        <div class="flex items-center gap-3">
            <span class="size-10 rounded-10px bg-primary/10 center shrink-0">
                <span class="icon-[tabler--books] size-5 text-primary"></span>
            </span>
            <h2 class="font-bold text-18px sm:text-20px text-primary">بناء المنهج الدراسي</h2>
        </div>
    </div>

    @if (true)
        <div class="rounded-12px bg-[#FAFAF4] border border-d9 px-5 py-4 mb-6 {{ !empty($draftIdValue) ? 'hidden' : '' }}" data-curriculum-need-draft>
            <p class="font-medium text-14px text-gray">احفظ بيانات الخطوة الأولى أولاً (أو اضغط التالي) لتتمكن من بناء المنهج — سيتم حفظ المسودة تلقائيًا.</p>
        </div>
        <form method="POST" action="{{ $curriculumChapterStoreUrl ?? route('panel.v1.instructor.curriculum.chapters.store') }}"
            class="flex flex-col sm:flex-row gap-3 mb-6 {{ empty($draftIdValue) ? 'hidden' : '' }}"
            data-curriculum-ajax="chapter" data-curriculum-ready>
            @csrf
            <input type="hidden" name="draft_id" value="{{ $draftIdValue }}" data-draft-id-input>
            <input type="text" name="title" required placeholder="عنوان وحدة جديدة (مثال: الوحدة الأولى)"
                class="{{ $input }} flex-1" data-field-label="عنوان الوحدة">
            <button type="submit"
                class="inline-flex items-center justify-center gap-2 h-12 sm:h-14 px-5 rounded-10px bg-primary text-white font-semibold text-15px hover:opacity-90 transition shrink-0">
                <span class="icon-[tabler--plus] size-4"></span>
                إضافة وحدة
            </button>
        </form>
    @endif

    <div data-curriculum-units>
        @forelse ($curriculumUnits ?? [] as $unit)
            @include('panel_v1.instructor.pages.create-course.partials.curriculum-unit', [
                'unit' => $unit,
                'draftIdValue' => $draftIdValue,
                'input' => $input,
            ])
        @empty
            <div class="rounded-14px border border-dashed border-d9 px-6 py-10 center flex-col text-center" data-curriculum-empty>
                <p class="font-semibold text-18px text-gray">لا توجد وحدات بعد</p>
                <p class="font-medium text-14px text-gray mt-2">أضف أول وحدة من الأعلى لبدء بناء المنهج.</p>
            </div>
        @endforelse
    </div>
</section>

<template id="curriculum-unit-template">
    @include('panel_v1.instructor.pages.create-course.partials.curriculum-unit', [
        'unit' => [
            'id' => '__ID__',
            'title' => '__TITLE__',
            'lessons' => [],
            'delete_url' => '__DELETE_URL__',
            'session_store_url' => '__SESSION_STORE__',
            'file_store_url' => '__FILE_STORE__',
            'text_store_url' => '__TEXT_STORE__',
        ],
        'draftIdValue' => $draftIdValue ?? '__DRAFT__',
        'input' => $input,
        'isTemplate' => true,
    ])
</template>
