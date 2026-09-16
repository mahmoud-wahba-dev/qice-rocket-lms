@extends('panel_v1.instructor.layouts.app')

@section('content')
@php
    $quizId = $quizId ?? 0;
    $questions = $realQuestions ?? [];
    $meta = $quizMeta ?? [];
    $questionCount = count($questions);
    $waitingCount = count($waitingResults ?? []);
    $hasQuestions = $questionCount > 0;
@endphp

<div class="space-y-6 pb-10">
    @component('panel_v1.instructor.components.page-header', [
        'title' => $quizTitle ?? 'إدارة الاختبار',
        'subtitle' => ($quizView['subtitle'] ?? '') ?: 'كل سؤال يُحفظ فور الضغط على «حفظ السؤال» — لا حاجة لزر حفظ عام',
    ])
        @slot('actions')
            <a href="{{ route('panel.v1.instructor.quizzes') }}"
                class="inline-flex items-center gap-2 rounded-12px border border-d9 px-5 h-12 font-semibold text-15px text-primary bg-white hover:bg-fa transition">
                كل الاختبارات
            </a>
            <a href="{{ route('panel.v1.instructor.quizzes.edit', ['id' => $quizId]) }}"
                class="inline-flex items-center gap-2 rounded-12px border border-color2 px-5 h-12 font-semibold text-15px text-color2 bg-white hover:opacity-90 transition">
                <span class="icon-[tabler--settings] size-5"></span>
                تعديل الإعدادات
            </a>
            <a href="#add-question"
                class="inline-flex items-center gap-2 rounded-12px bg-color2 px-5 h-12 font-semibold text-15px text-white hover:opacity-95 transition">
                <span class="icon-[tabler--plus] size-5"></span>
                إضافة سؤال
            </a>
        @endslot
    @endcomponent

    @if ($hasQuestions)
        <div class="rounded-14px border border-[#A7F3D0] bg-[#ECFDF5] px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-start gap-3 min-w-0">
                <span class="size-10 rounded-full bg-[#0FC787] center shrink-0">
                    <span class="icon-[tabler--check] size-5 text-white"></span>
                </span>
                <div class="min-w-0">
                    <p class="font-bold text-16px text-primary mb-0.5">الأسئلة محفوظة في هذا الاختبار ({{ $questionCount }})</p>
                    <p class="font-medium text-13px text-gray">
                        لا تحتاج زر حفظ إضافي — كل سؤال يُربط بالاختبار تلقائياً عند الحفظ.
                        @if ($waitingCount > 0)
                            لديك {{ $waitingCount }} نتيجة بانتظار التصحيح.
                        @else
                            عند تقديم الطلاب للاختبار ستظهر نتائجهم في الخطوة 3.
                        @endif
                    </p>
                </div>
            </div>
            <a href="#waiting-results"
                class="inline-flex items-center justify-center gap-2 rounded-12px bg-primary h-11 px-5 font-bold text-14px text-white hover:opacity-95 transition shrink-0">
                الانتقال لمراجعة النتائج
                <span class="icon-[tabler--arrow-narrow-left] size-4"></span>
            </a>
        </div>
    @endif

    {{-- Clickable steps --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <a href="{{ route('panel.v1.instructor.quizzes.edit', ['id' => $quizId]) }}"
            class="rounded-14px border border-d9 bg-white px-4 py-4 flex items-start gap-3 hover:border-primary/40 transition">
            <span class="size-9 rounded-full bg-[#0FC787] center shrink-0 font-bold text-14px text-white">1</span>
            <div>
                <p class="font-bold text-15px text-primary mb-0.5">إعدادات الاختبار ✓</p>
                <p class="font-medium text-13px text-gray">اضغط للتعديل (عنوان، نجاح، مدة)</p>
            </div>
        </a>
        <a href="#add-question"
            class="rounded-14px border {{ $hasQuestions ? 'border-d9 bg-white' : 'border-primary bg-[#FAF8F4]' }} px-4 py-4 flex items-start gap-3 hover:border-primary/40 transition">
            <span class="size-9 rounded-full {{ $hasQuestions ? 'bg-[#0FC787]' : 'bg-primary' }} center shrink-0 font-bold text-14px text-white">
                {{ $hasQuestions ? '✓' : '2' }}
            </span>
            <div>
                <p class="font-bold text-15px text-primary mb-0.5">إضافة الأسئلة {{ $hasQuestions ? '✓' : '' }}</p>
                <p class="font-medium text-13px text-gray">احفظ كل سؤال بزر «حفظ السؤال» أسفل النموذج</p>
            </div>
        </a>
        <a href="#waiting-results"
            class="rounded-14px border {{ $hasQuestions ? 'border-primary bg-[#FAF8F4]' : 'border-d9 bg-white' }} px-4 py-4 flex items-start gap-3 hover:border-primary/40 transition">
            <span class="size-9 rounded-full {{ $hasQuestions ? 'bg-primary' : 'bg-[#E8E8E8]' }} center shrink-0 font-bold text-14px {{ $hasQuestions ? 'text-white' : 'text-gray' }}">3</span>
            <div>
                <p class="font-bold text-15px text-primary mb-0.5">مراجعة النتائج</p>
                <p class="font-medium text-13px text-gray">اضغط هنا للانتقال لقسم تصحيح إجابات الطلاب</p>
            </div>
        </a>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="rounded-14px bg-white border border-d9 px-4 py-4" style="border-inline-start:4px solid #0f4c45">
            <p class="font-medium text-13px text-gray mb-1">عدد الأسئلة</p>
            <p class="font-semibold text-24px text-primary">{{ $questionCount }}</p>
        </div>
        <div class="rounded-14px bg-white border border-d9 px-4 py-4" style="border-inline-start:4px solid #0FC787">
            <p class="font-medium text-13px text-gray mb-1">الدرجة الكاملة</p>
            <p class="font-semibold text-24px text-[#0FC787]">{{ (int) ($meta['total_mark'] ?? 0) }}</p>
        </div>
        <div class="rounded-14px bg-white border border-d9 px-4 py-4" style="border-inline-start:4px solid #F59E0B">
            <p class="font-medium text-13px text-gray mb-1">درجة النجاح</p>
            <p class="font-semibold text-24px text-[#D97706]">{{ (int) ($meta['pass_mark'] ?? 0) }}</p>
        </div>
        <div class="rounded-14px bg-white border border-d9 px-4 py-4" style="border-inline-start:4px solid #6366F1">
            <p class="font-medium text-13px text-gray mb-1">المدة / المحاولات</p>
            <p class="font-semibold text-18px text-[#6366F1]">
                {{ !empty($meta['time']) ? ($meta['time'] . ' د') : 'مفتوح' }}
                ·
                {{ !empty($meta['attempt']) ? ($meta['attempt'] . ' محاولة') : '—' }}
            </p>
        </div>
    </div>

    {{-- Existing questions --}}
    <div id="quiz-questions" class="rounded-20px border border-d9 bg-white px-5 sm:px-7 py-6 shadow-sm scroll-mt-24">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
            <h2 class="font-bold text-20px text-primary">أسئلة الاختبار ({{ $questionCount }})</h2>
            <div class="flex flex-wrap items-center gap-3">
                @if ($hasQuestions)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-[#ECFDF5] px-3 py-1 font-semibold text-12px text-[#059669]">
                        <span class="icon-[tabler--database] size-3.5"></span>
                        محفوظة في قاعدة البيانات
                    </span>
                @endif
                <a href="#add-question" class="font-semibold text-14px text-primary hover:opacity-80">+ سؤال جديد</a>
            </div>
        </div>

        @forelse ($questions as $index => $question)
            <article class="border border-d9 rounded-14px px-4 sm:px-5 py-4 mb-4 last:mb-0">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="min-w-0">
                        <p class="font-medium text-13px text-gray mb-1">سؤال {{ $index + 1 }}</p>
                        <h3 class="font-bold text-16px sm:text-17px text-primary leading-snug">{{ $question['title'] }}</h3>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="inline-flex rounded-full bg-primary/10 px-3 py-1 font-semibold text-12px text-primary">
                            {{ $question['grade'] }} درجة
                        </span>
                        <span class="inline-flex rounded-full bg-[#F1F5F9] px-3 py-1 font-semibold text-12px text-gray">
                            {{ ($question['type'] ?? '') === 'descriptive' ? 'وصفي' : 'اختيار من متعدد' }}
                        </span>
                        <form method="POST"
                            action="{{ route('panel.v1.instructor.quizzes.questions.delete', ['id' => $quizId, 'questionId' => $question['id']]) }}"
                            onsubmit="return confirm('حذف هذا السؤال؟');">
                            @csrf
                            <button type="submit" class="size-9 rounded-full bg-[#FEF2F2] center text-[#EF4444] hover:opacity-80" aria-label="حذف">
                                <span class="icon-[tabler--trash] size-4"></span>
                            </button>
                        </form>
                    </div>
                </div>

                @if (($question['type'] ?? '') === 'descriptive')
                    <div class="rounded-12px bg-[#EFF6FF] border border-[#BFDBFE] px-4 py-3">
                        <p class="font-semibold text-12px text-[#1D4ED8] mb-1">الإجابة النموذجية</p>
                        <p class="font-medium text-14px text-primary">{{ $question['model_answer'] ?: '—' }}</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach ($question['options'] ?? [] as $option)
                            <div class="rounded-12px border px-4 py-3 flex items-center gap-3
                                {{ !empty($option['correct']) ? 'border-primary bg-primary/5' : 'border-d9 bg-[#F8FAFC]' }}">
                                @if (!empty($option['correct']))
                                    <span class="size-6 rounded-full bg-primary center shrink-0">
                                        <span class="icon-[tabler--check] size-3.5 text-white"></span>
                                    </span>
                                    <span class="font-bold text-14px text-primary flex-1">{{ $option['text'] }}</span>
                                    <span class="font-semibold text-11px text-primary">الصحيحة</span>
                                @else
                                    <span class="size-6 rounded-full border-2 border-d9 shrink-0"></span>
                                    <span class="font-medium text-14px text-gray flex-1">{{ $option['text'] }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </article>
        @empty
            <div class="rounded-14px border border-dashed border-d9 bg-[#F8FAFC] px-6 py-10 text-center">
                <span class="icon-[tabler--list-details] size-10 text-gray mx-auto mb-3 block"></span>
                <p class="font-bold text-16px text-primary mb-1">لا توجد أسئلة بعد</p>
                <p class="font-medium text-14px text-gray mb-4">ابدأ بإضافة أول سؤال من النموذج أدناه</p>
                <a href="#add-question" class="inline-flex items-center gap-2 rounded-12px bg-primary px-5 h-11 font-semibold text-14px text-white">
                    إضافة أول سؤال
                </a>
            </div>
        @endforelse
    </div>

    {{-- Add question form --}}
    <div id="add-question" class="rounded-20px border border-d9 bg-white px-5 sm:px-7 py-7 shadow-sm scroll-mt-24">
        <h2 class="font-bold text-20px sm:text-22px text-primary mb-2">إضافة سؤال جديد</h2>
        <p class="font-medium text-14px text-gray mb-6">
            للاختيار من متعدد: اكتب 2–4 خيارات ثم انقر على ○ بجانب الإجابة الصحيحة.
        </p>

        @if ($errors->any())
            <div class="rounded-12px bg-[#FEF2F2] border border-[#FECACA] px-4 py-3 mb-5">
                <ul class="list-disc list-inside space-y-1 font-medium text-14px text-[#DC2626]">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('panel.v1.instructor.quizzes.questions.store', ['id' => $quizId]) }}"
            class="space-y-5" data-question-form>
            @csrf

            <div>
                <label class="block font-semibold text-14px text-primary mb-2">نص السؤال <span class="text-[#E11D48]">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required maxlength="1000"
                    placeholder="مثال: ما هو الهدف من معايير الجودة؟"
                    class="input input-bordered w-full h-12 rounded-10px border-d9 font-medium text-15px text-primary">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-14px text-primary mb-2">نوع السؤال</label>
                    <div class="grid grid-cols-2 gap-2" data-question-type-toggle>
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="multiple" class="peer sr-only" @checked(old('type', 'multiple') === 'multiple')>
                            <span class="flex items-center justify-center gap-2 h-12 rounded-10px border border-d9 font-semibold text-14px text-gray peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:text-primary">
                                اختيار من متعدد
                            </span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="descriptive" class="peer sr-only" @checked(old('type') === 'descriptive')>
                            <span class="flex items-center justify-center gap-2 h-12 rounded-10px border border-d9 font-semibold text-14px text-gray peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:text-primary">
                                سؤال وصفي
                            </span>
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block font-semibold text-14px text-primary mb-2">درجة السؤال <span class="text-[#E11D48]">*</span></label>
                    <input type="number" name="grade" value="{{ old('grade', 10) }}" min="1" max="1000" required
                        class="input input-bordered w-full h-12 rounded-10px border-d9 font-medium text-15px text-primary">
                </div>
            </div>

            <div data-multiple-fields class="space-y-3">
                <div class="flex items-center justify-between gap-3">
                    <p class="font-semibold text-14px text-primary">الخيارات — اختر الصحيحة بالنقر على ○</p>
                    <p class="font-medium text-12px text-gray">خياران على الأقل</p>
                </div>

                @foreach (range(0, 3) as $i)
                    <label class="flex items-center gap-3 rounded-12px border border-d9 bg-[#F8FAFC] px-3 py-2.5 cursor-pointer hover:border-primary/40 transition has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                        <input type="radio" name="correct_option" value="{{ $i }}"
                            class="radio radio-primary size-5 shrink-0"
                            @checked((string) old('correct_option', '0') === (string) $i)
                            {{ $i > 1 ? '' : 'required' }}>
                        <span class="font-semibold text-13px text-primary shrink-0 w-16">خيار {{ $i + 1 }}</span>
                        <input type="text" name="options[]" value="{{ old('options.' . $i) }}"
                            placeholder="{{ $i < 2 ? 'اكتب نص الخيار *' : 'اختياري' }}"
                            class="input input-bordered border-0 bg-transparent w-full h-10 font-medium text-15px text-primary focus:outline-none"
                            {{ $i < 2 ? 'required' : '' }}>
                    </label>
                @endforeach
                <p class="font-medium text-12px text-[#1D4ED8]">
                    تلميح: بعد كتابة الخيارات، انقر الدائرة ○ بجانب الإجابة الصحيحة قبل الحفظ.
                </p>
            </div>

            <div data-descriptive-fields class="hidden space-y-3">
                <div>
                    <label class="block font-semibold text-14px text-primary mb-2">الإجابة النموذجية (اختياري)</label>
                    <textarea name="correct" rows="4"
                        class="textarea textarea-bordered w-full rounded-10px border-d9 font-medium text-15px text-primary min-h-28"
                        placeholder="اكتب نموذجاً يساعد المدرب عند التصحيح اليدوي...">{{ old('correct') }}</textarea>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2">
                <p class="font-medium text-13px text-gray">
                    بعد الحفظ يظهر السؤال فوراً في القائمة أعلاه ويُربط بهذا الاختبار.
                </p>
                <div class="flex flex-wrap items-center gap-3">
                    <button type="reset" class="btn btn-ghost rounded-12px h-12 px-6 font-semibold text-15px text-gray">مسح</button>
                    <button type="submit" class="btn btn-primary rounded-12px h-12 px-8 font-bold text-15px inline-flex items-center gap-2">
                        <span class="icon-[tabler--device-floppy] size-5"></span>
                        حفظ السؤال في الاختبار
                    </button>
                </div>
            </div>
        </form>
    </div>

    @if ($hasQuestions)
        <div class="flex justify-center">
            <a href="#waiting-results"
                class="inline-flex items-center gap-2 rounded-12px border-2 border-primary bg-white h-12 px-6 font-bold text-15px text-primary hover:bg-primary hover:text-white transition">
                انتهيت من الأسئلة — مراجعة النتائج
                <span class="icon-[tabler--arrow-narrow-left] size-5"></span>
            </a>
        </div>
    @endif

    <div id="waiting-results" class="rounded-20px border border-d9 bg-white px-5 sm:px-7 py-6 shadow-sm scroll-mt-24">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 mb-5">
            <div>
                <p class="font-semibold text-13px text-primary/70 mb-1">الخطوة 3</p>
                <h2 class="font-bold text-20px text-primary">مراجعة النتائج وتصحيح إجابات الطلاب</h2>
                <p class="font-medium text-14px text-gray mt-1">
                    الأسئلة أعلاه محفوظة مسبقاً. هنا فقط تصحيح محاولات الطلاب بعد تقديمهم للاختبار.
                </p>
            </div>
            <a href="{{ route('panel.v1.instructor.quizzes') }}"
                class="inline-flex items-center gap-2 rounded-12px bg-primary h-11 px-5 font-bold text-14px text-white hover:opacity-95 transition shrink-0">
                إنهاء والعودة للقائمة
            </a>
        </div>
        @forelse ($waitingResults ?? [] as $waiting)
            <div class="flex items-center justify-between gap-3 border-b border-d9 last:border-0 py-4">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="size-10 rounded-full bg-primary/10 center shrink-0 font-bold text-14px text-primary">
                        {{ mb_substr($waiting->user->full_name ?? 'ط', 0, 1) }}
                    </span>
                    <div class="min-w-0">
                        <p class="font-bold text-16px text-primary truncate">{{ $waiting->user->full_name ?? '' }}</p>
                        <p class="font-medium text-13px text-gray">{{ date('Y/m/d', (int) $waiting->created_at) }}</p>
                    </div>
                </div>
                <a href="{{ route('panel.v1.instructor.quiz-results.grade', ['resultId' => $waiting->id]) }}"
                    class="inline-flex items-center justify-center rounded-10px bg-primary h-11 px-5 font-bold text-14px text-white hover:opacity-95 transition">
                    تصحيح الآن
                </a>
            </div>
        @empty
            <p class="font-medium text-15px text-gray">لا توجد نتائج بانتظار التصحيح حالياً.</p>
        @endforelse
    </div>
</div>

<script>
(() => {
    const form = document.querySelector('[data-question-form]');
    if (!form) return;
    const multipleBox = form.querySelector('[data-multiple-fields]');
    const descriptiveBox = form.querySelector('[data-descriptive-fields]');
    const syncType = () => {
        const type = form.querySelector('input[name="type"]:checked')?.value || 'multiple';
        const isMultiple = type === 'multiple';
        multipleBox?.classList.toggle('hidden', !isMultiple);
        descriptiveBox?.classList.toggle('hidden', isMultiple);
        multipleBox?.querySelectorAll('input[name="options[]"]').forEach((input, idx) => {
            input.required = isMultiple && idx < 2;
        });
        multipleBox?.querySelectorAll('input[name="correct_option"]').forEach((input, idx) => {
            input.required = isMultiple && idx === 0;
        });
    };
    form.querySelectorAll('input[name="type"]').forEach((el) => el.addEventListener('change', syncType));
    syncType();
})();
</script>
@endsection
