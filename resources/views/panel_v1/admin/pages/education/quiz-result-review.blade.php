@extends('panel_v1.admin.layouts.app')

@section('content')
@php
    $answers = $userAnswers ?? [];
    $statusLabel = ($quizResult->status ?? '') === \App\Models\QuizzesResult::$passed ? 'ناجح' : ((($quizResult->status ?? '') === \App\Models\QuizzesResult::$failed) ? 'راسب' : 'بانتظار المراجعة');
    $statusClass = ($quizResult->status ?? '') === \App\Models\QuizzesResult::$passed ? 'bg-[#D1FAE5] text-[#059669]' : ((($quizResult->status ?? '') === \App\Models\QuizzesResult::$failed) ? 'bg-[#FEE2E2] text-[#DC2626]' : 'bg-[#FEF3C7] text-[#D97706]');
@endphp
<div class="space-y-6 pb-8 max-w-3xl mx-auto">
    @include('panel_v1.admin.components.page-header', [
        'title' => 'مراجعة النتيجة',
        'subtitle' => ($quiz->title ?? 'اختبار').' — '.($quizResult->user->full_name ?? ''),
    ])

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="border border-d9 rounded-14px bg-white p-4 text-center">
            <p class="font-bold text-20px text-primary leading-none mb-1">{{ $quizResult->user_grade ?? 0 }}/{{ $questionsSumGrade ?? 0 }}</p>
            <p class="font-semibold text-12px text-gray">الدرجة</p>
        </div>
        <div class="border border-d9 rounded-14px bg-white p-4 text-center">
            <p class="font-bold text-14px leading-none mb-1"><span class="inline-flex rounded-full px-2.5 py-1 font-semibold text-11px {{ $statusClass }}">{{ $statusLabel }}</span></p>
            <p class="font-semibold text-12px text-gray mt-1">الحالة</p>
        </div>
        <div class="border border-d9 rounded-14px bg-white p-4 text-center">
            <p class="font-bold text-20px text-primary leading-none mb-1">{{ $numberOfAttempt ?? 1 }}</p>
            <p class="font-semibold text-12px text-gray">المحاولات</p>
        </div>
        <div class="border border-d9 rounded-14px bg-white p-4 text-center">
            <p class="font-bold text-14px text-primary leading-none mb-1">{{ !empty($quizResult->created_at) ? date('Y/m/d', (int)$quizResult->created_at) : '—' }}</p>
            <p class="font-semibold text-12px text-gray mt-1">التاريخ</p>
        </div>
    </div>

    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <form method="POST" action="{{ $formAction }}" class="space-y-5">
            @csrf
            @forelse($quizQuestions ?? [] as $index => $question)
                @php
                    $ua = $answers[$question->id] ?? [];
                    $userGrade = $ua['grade'] ?? 0;
                    $isDescriptive = $question->type === \App\Models\QuizzesQuestion::$descriptive;
                @endphp
                <div class="border border-d9 rounded-12px p-4 bg-[#FAFAF4]">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                        <p class="font-bold text-14px text-primary">{{ $index + 1 }}. {{ $question->title }}</p>
                        <span class="inline-flex rounded-full px-2.5 py-1 font-semibold text-11px bg-[#EFF6FF] text-[#2563EB]">{{ $question->type }} — {{ $userGrade }}/{{ $question->grade }}</span>
                    </div>
                    @if($isDescriptive)
                        <label class="font-semibold text-13px text-primary mb-2 block">إجابة الطالب</label>
                        <textarea rows="3" disabled class="textarea textarea-bordered w-full rounded-12px border-d9 text-14px p-3 bg-white">{{ $ua['answer'] ?? '' }}</textarea>
                        <div class="mt-3">
                            <label class="font-semibold text-13px text-primary mb-2 block">الدرجة المستحقة (من {{ $question->grade }})</label>
                            <input type="number" name="question[{{ $question->id }}][grade]" value="{{ $userGrade }}" min="0" max="{{ $question->grade }}" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px bg-white">
                        </div>
                    @else
                        <div class="space-y-2">
                            @foreach($question->quizzesQuestionsAnswers ?? [] as $answer)
                                @php $isUser = isset($ua['answer']) && (int)$ua['answer'] === (int)$answer->id; @endphp
                                <div class="flex items-center gap-2 rounded-10px border px-3 py-2 text-13px font-medium {{ $answer->correct ? 'border-[#059669] bg-[#D1FAE5]/40 text-primary' : ($isUser ? 'border-red-300 bg-[#FEE2E2]/50 text-red-600' : 'border-d9 bg-white text-gray') }}">
                                    <span class="size-2 rounded-full shrink-0 {{ $answer->correct ? 'bg-[#059669]' : ($isUser ? 'bg-red-500' : 'bg-gray-300') }}"></span>
                                    <span class="flex-1">{{ $answer->title }}</span>
                                    @if($answer->correct)<span class="font-bold text-11px text-[#059669]">صحيحة</span>@endif
                                    @if($isUser && !$answer->correct)<span class="font-bold text-11px text-red-500">إجابة الطالب</span>@endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <p class="font-medium text-14px text-gray text-center py-8">لا توجد أسئلة لهذا الاختبار.</p>
            @endforelse

            <div class="flex items-center gap-3 pt-4 border-t border-d9">
                <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px hover:opacity-95 transition">حفظ التصحيح</button>
                <a href="{{ route('panel.v1.admin.education.quiz-results', ['quizId' => $quiz->id ?? 0]) }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition">عودة للنتائج</a>
            </div>
        </form>
    </div>
</div>
@endsection
