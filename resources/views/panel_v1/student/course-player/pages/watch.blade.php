@extends('panel_v1.student.course-player.layouts.app')

@section('content')
@php
$courseSlug = $slug ?? 'demo';
$lessonTitle = $lesson['title'] ?? 'محتوى اليوم';
$chapterTitle = $lesson['chapter_title'] ?? '';
$heading = $chapterTitle !== ''
    ? ('محتوى اليوم - ' . $lessonTitle)
    : $lessonTitle;
$showQuiz = $hasLectureQuiz ?? false;
$showAssignment = $hasLectureAssignment ?? false;
$showComments = $hasComments ?? false;
$showFiles = $hasFiles ?? false;
$quizCard = $lectureQuiz ?? [];
$assignmentCard = $lectureAssignment ?? [];
$fileRows = $files ?? [];
$media = $currentMedia ?? null;
$mediaMode = $media['mode'] ?? null;
@endphp

@push('head')
<link rel="stylesheet" href="/assets/vendors/plyr.io/plyr.min.css">
<style>
    #landing-v1-app .panel-v1-course-player .plyr {
        --plyr-color-main: #0F3D36;
        border-radius: 16px;
        overflow: hidden;
        width: 100%;
        height: 100%;
    }
    #landing-v1-app .panel-v1-course-player .plyr--video,
    #landing-v1-app .panel-v1-course-player .plyr__video-embed {
        background: #2f2f2f;
    }
    #landing-v1-app .panel-v1-course-player .aspect-video .plyr,
    #landing-v1-app .panel-v1-course-player .aspect-video .plyr__video-wrapper {
        height: 100%;
    }
    #landing-v1-app .panel-v1-course-player .aspect-video video {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
</style>
@endpush

