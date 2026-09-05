@extends('panel_v1.student.course-player.layouts.app')

@section('content')
@php
$courseSlug = $slug ?? 'demo';
$lessonTitle = $lesson['title'] ?? 'محتوى اليوم';
$showQuiz = $hasLectureQuiz ?? false;
$showAssignment = $hasLectureAssignment ?? false;
$showComments = $hasComments ?? false;
$showFiles = $hasFiles ?? false;
$quizCard = $lectureQuiz ?? [];
$assignmentCard = $lectureAssignment ?? [];
$fileRows = $files ?? [];
@endphp

<div class="flex flex-col gap-8 pb-8">
    <h1 class="font-extrabold text-24px sm:text-28px lg:text-32px text-primary leading-snug">
        {{ $lessonTitle }}
    </h1>

    {{-- Video placeholder --}}
    <div class="relative w-full overflow-hidden rounded-16px bg-[#4a4a4a] aspect-video shadow-sm">
        <div class="absolute inset-0 flex flex-col items-center justify-center text-white/90 pointer-events-none">
            <p class="absolute top-4 start-4 font-medium text-13px sm:text-14px text-white/80">
                This is where the title can go for your video.
            </p>
            <span class="size-16 sm:size-20 rounded-full bg-white/20 center backdrop-blur-sm">
                <span class="icon-[tabler--player-play-filled] size-8 sm:size-10 text-white ms-1"></span>
            </span>
        </div>
        <div
            class="absolute inset-x-0 bottom-0 h-10 bg-gradient-to-t from-black/70 to-transparent flex items-end px-3 pb-2 gap-3">
            <span class="icon-[tabler--player-play] size-4 text-white"></span>
            <div class="flex-1 h-1 rounded-full bg-white/30 overflow-hidden">
                <div class="h-full w-1/12 bg-[#EF4444] rounded-full"></div>
            </div>
            <span class="font-medium text-11px text-white/90">0:01 / 1:00</span>
            <span class="icon-[tabler--volume] size-4 text-white"></span>
            <span class="icon-[tabler--maximize] size-4 text-white"></span>
        </div>
    </div>

    {{-- Tabs --}}
    <nav class="student-dash-tabs tabs tabs-bordered tabs-lg w-full overflow-x-auto" aria-label="محتوى المحاضرة"
        role="tablist" aria-orientation="horizontal">
        <button type="button"
            class="tab flex-1 justify-center font-semibold text-16px sm:text-20px text-gray active-tab:text-primary pb-4 active whitespace-nowrap"
            id="lesson-tabs-item-1" data-tab="#lesson-tabs-1" aria-controls="lesson-tabs-1" role="tab"
            aria-selected="true">
            اختبار المحاضرة
        </button>
        <button type="button"
            class="tab flex-1 justify-center font-semibold text-16px sm:text-20px text-gray active-tab:text-primary pb-4 whitespace-nowrap"
            id="lesson-tabs-item-2" data-tab="#lesson-tabs-2" aria-controls="lesson-tabs-2" role="tab"
            aria-selected="false">
            تكليفات المحاضرة
        </button>
        <button type="button"
            class="tab flex-1 justify-center font-semibold text-16px sm:text-20px text-gray active-tab:text-primary pb-4 whitespace-nowrap"
            id="lesson-tabs-item-3" data-tab="#lesson-tabs-3" aria-controls="lesson-tabs-3" role="tab"
            aria-selected="false">
            التعليقات
        </button>
        <button type="button"
            class="tab flex-1 justify-center font-semibold text-16px sm:text-20px text-gray active-tab:text-primary pb-4 whitespace-nowrap"
            id="lesson-tabs-item-4" data-tab="#lesson-tabs-4" aria-controls="lesson-tabs-4" role="tab"
            aria-selected="false">
            الملفات
        </button>
    </nav>

    <div>
        {{-- Quiz tab --}}
        <div id="lesson-tabs-1" role="tabpanel" aria-labelledby="lesson-tabs-item-1">
            @if ($showQuiz)
            <div class="border border-d9 rounded-20px bg-white px-5 sm:px-8 py-7">
                <div class="flex items-start gap-3 mb-6">
                    <span class="size-11 rounded-12px bg-primary/10 center shrink-0">
                        <span class="icon-[tabler--clipboard-list] size-6 text-primary"></span>
                    </span>
                    <div>
                        <h2 class="font-bold text-20px sm:text-24px text-primary mb-1">{{ $quizCard['title'] ?? '' }}
                        </h2>
                        <p class="font-medium text-14px text-gray">{{ $quizCard['subtitle'] ?? '' }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-7">
                    <div
                        class="rounded-12px bg-[#F8FAFC] border border-[#E2E8F0] px-4 py-3 text-center flex items-center justify-between gap-8 ">
                        <p class="font-bold text-12px text-[#64748B] mb-1">وقت الاختبار</p>
                        <p class="font-bold text-13px text-[#0F172A]">{{ $quizCard['duration'] ?? '' }}</p>
                    </div>
                    <div
                        class="rounded-12px bg-[#F8FAFC] border border-[#E2E8F0] px-4 py-3 text-center flex items-center justify-between gap-8 ">
                        <p class="font-bold text-12px text-[#64748B] mb-1">عدد الأسئلة</p>
                        <p class="font-bold text-13px text-[#0F172A]">{{ $quizCard['questions_count'] ?? '' }}</p>
                    </div>
                    <div
                        class="rounded-12px bg-[#F8FAFC] border border-[#E2E8F0] px-4 py-3 text-center flex items-center justify-between gap-8 ">
                        <p class="font-bold text-12px text-[#64748B] mb-1">درجة النجاح</p>
                        <p class="font-bold text-13px text-[#0F172A]">{{ $quizCard['pass_score'] ?? '' }}</p>
                    </div>
                    <div
                        class="rounded-12px bg-[#F8FAFC] border border-[#E2E8F0] px-4 py-3 text-center flex items-center justify-between gap-8 ">
                        <p class="font-bold text-12px text-[#64748B] mb-1">المحاولات</p>
                        <p class="font-bold text-13px text-[#0F172A]">{{ $quizCard['attempts'] ?? '' }}</p>
                    </div>
                </div>

                <div class="flex justify-end">
                    <a href="{{ route('panel.v1.student.course.quiz', ['slug' => $courseSlug]) }}"
                        class="btn btn-primary rounded-10px h-12 px-6 font-bold text-15px gap-2">
                        <span class="icon-[tabler--arrow-left] size-5"></span>
                        ابدأ الاختبار الآن
                    </a>
                </div>
            </div>
            @else
            @include('panel_v1.student.course-player.components.empty-state', [
            'title' => 'لا توجد اختبارات لهذه المحاضرة',
            ])
            @endif
        </div>

        {{-- Assignments tab --}}
        <div id="lesson-tabs-2" class="hidden" role="tabpanel" aria-labelledby="lesson-tabs-item-2">
            @if ($showAssignment)
                <div class="border border-d9 rounded-20px bg-white px-5 sm:px-8 py-7">
                    <div class="flex items-start gap-3 mb-6">
                        <span class="size-12 rounded-14px bg-[#FAF8F4] center shrink-0">
                            <span class="icon-[tabler--clipboard-list] size-6 text-primary"></span>
                        </span>
                        <div class="min-w-0 text-start">
                            <h2 class="font-bold text-20px sm:text-22px text-primary leading-snug mb-1">
                                {{ $assignmentCard['title'] ?? '' }}
                            </h2>
                            @if (!empty($assignmentCard['subtitle']))
                                <p class="font-medium text-14px text-primary/70 leading-snug">
                                    {{ $assignmentCard['subtitle'] }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-6 sm:gap-8 mb-6">
                        <span
                            class="inline-flex items-center gap-1 rounded-10px border border-d9 bg-white px-3.5 py-2 font-medium text-13px sm:text-14px">
                            <span class="text-gray">الموعد النهائي:</span>
                            <span class="text-[#64748B]">{{ $assignmentCard['deadline'] ?? '' }}</span>
                        </span>
                        <span
                            class="inline-flex items-center gap-1 rounded-10px border border-d9 bg-white px-3.5 py-2 font-medium text-13px sm:text-14px">
                            <span class="text-gray">المحاولات:</span>
                            <span class="text-[#64748B]">{{ $assignmentCard['attempts'] ?? '' }}</span>
                        </span>
                        <span
                            class="inline-flex items-center gap-1 rounded-10px border border-d9 bg-white px-3.5 py-2 font-medium text-13px sm:text-14px">
                            <span class="text-gray">درجة الواجب:</span>
                            <span class="font-bold text-[#00B31B]">{{ $assignmentCard['grade'] ?? '' }}</span>
                        </span>
                        <span
                            class="inline-flex items-center gap-1 rounded-10px border border-d9 bg-white px-3.5 py-2 font-medium text-13px sm:text-14px">
                            <span class="text-gray">درجة النجاح:</span>
                            <span class="font-bold text-[#00B31B]">{{ $assignmentCard['pass_grade'] ?? '' }}</span>
                        </span>
                    </div>

                    <p class="font-medium text-15px text-[#475569] leading-relaxed mb-4 text-start">
                        {{ $assignmentCard['description'] ?? '' }}
                    </p>

                    <a href="#"
                        class="inline-flex items-center gap-2 font-medium text-14px text-[#94A3B8] mb-8 hover:text-primary transition text-start">
                        <span class="icon-[tabler--file-text] size-5 shrink-0"></span>
                        ملف المصادر المرفق: {{ $assignmentCard['file_name'] ?? '' }} ({{ $assignmentCard['file_size'] ?? '' }})
                    </a>

                    <div class="flex justify-end">
                        <a href="{{ route('panel.v1.student.course.assignment', ['slug' => $courseSlug]) }}"
                            class="btn btn-primary rounded-12px h-12 px-6 font-bold text-15px gap-2">
                            الانتقال لحل الواجب
                            <span class="icon-[tabler--arrow-down] size-5"></span>
                        </a>
                    </div>
                </div>
            @else
                @include('panel_v1.student.course-player.components.empty-state', [
                    'title' => 'لا توجد تكليفات لهذه المحاضرة',
                ])
            @endif
        </div>

        {{-- Comments tab --}}
        <div id="lesson-tabs-3" class="hidden" role="tabpanel" aria-labelledby="lesson-tabs-item-3">
            @if ($showComments)
            <div class="border border-d9 rounded-20px bg-white px-6 py-10 text-center">
                <p class="font-medium text-16px text-gray">ستظهر التعليقات هنا.</p>
            </div>
            @else
            @include('panel_v1.student.course-player.components.empty-state', [
            'title' => 'لم تضف تعليق بعد',
            'cta' => 'أضف تعليق',
            'ctaHref' => '#',
            ])
            @endif
        </div>

        {{-- Files tab --}}
        <div id="lesson-tabs-4" class="hidden" role="tabpanel" aria-labelledby="lesson-tabs-item-4">
            @if ($showFiles && count($fileRows))
            <div class="space-y-4">
                @foreach ($fileRows as $file)
                <div class="border border-d9 rounded-16px bg-white px-5 py-4 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="size-11 rounded-12px bg-primary/10 center shrink-0">
                            <span class="icon-[tabler--file-type-pdf] size-6 text-primary"></span>
                        </span>
                        <p class="font-semibold text-16px text-primary truncate">{{ $file['name'] }}</p>
                    </div>
                    <button type="button"
                        class="size-11 rounded-12px bg-primary center text-white shrink-0 hover:bg-primary/90 transition"
                        aria-label="تحميل">
                        <span class="icon-[tabler--download] size-5"></span>
                    </button>
                </div>
                @endforeach
            </div>
            @else
            @include('panel_v1.student.course-player.components.empty-state', [
            'title' => 'لا توجد ملفات لهذه المحاضرة',
            ])
            @endif
        </div>
    </div>

    <div class="flex justify-end pt-2">
        <a href="{{ route('panel.v1.student.course.watch', ['slug' => $courseSlug]) }}"
            class="btn btn-primary rounded-12px h-12 sm:h-14 px-6 sm:px-8 font-bold text-15px sm:text-16px gap-2 w-full sm:w-auto">
            <span class="icon-[tabler--arrow-left] size-5"></span>
            الانتقال الى المحاضرة التالية
        </a>
    </div>
</div>
@endsection
