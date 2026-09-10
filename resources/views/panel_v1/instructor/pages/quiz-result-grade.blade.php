@extends('panel_v1.instructor.layouts.app')

@section('content')
<section class="pt-10 pb-16">
    <div class="container max-w-3xl">
        <div class="mb-14">
            <h1 class="font-extrabold text-36px text-primary mb-3">تصحيح نتيجة اختبار</h1>
            <p class="font-semibold text-20px text-gray">
                {{ $quizResult->user->full_name ?? '' }} — {{ $quizResult->quiz->title ?? '' }}
            </p>
        </div>

        @php
            $decoded = json_decode($quizResult->results ?? '[]', true) ?: [];
        @endphp

        <div class="border border-d9 rounded-16px bg-white px-8 py-8 mb-8 space-y-6">
            @forelse ($decoded as $questionId => $entry)
                @php
                    $question = \App\Models\QuizzesQuestion::find($questionId);
                @endphp
                <div class="border-b border-d9 last:border-0 pb-6 last:pb-0">
                    <p class="font-bold text-18px text-primary mb-2">{{ $question->title ?? ('سؤال #' . $questionId) }}</p>
                    @if (!empty($entry['answer']))
                        @php
                            $answer = \App\Models\QuizzesQuestionsAnswer::find($entry['answer']);
                        @endphp
                        <p class="font-medium text-15px text-black">إجابة الطالب: {{ $answer->title ?? '—' }}</p>
                        <p class="font-medium text-14px {{ !empty($entry['status']) ? 'text-[#00B31B]' : 'text-[#EF4444]' }} mt-1">
                            {{ !empty($entry['status']) ? 'صحيحة' : 'خاطئة' }} ({{ $entry['grade'] ?? 0 }} درجة)
                        </p>
                    @else
                        <p class="font-medium text-15px text-black">إجابة الطالب (وصفي): {{ $entry['text'] ?? '—' }}</p>
                    @endif
                </div>
            @empty
                <p class="font-medium text-16px text-gray">لا توجد إجابات مسجلة.</p>
            @endforelse
        </div>

        <form method="POST" action="{{ route('panel.v1.instructor.quiz-results.grade.store', ['resultId' => $quizResult->id]) }}"
            class="border border-d9 rounded-16px bg-white px-8 py-8 space-y-6">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="relative">
                    <label class="absolute -top-2.5 start-4 z-[1] bg-white px-2 font-medium text-13px text-gray">الدرجة *</label>
                    <input type="number" name="user_grade" value="{{ old('user_grade', $quizResult->user_grade ?? 0) }}" min="0"
                        class="input input-bordered w-full h-14 rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary">
                </div>
                <div class="relative">
                    <label class="absolute -top-2.5 start-4 z-[1] bg-white px-2 font-medium text-13px text-gray">الحالة *</label>
                    <select name="status"
                        class="select select-bordered w-full h-14 rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary">
                        @foreach (['passed' => 'ناجح', 'failed' => 'راسب', 'waiting' => 'بانتظار التصحيح'] as $value => $label)
                            <option value="{{ $value }}" {{ old('status', $quizResult->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary rounded-10px h-14 px-10 font-bold text-18px">اعتماد النتيجة</button>
        </form>
    </div>
</section>
@endsection