<div class="flex flex-col gap-8 pb-8">
    <h1 class="font-extrabold text-24px sm:text-28px lg:text-32px text-primary leading-snug">
        {{ $heading }}
    </h1>

    {{-- Real player (Plyr — same stack as design_1 learning page) --}}
    <div class="relative w-full overflow-hidden rounded-16px bg-[#4a4a4a] shadow-sm">
        @if ($mediaMode === 'youtube' || $mediaMode === 'vimeo')
            <div class="js-file-player-el plyr__video-embed aspect-video w-full" id="course-player-media">
                <iframe
                    src="{{ $media['src'] }}"
                    allowfullscreen
                    allowtransparency
                    allow="autoplay"
                    title="{{ $media['title'] ?? $lessonTitle }}"
                ></iframe>
            </div>
        @elseif ($mediaMode === 'html5')
            <div class="aspect-video w-full bg-[#2f2f2f]">
                <video id="course-player-media" class="js-file-player-el plyr-io-video w-full h-full"
                    controls preload="metadata" playsinline crossorigin="anonymous"
                    @if (!empty($media['poster'])) data-poster="{{ $media['poster'] }}" @endif>
                    <source src="{{ $media['src'] }}" type="video/mp4"/>
                </video>
            </div>
        @elseif ($mediaMode === 'download')
            <div class="aspect-video flex flex-col items-center justify-center gap-4 px-6 text-center text-white">
                <span class="icon-[tabler--file-text] size-14 text-white/80"></span>
                <p class="font-bold text-18px">{{ $media['title'] ?? 'ملف مرفق' }}</p>
                <p class="font-medium text-14px text-white/70">{{ strtoupper($media['file_type'] ?? 'file') }} — {{ $media['volume'] ?? '' }}</p>
                @if (!empty($media['src']))
                    <a href="{{ $media['src'] }}" target="_blank" rel="noopener"
                        class="btn btn-primary rounded-10px h-11 px-6 font-bold text-14px">تحميل الملف</a>
                @endif
            </div>
        @elseif ($mediaMode === 'session')
            <div class="aspect-video flex flex-col items-center justify-center gap-3 px-6 text-center text-white">
                <span class="icon-[tabler--broadcast] size-14 text-white/80"></span>
                <p class="font-bold text-18px">{{ $media['title'] ?? 'محاضرة مباشرة' }}</p>
                @if (!empty($media['date']))
                    <p class="font-medium text-14px text-white/70">موعد الجلسة: {{ $media['date'] }}</p>
                @endif
                @if (!empty($media['src']))
                    <a href="{{ $media['src'] }}" target="_blank" rel="noopener"
                        class="btn btn-primary rounded-10px h-11 px-6 font-bold text-14px">الانضمام للجلسة</a>
                @else
                    <p class="font-medium text-13px text-white/60">رابط الجلسة غير متاح حالياً</p>
                @endif
            </div>
        @elseif ($mediaMode === 'text')
            <div class="min-h-[280px] bg-white text-primary px-6 py-8">
                <h2 class="font-bold text-20px mb-4">{{ $media['title'] ?? '' }}</h2>
                <div class="font-medium text-15px leading-relaxed prose max-w-none">{!! $media['content'] ?? '' !!}</div>
            </div>
        @else
            <div class="aspect-video flex flex-col items-center justify-center text-white/90">
                <span class="size-16 rounded-full bg-white/20 center mb-4">
                    <span class="icon-[tabler--player-play-filled] size-8 text-white ms-1"></span>
                </span>
                <p class="font-medium text-14px">لا يوجد فيديو لهذه المحاضرة بعد</p>
            </div>
        @endif
    </div>

    <nav class="student-dash-tabs tabs tabs-bordered tabs-lg w-full overflow-x-auto" aria-label="محتوى المحاضرة"
        role="tablist" aria-orientation="horizontal">
        <button type="button"
            class="tab flex-1 justify-center font-semibold text-16px sm:text-20px text-gray active-tab:text-primary pb-4 active whitespace-nowrap"
            id="lesson-tabs-item-1" data-v1-tab="#lesson-tabs-1" aria-controls="lesson-tabs-1" role="tab"
            aria-selected="true">
            اختبار المحاضرة
        </button>
        <button type="button"
            class="tab flex-1 justify-center font-semibold text-16px sm:text-20px text-gray active-tab:text-primary pb-4 whitespace-nowrap"
            id="lesson-tabs-item-2" data-v1-tab="#lesson-tabs-2" aria-controls="lesson-tabs-2" role="tab"
            aria-selected="false">
            تكليفات المحاضرة
        </button>
        <button type="button"
            class="tab flex-1 justify-center font-semibold text-16px sm:text-20px text-gray active-tab:text-primary pb-4 whitespace-nowrap"
            id="lesson-tabs-item-3" data-v1-tab="#lesson-tabs-3" aria-controls="lesson-tabs-3" role="tab"
            aria-selected="false">
            التعليقات
        </button>
        <button type="button"
            class="tab flex-1 justify-center font-semibold text-16px sm:text-20px text-gray active-tab:text-primary pb-4 whitespace-nowrap"
            id="lesson-tabs-item-4" data-v1-tab="#lesson-tabs-4" aria-controls="lesson-tabs-4" role="tab"
            aria-selected="false">
            الملفات
        </button>
    </nav>

    <div>
        <div id="lesson-tabs-1" role="tabpanel" aria-labelledby="lesson-tabs-item-1">
            @if ($showQuiz)
            <div class="border border-d9 rounded-20px bg-white px-5 sm:px-8 py-7">
                <div class="flex items-start gap-3 mb-6">
                    <span class="size-11 rounded-12px bg-primary/10 center shrink-0">
                        <span class="icon-[tabler--clipboard-list] size-6 text-primary"></span>
                    </span>
                    <div>
                        <h2 class="font-bold text-20px sm:text-24px text-primary mb-1">{{ $quizCard['title'] ?? '' }}</h2>
                        <p class="font-medium text-14px text-gray">{{ $quizCard['subtitle'] ?? '' }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-7">
                    <div class="rounded-12px bg-[#F8FAFC] border border-[#E2E8F0] px-4 py-3 flex items-center justify-between gap-8">
                        <p class="font-bold text-12px text-[#64748B]">وقت الاختبار</p>
                        <p class="font-bold text-13px text-[#0F172A]">{{ $quizCard['duration'] ?? '' }}</p>
                    </div>
                    <div class="rounded-12px bg-[#F8FAFC] border border-[#E2E8F0] px-4 py-3 flex items-center justify-between gap-8">
                        <p class="font-bold text-12px text-[#64748B]">عدد الأسئلة</p>
                        <p class="font-bold text-13px text-[#0F172A]">{{ $quizCard['questions_count'] ?? '' }}</p>
                    </div>
                    <div class="rounded-12px bg-[#F8FAFC] border border-[#E2E8F0] px-4 py-3 flex items-center justify-between gap-8">
                        <p class="font-bold text-12px text-[#64748B]">درجة النجاح</p>
                        <p class="font-bold text-13px text-[#0F172A]">{{ $quizCard['pass_score'] ?? '' }}</p>
                    </div>
                    <div class="rounded-12px bg-[#F8FAFC] border border-[#E2E8F0] px-4 py-3 flex items-center justify-between gap-8">
                        <p class="font-bold text-12px text-[#64748B]">المحاولات</p>
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
                        <span class="inline-flex items-center gap-1 rounded-10px border border-d9 bg-white px-3.5 py-2 font-medium text-13px sm:text-14px">
                            <span class="text-gray">الموعد النهائي:</span>
                            <span class="text-[#64748B]">{{ $assignmentCard['deadline'] ?? '' }}</span>
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-10px border border-d9 bg-white px-3.5 py-2 font-medium text-13px sm:text-14px">
                            <span class="text-gray">المحاولات:</span>
                            <span class="text-[#64748B]">{{ $assignmentCard['attempts'] ?? '' }}</span>
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-10px border border-d9 bg-white px-3.5 py-2 font-medium text-13px sm:text-14px">
                            <span class="text-gray">درجة الواجب:</span>
                            <span class="font-bold text-[#00B31B]">{{ $assignmentCard['grade'] ?? '' }}</span>
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-10px border border-d9 bg-white px-3.5 py-2 font-medium text-13px sm:text-14px">
                            <span class="text-gray">درجة النجاح:</span>
                            <span class="font-bold text-[#00B31B]">{{ $assignmentCard['pass_grade'] ?? '' }}</span>
                        </span>
                    </div>

                    <p class="font-medium text-15px text-[#475569] leading-relaxed mb-4 text-start">
                        {{ $assignmentCard['description'] ?? '' }}
                    </p>

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

        <div id="lesson-tabs-3" class="hidden" role="tabpanel" aria-labelledby="lesson-tabs-item-3">
            @include('panel_v1.student.course-player.components.empty-state', [
                'title' => 'لم تضف تعليق بعد',
                'cta' => 'أضف تعليق',
                'ctaHref' => '#',
            ])
        </div>

        <div id="lesson-tabs-4" class="hidden" role="tabpanel" aria-labelledby="lesson-tabs-item-4">
            @if ($showFiles && count($fileRows))
            <div class="space-y-4">
                @foreach ($fileRows as $file)
                <div class="border border-d9 rounded-16px bg-white px-5 py-4 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="size-11 rounded-12px bg-primary/10 center shrink-0">
                            <span class="icon-[tabler--file-type-pdf] size-6 text-primary"></span>
                        </span>
                        <div class="min-w-0">
                            <p class="font-semibold text-16px text-primary truncate">{{ $file['name'] }}</p>
                            @if (!empty($file['size']))
                                <p class="font-medium text-12px text-gray">{{ $file['size'] }}</p>
                            @endif
                        </div>
                    </div>
                    @if (!empty($file['url']))
                        <a href="{{ $file['url'] }}" target="_blank" rel="noopener"
                            class="size-11 rounded-12px bg-primary center text-white shrink-0 hover:bg-primary/90 transition"
                            aria-label="تحميل">
                            <span class="icon-[tabler--download] size-5"></span>
                        </a>
                    @endif
                </div>
                @endforeach
            </div>
            @else
            @include('panel_v1.student.course-player.components.empty-state', [
                'title' => 'لا توجد ملفات مرفقة',
            ])
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script src="/assets/vendors/plyr.io/plyr.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Plyr === 'undefined') return;
    document.querySelectorAll('.js-file-player-el').forEach(function (el) {
        try { new Plyr(el); } catch (e) {}
    });
});
</script>
@endpush
@endsection
