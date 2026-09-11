@extends('panel_v1.instructor.layouts.course')

@section('content')
@php
    $courseSlug = $slug ?? 'demo';
    $lessonTitle = $lesson['title'] ?? 'اسم المحاضرة هنا';
    $quizCard = $lectureQuiz ?? [];
    $assignmentCard = $lectureAssignment ?? [];
    $fileRows = $files ?? [];
@endphp

<div class="flex flex-col gap-6 sm:gap-8 pb-10">
    <h1 class="font-bold text-22px sm:text-26px lg:text-28px text-primary leading-snug text-start">
        - {{ $lessonTitle }}
    </h1>

    {{-- Video player --}}
    <div class="relative w-full overflow-hidden rounded-16px bg-[#4a4a4a] aspect-video shadow-sm">
        <div class="absolute inset-0 flex flex-col items-center justify-center text-white/90 pointer-events-none">
            <p class="absolute top-4 start-4 font-medium text-13px sm:text-14px text-white/80">
                This is where the title can go for your video.
            </p>
            <span class="size-16 sm:size-20 rounded-full bg-white/20 center backdrop-blur-sm">
                <span class="icon-[tabler--player-play-filled] size-8 sm:size-10 text-white ms-1"></span>
            </span>
        </div>
        <div class="absolute inset-x-0 bottom-0 h-11 bg-gradient-to-t from-black/75 to-transparent flex items-center px-3 sm:px-4 gap-2.5 sm:gap-3">
            <span class="icon-[tabler--player-play] size-4 text-white shrink-0"></span>
            <span class="icon-[tabler--player-skip-forward] size-4 text-white shrink-0 opacity-80"></span>
            <span class="icon-[tabler--volume] size-4 text-white shrink-0"></span>
            <span class="font-medium text-11px text-white/90 shrink-0">0:01 / 1:00</span>
            <div class="flex-1 h-1 rounded-full bg-white/30 overflow-hidden min-w-0">
                <div class="h-full w-[8%] bg-[#EF4444] rounded-full"></div>
            </div>
            <span class="inline-flex items-center">
                <input type="checkbox" class="switch switch-sm switch-primary" aria-label="تشغيل تلقائي" checked>
            </span>
            <span class="icon-[tabler--badge-cc] size-4 text-white shrink-0 opacity-80"></span>
            <span class="icon-[tabler--settings] size-4 text-white shrink-0 opacity-80"></span>
            <span class="icon-[tabler--maximize] size-4 text-white shrink-0"></span>
        </div>
    </div>

    {{-- Lesson tabs --}}
    <nav class="tabs tabs-bordered flex w-full overflow-x-auto border-b border-d9" aria-label="محتوى المحاضرة" role="tablist">
        <button type="button"
            class="tab active justify-center whitespace-nowrap font-semibold text-15px sm:text-17px text-gray pb-4 active-tab:text-primary active-tab:border-b-primary"
            id="inst-lesson-tab-1" data-v1-tab="#inst-lesson-1" role="tab" aria-selected="true">
            اختبار المحاضرة
        </button>
        <button type="button"
            class="tab justify-center whitespace-nowrap font-semibold text-15px sm:text-17px text-gray pb-4 active-tab:text-primary active-tab:border-b-primary"
            id="inst-lesson-tab-2" data-v1-tab="#inst-lesson-2" role="tab" aria-selected="false">
            تكليفات المحاضرة
        </button>
        <button type="button"
            class="tab justify-center whitespace-nowrap font-semibold text-15px sm:text-17px text-gray pb-4 active-tab:text-primary active-tab:border-b-primary"
            id="inst-lesson-tab-3" data-v1-tab="#inst-lesson-3" role="tab" aria-selected="false">
            التعليقات
        </button>
        <button type="button"
            class="tab justify-center whitespace-nowrap font-semibold text-15px sm:text-17px text-gray pb-4 active-tab:text-primary active-tab:border-b-primary"
            id="inst-lesson-tab-4" data-v1-tab="#inst-lesson-4" role="tab" aria-selected="false">
            الملفات
        </button>
    </nav>

    <div>
        <div id="inst-lesson-1" role="tabpanel" aria-labelledby="inst-lesson-tab-1">
            <div class="border border-d9 rounded-16px bg-white px-5 sm:px-7 py-6 sm:py-7">
                <div class="flex items-start gap-3 mb-6">
                    <span class="size-11 rounded-12px bg-primary/10 center shrink-0">
                        <span class="icon-[tabler--clipboard-list] size-6 text-primary"></span>
                    </span>
                    <div class="text-start min-w-0">
                        <h2 class="font-bold text-18px sm:text-20px text-primary mb-1">{{ $quizCard['title'] ?? '' }}</h2>
                        <p class="font-medium text-14px text-gray">{{ $quizCard['subtitle'] ?? '' }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
                    @foreach ([
                        ['وقت الاختبار', $quizCard['duration'] ?? ''],
                        ['عدد الأسئلة', $quizCard['questions_count'] ?? ''],
                        ['درجة النجاح', $quizCard['pass_score'] ?? ''],
                        ['المحاولات', $quizCard['attempts'] ?? ''],
                    ] as [$label, $value])
                        <div class="rounded-12px bg-[#F8FAFC] border border-d9 px-4 py-3 flex items-center justify-between gap-3">
                            <p class="font-semibold text-13px text-gray">{{ $label }}</p>
                            <p class="font-bold text-14px text-primary">{{ $value }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="flex justify-end">
                    <a href="{{ route('panel.v1.instructor.quizzes') }}"
                        class="inline-flex items-center gap-2 h-12 px-5 rounded-12px bg-primary text-white font-bold text-15px hover:opacity-90 transition">
                        إدارة اختبارات الدورة
                        <span class="icon-[tabler--arrow-narrow-left] size-5"></span>
                    </a>
                </div>
            </div>
        </div>

        <div id="inst-lesson-2" class="hidden" role="tabpanel" aria-labelledby="inst-lesson-tab-2">
            <div class="border border-d9 rounded-16px bg-white px-5 sm:px-7 py-6 sm:py-7">
                <div class="flex items-start gap-3 mb-5">
                    <span class="size-11 rounded-12px bg-[#FAF8F4] center shrink-0">
                        <span class="icon-[tabler--clipboard-check] size-6 text-primary"></span>
                    </span>
                    <div class="text-start min-w-0">
                        <h2 class="font-bold text-18px sm:text-20px text-primary mb-1">{{ $assignmentCard['title'] ?? '' }}</h2>
                        <p class="font-medium text-14px text-gray">{{ $assignmentCard['subtitle'] ?? '' }}</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-3 mb-5">
                    @foreach ([
                        ['الموعد النهائي', $assignmentCard['deadline'] ?? ''],
                        ['المحاولات', $assignmentCard['attempts'] ?? ''],
                        ['درجة الواجب', $assignmentCard['grade'] ?? ''],
                        ['درجة النجاح', $assignmentCard['pass_grade'] ?? ''],
                    ] as [$label, $value])
                        <span class="inline-flex items-center gap-1.5 rounded-10px border border-d9 px-3.5 py-2 font-medium text-13px">
                            <span class="text-gray">{{ $label }}:</span>
                            <span class="font-bold text-primary">{{ $value }}</span>
                        </span>
                    @endforeach
                </div>
                <p class="font-medium text-15px text-primary/80 leading-relaxed mb-6 text-start">
                    {{ $assignmentCard['description'] ?? '' }}
                </p>
                <div class="flex justify-end">
                    <a href="{{ route('panel.v1.instructor.courses.assignment', ['slug' => $courseSlug]) }}"
                        class="inline-flex items-center gap-2 h-12 px-5 rounded-12px bg-primary text-white font-bold text-15px hover:opacity-90 transition">
                        تقييم إجابات الطلاب
                        <span class="icon-[tabler--arrow-narrow-left] size-5"></span>
                    </a>
                </div>
            </div>
        </div>

        <div id="inst-lesson-3" class="hidden" role="tabpanel" aria-labelledby="inst-lesson-tab-3">
            <div class="border border-d9 rounded-16px bg-white px-6 py-14 text-center">
                <p class="font-semibold text-17px text-primary mb-2">لا توجد تعليقات بعد</p>
                <p class="font-medium text-14px text-gray">ستظهر تعليقات الطلاب على هذه المحاضرة هنا.</p>
            </div>
        </div>

        <div id="inst-lesson-4" class="hidden" role="tabpanel" aria-labelledby="inst-lesson-tab-4">
            @if (count($fileRows))
                <div class="space-y-3">
                    @foreach ($fileRows as $file)
                        <div class="border border-d9 rounded-14px bg-white px-4 sm:px-5 py-4 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="size-11 rounded-10px bg-primary/10 center shrink-0">
                                    <span class="icon-[tabler--file-text] size-5 text-primary"></span>
                                </span>
                                <div class="min-w-0 text-start">
                                    <p class="font-semibold text-15px text-primary truncate">{{ $file['name'] }}</p>
                                    <p class="font-medium text-13px text-gray">{{ $file['size'] }}</p>
                                </div>
                            </div>
                            <a href="#" class="font-semibold text-14px text-primary hover:opacity-80 shrink-0">تحميل</a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="border border-d9 rounded-16px bg-white px-6 py-14 text-center">
                    <p class="font-medium text-15px text-gray">لا توجد ملفات مرفقة لهذه المحاضرة.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="flex justify-end pt-2">
        <button type="button"
            class="inline-flex items-center gap-2 h-12 sm:h-14 px-6 sm:px-8 rounded-12px bg-primary text-white font-bold text-15px sm:text-16px hover:opacity-90 transition shadow-[0_6px_20px_rgba(15,76,69,0.18)]">
            الانتقال الى المحاضرة التالية
            <span class="icon-[tabler--arrow-narrow-left] size-5"></span>
        </button>
    </div>
</div>
@endsection
