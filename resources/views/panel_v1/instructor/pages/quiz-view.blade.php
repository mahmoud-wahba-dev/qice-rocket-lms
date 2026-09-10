@extends('panel_v1.instructor.layouts.course')

@section('content')
@php
    $slug = $slug ?? ($demoSlug ?? 'demo');
    $quiz = $quizView ?? [];
    $options = $quiz['options'] ?? [];
    $current = (int) ($quiz['current'] ?? 1);
    $total = (int) ($quiz['total'] ?? 1);
@endphp

<div class="pb-10 space-y-5">
    {{-- Quiz header --}}
    <div class="rounded-14px border border-d9 bg-white px-5 sm:px-6 py-5 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-4">
            <div class="flex items-start gap-3 min-w-0">
                <span class="size-11 rounded-12px bg-primary center shrink-0 mt-0.5">
                    <span class="icon-[tabler--check] size-6 text-white"></span>
                </span>
                <div class="min-w-0">
                    <h1 class="font-bold text-20px sm:text-22px text-primary leading-snug mb-1">
                        {{ $quiz['title'] ?? '' }}
                    </h1>
                    <p class="font-medium text-14px sm:text-15px text-gray leading-snug">
                        {{ $quiz['subtitle'] ?? '' }}
                    </p>
                </div>
            </div>
            <a href="{{ route('panel.v1.instructor.courses.performance', ['slug' => $slug]) }}"
                class="inline-flex items-center gap-1.5 font-semibold text-15px text-primary hover:opacity-80 transition shrink-0">
                العودة إلى الدورة
                <span class="icon-[tabler--arrow-narrow-left] size-4"></span>
            </a>
        </div>
        <p class="font-semibold text-14px text-gray">أسئلة {{ $quiz['questions_count'] ?? $total }}</p>
    </div>

    {{-- Question + options --}}
    <div class="rounded-14px border border-d9 bg-white px-5 sm:px-7 py-6 sm:py-8 shadow-sm">
        <div class="flex items-start justify-between gap-3 mb-6">
            <h2 class="font-bold text-20px sm:text-24px text-primary leading-snug">
                {{ $quiz['question'] ?? '' }}
            </h2>
            <span class="size-10 rounded-full bg-primary center shrink-0 font-bold text-15px text-white"
                aria-label="رقم السؤال {{ $current }}">
                {{ $current }}
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4" role="list">
            @foreach ($options as $option)
                @php
                    $isSelected = !empty($option['selected']);
                    $isCorrect = !empty($option['correct']);
                    $borderClass = $isSelected || $isCorrect
                        ? 'border-primary bg-primary/5'
                        : 'border-d9 bg-white hover:border-primary/30';
                @endphp
                <div class="relative rounded-14px border-2 px-4 sm:px-5 py-5 min-h-[5.5rem] {{ $borderClass }}"
                    role="listitem">
                    @if ($isSelected || $isCorrect)
                        <div class="absolute top-3 start-3 flex flex-wrap gap-1.5">
                            @if ($isCorrect)
                                <span class="inline-flex items-center gap-1 rounded-full bg-primary px-2.5 py-1 font-semibold text-11px text-white">
                                    <span class="icon-[tabler--check] size-3.5"></span>
                                    صحيح
                                </span>
                            @endif
                            @if ($isSelected)
                                <span class="inline-flex items-center gap-1 rounded-full bg-[#0FC787] px-2.5 py-1 font-semibold text-11px text-white">
                                    <span class="icon-[tabler--user] size-3.5"></span>
                                    إجابتك
                                </span>
                            @endif
                        </div>
                    @endif
                    <p class="font-semibold text-16px sm:text-18px text-primary leading-snug {{ ($isSelected || $isCorrect) ? 'pt-8' : '' }}">
                        {{ $option['text'] }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Footer actions --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <button type="button"
                class="size-11 rounded-full border border-d9 bg-white center text-primary hover:bg-fa transition disabled:opacity-40"
                aria-label="السابق" disabled>
                <span class="icon-[tabler--chevron-right] size-5"></span>
            </button>
            <button type="button"
                class="size-11 rounded-full border border-d9 bg-white center text-primary hover:bg-fa transition"
                aria-label="التالي">
                <span class="icon-[tabler--chevron-left] size-5"></span>
            </button>
        </div>
        <a href="{{ route('panel.v1.instructor.quizzes') }}"
            class="inline-flex items-center justify-center rounded-12px bg-primary h-12 px-6 font-bold text-15px text-white hover:opacity-95 transition">
            اختباراتي
        </a>
        <a href="{{ route('panel.v1.instructor.quizzes.edit', ['id' => $quizId ?? 0]) }}"
            class="inline-flex items-center justify-center rounded-12px border border-d9 bg-white h-12 px-6 font-bold text-15px text-primary hover:bg-fa transition">
            تعديل الاختبار
        </a>
    </div>

    @if (!empty($realQuestions))
        <div class="rounded-14px border border-d9 bg-white px-5 sm:px-7 py-6 shadow-sm">
            <h2 class="font-bold text-20px text-primary mb-6">أسئلة الاختبار الحقيقية ({{ count($realQuestions) }})</h2>
            <div class="space-y-5">
                @foreach ($realQuestions as $realQuestion)
                    <div class="border border-d9 rounded-12px px-5 py-4">
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <p class="font-bold text-16px text-primary">{{ $realQuestion['title'] }}</p>
                            <form method="POST"
                                action="{{ route('panel.v1.instructor.quizzes.questions.delete', ['id' => $quizId, 'questionId' => $realQuestion['id']]) }}"
                                onsubmit="return confirm('حذف السؤال؟');">
                                @csrf
                                <button type="submit" class="font-bold text-14px text-[#EF4444]">حذف</button>
                            </form>
                        </div>
                        <p class="font-medium text-13px text-gray mb-2">
                            {{ $realQuestion['type'] === 'descriptive' ? 'وصفي' : 'اختيار من متعدد' }} — {{ $realQuestion['grade'] }} درجة
                        </p>
                        @foreach ($realQuestion['options'] as $realOption)
                            <p class="font-medium text-14px {{ $realOption['correct'] ? 'text-[#00B31B] font-bold' : 'text-gray' }}">
                                {{ $realOption['correct'] ? '✓' : '•' }} {{ $realOption['text'] }}
                            </p>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-14px border border-d9 bg-white px-5 sm:px-7 py-6 shadow-sm">
            <h2 class="font-bold text-20px text-primary mb-6">إضافة سؤال</h2>
            <form method="POST" action="{{ route('panel.v1.instructor.quizzes.questions.store', ['id' => $quizId]) }}"
                class="space-y-5">
                @csrf
                <div class="relative">
                    <label class="absolute -top-2.5 start-4 z-[1] bg-white px-2 font-medium text-13px text-gray">نص السؤال *</label>
                    <input type="text" name="title"
                        class="input input-bordered w-full h-14 rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <select name="type"
                        class="select select-bordered h-14 rounded-10px border-d9 font-medium text-16px focus:outline-none focus:border-primary">
                        <option value="multiple">اختيار من متعدد</option>
                        <option value="descriptive">وصفي</option>
                    </select>
                    <input type="number" name="grade" value="10" min="1"
                        class="input input-bordered h-14 rounded-10px border-d9 font-medium text-16px focus:outline-none focus:border-primary"
                        placeholder="الدرجة">
                    <input type="number" name="correct_index" value="0" min="0"
                        class="input input-bordered h-14 rounded-10px border-d9 font-medium text-16px focus:outline-none focus:border-primary"
                        placeholder="رقم الإجابة الصحيحة (يبدأ من 0)">
                </div>
                <div class="space-y-3" id="quiz-options-list">
                    <input type="text" name="options[]"
                        class="input input-bordered w-full h-14 rounded-10px border-d9 font-medium text-16px focus:outline-none focus:border-primary"
                        placeholder="الاختيار 1">
                    <input type="text" name="options[]"
                        class="input input-bordered w-full h-14 rounded-10px border-d9 font-medium text-16px focus:outline-none focus:border-primary"
                        placeholder="الاختيار 2">
                    <input type="text" name="options[]"
                        class="input input-bordered w-full h-14 rounded-10px border-d9 font-medium text-16px focus:outline-none focus:border-primary"
                        placeholder="الاختيار 3 (اختياري)">
                    <input type="text" name="options[]"
                        class="input input-bordered w-full h-14 rounded-10px border-d9 font-medium text-16px focus:outline-none focus:border-primary"
                        placeholder="الاختيار 4 (اختياري)">
                </div>
                <button type="submit" class="btn btn-primary rounded-10px h-12 px-8 font-bold text-16px">إضافة السؤال</button>
            </form>
        </div>

        <div class="rounded-14px border border-d9 bg-white px-5 sm:px-7 py-6 shadow-sm">
            <h2 class="font-bold text-20px text-primary mb-6">نتائج بانتظار التصحيح</h2>
            @forelse ($waitingResults ?? [] as $waiting)
                <div class="flex items-center justify-between gap-3 border-b border-d9 last:border-0 py-4">
                    <div>
                        <p class="font-bold text-16px text-primary">{{ $waiting->user->full_name ?? '' }}</p>
                        <p class="font-medium text-13px text-gray">{{ date('Y/m/d', (int) $waiting->created_at) }}</p>
                    </div>
                    <a href="{{ route('panel.v1.instructor.quiz-results.grade', ['resultId' => $waiting->id]) }}"
                        class="btn btn-outline rounded-10px h-11 px-5 font-bold text-14px text-primary">
                        تصحيح
                    </a>
                </div>
            @empty
                <p class="font-medium text-15px text-gray">لا توجد نتائج بانتظار التصحيح.</p>
            @endforelse
        </div>
    @endif
</div>
@endsection
