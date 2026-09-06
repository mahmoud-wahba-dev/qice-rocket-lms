@php
    $courseData = $course ?? ['title' => '', 'subtitle' => '', 'progress' => 0];
    $chapterList = $chapters ?? [];
@endphp

<div class="flex flex-col h-full">
    <div class="bg-primary text-white px-5 py-6">
        <div class="flex items-start justify-between gap-3 mb-4 lg:hidden">
            <p class="font-bold text-18px leading-snug">{{ $courseData['title'] }}</p>
            <button type="button" class="btn btn-text btn-square text-white hover:bg-white/10"
                data-instructor-course-sidebar-close aria-label="إغلاق">
                <span class="icon-[tabler--x] size-5"></span>
            </button>
        </div>
        <h2 class="hidden lg:block font-bold text-20px leading-snug mb-2">{{ $courseData['title'] }}</h2>
        <p class="font-medium text-14px text-white/80 mb-5">{{ $courseData['subtitle'] }}</p>
        <div class="flex items-center justify-between gap-2 mb-2">
            <span class="font-medium text-13px">نسبة الإنجاز</span>
            <span class="font-bold text-14px">{{ $courseData['progress'] ?? 0 }}%</span>
        </div>
        <div class="h-2 rounded-full bg-white/20 overflow-hidden">
            <div class="h-full rounded-full bg-[#0FC787]" style="width: {{ (int) ($courseData['progress'] ?? 0) }}%"></div>
        </div>
    </div>

    <div class="flex-1 px-4 py-5 space-y-3 overflow-y-auto">
        @foreach ($chapterList as $chapter)
            @php
                $isExpanded = !empty($chapter['expanded']);
                $isCompleted = !empty($chapter['completed']);
            @endphp
            <div class="rounded-14px overflow-hidden border transition-colors {{ $isExpanded ? 'bg-[#FAF8F4] border-primary' : 'bg-white border-d9' }}"
                data-course-accordion data-open="{{ $isExpanded ? 'true' : 'false' }}">
                <button type="button"
                    class="w-full flex items-start justify-between gap-3 px-4 py-3.5 text-start"
                    data-course-accordion-toggle aria-expanded="{{ $isExpanded ? 'true' : 'false' }}">
                    <span class="flex items-start gap-3 min-w-0">
                        @if ($isCompleted)
                            <span class="size-7 rounded-full bg-primary center shrink-0 mt-0.5">
                                <span class="icon-[tabler--check] size-4 text-white"></span>
                            </span>
                        @else
                            <span class="size-7 rounded-full bg-[#E8E8E8] center shrink-0 mt-0.5">
                                <span class="icon-[tabler--check] size-4 text-white"></span>
                            </span>
                        @endif
                        <span class="min-w-0">
                            <span class="block font-bold text-15px text-black">{{ $chapter['title'] }}</span>
                            @if (!empty($chapter['subtitle']))
                                <span class="block font-medium text-12px text-gray mt-1 {{ $isExpanded ? '' : 'hidden' }}"
                                    data-course-accordion-subtitle>{{ $chapter['subtitle'] }}</span>
                            @endif
                        </span>
                    </span>
                    <span class="icon-[tabler--chevron-down] size-5 text-black/50 shrink-0 mt-1 transition-transform {{ $isExpanded ? 'rotate-180' : '' }}"
                        data-course-accordion-chevron></span>
                </button>
                <div class="{{ $isExpanded ? '' : 'hidden' }} px-4 pb-4" data-course-accordion-panel>
                    <div class="space-y-1 ps-10">
                        @foreach ($chapter['items'] ?? [] as $item)
                            <div class="flex items-center gap-2 py-2 font-medium text-14px text-black">
                                @if (($item['type'] ?? '') === 'video')
                                    <span class="icon-[tabler--player-play] size-4"></span>
                                @else
                                    <span class="icon-[tabler--file-text] size-4"></span>
                                @endif
                                {{ $item['title'] }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
