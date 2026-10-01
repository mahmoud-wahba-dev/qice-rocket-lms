@extends('panel_v1.admin.layouts.app')

@section('content')
@php
    $answerParagraphs = $answerParagraphs ?? [];
@endphp

<div class="space-y-6 pb-8 max-w-4xl">
    @component('panel_v1.admin.components.page-header', [
        'title' => 'تصحيح التكليف',
        'subtitle' => ($assignmentTitle ?? '') . (!empty($courseTitle) ? ' — ' . $courseTitle : ''),
    ])
        @slot('actions')
            <a href="{{ $coursePerformanceUrl ?? route('panel.v1.admin.education.section', ['section' => 'courses']) }}"
                class="inline-flex items-center justify-center gap-2 rounded-12px border border-d9 bg-white px-5 h-12 font-semibold text-15px text-primary hover:bg-fa transition">
                العودة للوحة الأداء
            </a>
        @endslot
    @endcomponent

    <div class="rounded-14px border border-d9 bg-white px-5 sm:px-7 py-6 space-y-5">
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex rounded-full bg-primary/10 px-3 py-1.5 font-semibold text-13px text-primary">
                {{ $studentName ?? 'طالب' }}
            </span>
            @if (!empty($studentEmail))
                <span class="font-medium text-13px text-gray">{{ $studentEmail }}</span>
            @endif
            <span class="inline-flex rounded-full bg-[#FFFBEB] px-3 py-1.5 font-semibold text-13px text-[#D97706]">
                {{ $historyStatusLabel ?? '—' }}
            </span>
            @if ($historyGrade !== null && $historyGrade !== '')
                <span class="inline-flex rounded-full bg-[#ECFDF5] px-3 py-1.5 font-semibold text-13px text-[#059669]">
                    الدرجة: {{ $historyGrade }} / {{ $maxGrade ?? '—' }}
                </span>
            @endif
        </div>

        <div>
            <h2 class="font-bold text-16px text-primary mb-2">نص الواجب والمطلوب</h2>
            <p class="font-medium text-15px text-gray leading-relaxed">{{ $assignmentDescription ?? '' }}</p>
        </div>

        <div>
            <h2 class="font-bold text-16px text-primary mb-2">إجابة الطالب</h2>
            @forelse ($answerParagraphs as $paragraph)
                <p class="font-medium text-15px text-black leading-relaxed mb-2">{{ $paragraph }}</p>
            @empty
                <p class="font-medium text-14px text-gray">لم يُرفق نص إجابة.</p>
            @endforelse
            @if (!empty($attachmentUrl))
                <a href="{{ $attachmentUrl }}" target="_blank" rel="noopener"
                    class="inline-flex items-center gap-2 mt-3 font-semibold text-14px text-primary hover:underline">
                    <span class="icon-[tabler--paperclip] size-4"></span>
                    {{ $attachmentName ?: 'تحميل المرفق' }}
                </a>
            @endif
        </div>

        @if (!empty($canGrade))
            <form method="POST" action="{{ $gradeAction }}" class="border-t border-d9 pt-5 space-y-4">
                @csrf
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">
                        الدرجة (النجاح من {{ $passGrade ?? 0 }} / العظمى {{ $maxGrade ?? 50 }})
                    </label>
                    <input type="number" name="grade" min="0" max="{{ $maxGrade ?? 50 }}"
                        value="{{ old('grade', $historyGrade) }}" required
                        class="input input-bordered h-12 w-full max-w-xs rounded-10px border-d9 font-medium text-15px" />
                    @error('grade')
                        <p class="font-medium text-13px text-[#DC2626] mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit"
                    class="inline-flex items-center justify-center rounded-10px bg-primary px-6 h-11 font-semibold text-14px text-white hover:opacity-95 transition">
                    حفظ التقييم
                </button>
            </form>
        @endif
    </div>
</div>
@endsection
