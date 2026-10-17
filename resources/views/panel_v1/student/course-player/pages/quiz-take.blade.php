@extends('panel_v1.student.course-player.layouts.app')

@section('content')
@php
    $courseSlug       = $slug ?? 'demo';
    $take             = $quizTake ?? [];
    $options          = $take['options'] ?? [];
    $timeLimitSeconds = (int) ($take['time_limit_seconds'] ?? 0);
    $deadlineTs       = (int) ($take['deadline_ts'] ?? 0);
    $hasTimer         = $timeLimitSeconds > 0 && $deadlineTs > 0;
    $answerFormId     = 'quiz-answer-form';
@endphp

<div class="flex flex-col gap-6 pb-10">

    {{-- ── Header row: title + progress + timer ── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <span class="size-11 rounded-12px bg-primary/10 center shrink-0">
                <span class="icon-[tabler--clipboard-list] size-6 text-primary"></span>
            </span>
            <h1 class="font-bold text-20px sm:text-24px text-primary truncate">{{ $take['quiz_title'] ?? '' }}</h1>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            {{-- Progress pill --}}
            <span class="inline-flex items-center gap-1.5 rounded-full border border-d9 bg-white px-3.5 py-1.5 font-semibold text-13px text-primary">
                <span class="icon-[tabler--list-numbers] size-4 text-gray"></span>
                السؤال {{ $take['current'] ?? 1 }} من {{ $take['total'] ?? 1 }}
            </span>

            {{-- Timer widget (only shown when quiz has a time limit) --}}
            @if ($hasTimer)
            <div id="quiz-timer"
                 data-deadline="{{ $deadlineTs }}"
                 data-form="{{ $answerFormId }}"
                 class="inline-flex items-center gap-2 rounded-full border border-d9 bg-white px-3.5 py-1.5 font-bold text-14px text-primary transition-colors duration-300">
                <span class="icon-[tabler--clock] size-4 shrink-0" id="timer-icon"></span>
                <span id="timer-display" class="font-mono tabular-nums tracking-wide">--:--</span>
            </div>
            @endif
        </div>
    </div>

    {{-- ── Progress bar ── --}}
    @php $pct = round((($take['current'] ?? 1) / max(1, $take['total'] ?? 1)) * 100); @endphp
    <div class="h-1.5 w-full rounded-full bg-d9 overflow-hidden">
        <div class="h-full rounded-full bg-primary transition-all duration-500" style="width: {{ $pct }}%"></div>
    </div>

    {{-- ── Question form ── --}}
    <form id="{{ $answerFormId }}" method="POST"
          action="{{ route('panel.v1.student.course.quiz.answer', ['slug' => $courseSlug, 'quiz' => $take['quiz_id'] ?? null]) }}">
        @csrf
        <input type="hidden" name="quiz_id"     value="{{ $take['quiz_id'] ?? '' }}">
        <input type="hidden" name="question_id" value="{{ $take['question_id'] ?? '' }}">
        <input type="hidden" name="q"           value="{{ $take['current'] ?? 1 }}">

        <div class="border border-d9 rounded-20px bg-white px-5 sm:px-8 py-8">
            <h2 class="font-extrabold text-22px sm:text-28px text-primary mb-2 leading-snug">
                {{ $take['question_title'] ?? '' }}
            </h2>
            <p class="font-medium text-15px text-gray mb-8">{{ $take['instruction'] ?? '' }}</p>

            @if (!empty($take['is_descriptive']))
                <textarea name="answer_text" rows="6"
                    class="textarea textarea-bordered w-full rounded-14px border-d9 font-medium text-16px focus:outline-none focus:border-primary min-h-40"
                    placeholder="اكتب إجابتك هنا...">{{ old('answer_text', $take['saved_text'] ?? '') }}</textarea>
            @else
                <div class="space-y-4" role="radiogroup" aria-label="خيارات الإجابة">
                    @foreach ($options as $option)
                        <label class="flex items-center gap-4 rounded-14px border px-4 sm:px-5 py-4 cursor-pointer transition
                               {{ (string) old('quiz_option', $take['saved_option'] ?? '') === (string) $option['id']
                                   ? 'border-primary bg-primary/5'
                                   : 'border-d9 bg-white hover:border-primary/40' }}">
                            <input type="radio" name="quiz_option" value="{{ $option['id'] }}"
                                class="radio radio-primary"
                                {{ (string) old('quiz_option', $take['saved_option'] ?? '') === (string) $option['id'] ? 'checked' : '' }}>
                            <span class="font-medium text-15px sm:text-16px text-black leading-snug">{{ $option['text'] }}</span>
                        </label>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="flex flex-col-reverse mt-6 sm:flex-row items-stretch sm:items-center justify-between gap-3">
            @if (($take['current'] ?? 1) > 1)
                <a href="{{ route('panel.v1.student.course.quiz.take', ['slug' => $courseSlug, 'quiz' => $take['quiz_id'] ?? null, 'q' => ($take['current'] ?? 1) - 1]) }}"
                    class="btn btn-ghost rounded-12px h-12 px-6 font-semibold text-15px text-gray border border-d9">
                    السابق
                </a>
            @else
                <button type="button" disabled
                    class="btn btn-ghost rounded-12px h-12 px-6 font-semibold text-15px text-gray border border-d9 opacity-40 cursor-not-allowed">
                    السابق
                </button>
            @endif

            <button type="submit" class="btn btn-primary rounded-12px h-12 px-6 font-bold text-15px gap-2">
                {{ !empty($take['is_last']) ? 'إنهاء الاختبار' : 'السؤال التالي' }}
                <span class="icon-[tabler--arrow-left] size-5"></span>
            </button>
        </div>
    </form>
</div>

{{-- ── Timeout overlay (hidden by default, shown when time expires) ── --}}
@if ($hasTimer)
<div id="quiz-timeout-overlay"
     class="fixed inset-0 z-[999] bg-black/60 backdrop-blur-sm flex items-center justify-center hidden"
     aria-live="assertive">
    <div class="bg-white rounded-24px shadow-2xl p-8 sm:p-12 max-w-sm w-full mx-4 text-center">
        <div class="size-16 rounded-full bg-[#FEE2E2] center mx-auto mb-6">
            <span class="icon-[tabler--clock-off] size-8 text-[#DC2626]"></span>
        </div>
        <h2 class="font-extrabold text-24px text-primary mb-3">انتهى الوقت!</h2>
        <p class="font-medium text-15px text-gray mb-8 leading-relaxed">
            انتهى وقت الاختبار — سيتم إرسال إجاباتك تلقائياً الآن.
        </p>
        <div class="flex items-center justify-center gap-2 text-primary font-bold text-15px">
            <span class="loading loading-spinner size-5"></span>
            جارٍ الإرسال...
        </div>
    </div>
</div>
@endif

@push('scripts')
@if ($hasTimer)
<script>
(function () {
    'use strict';

    var DEADLINE   = {{ $deadlineTs }};           // unix timestamp (server)
    var FORM_ID    = '{{ $answerFormId }}';
    var display    = document.getElementById('timer-display');
    var timerWrap  = document.getElementById('quiz-timer');
    var overlay    = document.getElementById('quiz-timeout-overlay');
    var submitted  = false;

    if (!display || !timerWrap) return;

    // ── Warn student before leaving mid-quiz ──
    window.addEventListener('beforeunload', function (e) {
        if (!submitted) {
            e.preventDefault();
            e.returnValue = 'سيتم فقدان تقدمك في الاختبار إذا غادرت الصفحة. هل أنت متأكد؟';
            return e.returnValue;
        }
    });

    // ── Remove warning when the answer form is submitted normally ──
    var form = document.getElementById(FORM_ID);
    if (form) {
        form.addEventListener('submit', function () {
            submitted = true;
        });
    }

    function autoSubmit() {
        if (submitted) return;
        submitted = true;

        // Show overlay
        if (overlay) overlay.classList.remove('hidden');

        // Submit after a short delay so the overlay is visible
        setTimeout(function () {
            if (form) {
                form.submit();
            }
        }, 1800);
    }

    function tick() {
        var now       = Math.floor(Date.now() / 1000);
        var remaining = DEADLINE - now;

        if (remaining <= 0) {
            display.textContent = '00:00';
            timerWrap.classList.remove('border-d9', 'text-primary');
            timerWrap.classList.add('border-[#DC2626]', 'bg-[#FEE2E2]', 'text-[#DC2626]');
            autoSubmit();
            return;
        }

        var mins = Math.floor(remaining / 60);
        var secs = remaining % 60;
        display.textContent = String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');

        // ── Colour states ──
        if (remaining <= 60) {
            // Under 1 min — red urgent
            timerWrap.classList.remove('border-d9', 'text-primary', 'border-[#D97706]', 'bg-[#FEF3C7]', 'text-[#D97706]');
            timerWrap.classList.add('border-[#DC2626]', 'bg-[#FEE2E2]', 'text-[#DC2626]');
        } else if (remaining <= 300) {
            // Under 5 min — amber warning
            timerWrap.classList.remove('border-d9', 'text-primary', 'border-[#DC2626]', 'bg-[#FEE2E2]', 'text-[#DC2626]');
            timerWrap.classList.add('border-[#D97706]', 'bg-[#FEF3C7]', 'text-[#D97706]');
        }

        setTimeout(tick, 1000);
    }

    tick();
})();
</script>
@endif
@endpush
@endsection
