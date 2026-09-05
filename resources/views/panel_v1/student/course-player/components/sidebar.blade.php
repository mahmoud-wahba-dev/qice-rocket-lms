@php
    $courseSlug = $slug ?? 'demo';
    $courseData = $course ?? ['title' => '', 'subtitle' => '', 'progress' => 0, 'progress_label' => 'نسبة الإنجاز'];
    $chapterList = $chapters ?? [];
@endphp

<div class="flex flex-col h-full">
    <div class="bg-primary text-white px-5 py-6 lg:px-6">
        <div class="flex items-start justify-between gap-3 mb-5 lg:hidden">
            <p class="font-bold text-25px leading-snug">{{ $courseData['title'] }}</p>
            <button type="button" class="btn btn-text btn-square text-white hover:bg-white/10"
                data-course-sidebar-close aria-label="إغلاق">
                <span class="icon-[tabler--x] size-5"></span>
            </button>
        </div>

        <h2 class="hidden lg:block font-bold text-25px leading-snug mb-2">{{ $courseData['title'] }}</h2>
        <p class="font-medium text-14px text-white mb-5">{{ $courseData['subtitle'] }}</p>

        <div>
            <div class="flex items-center justify-between gap-2 mb-2">
                <span class="font-medium text-12px text-white/90">{{ $courseData['progress_label'] }}</span>
                <span class="font-bold text-14px">{{ $courseData['progress'] }}%</span>
            </div>
            <div class="h-2 rounded-full bg-white/20 overflow-hidden" role="progressbar"
                aria-valuenow="{{ $courseData['progress'] }}" aria-valuemin="0" aria-valuemax="100">
                <div class="h-full rounded-full bg-[#0FC787]" style="width: {{ (int) $courseData['progress'] }}%"></div>
            </div>
        </div>
    </div>

    <div class="flex-1 px-4 py-5 space-y-3 overflow-y-auto">
        @foreach ($chapterList as $chapter)
            @php
                $isExpanded = !empty($chapter['expanded']);
                $isCompleted = !empty($chapter['completed']);
                $chapterSubtitle = $chapter['subtitle'] ?? 'هنا عنوان المحاضرة';
            @endphp

            <div
                class="rounded-14px overflow-hidden transition-colors border
                    {{ $isExpanded ? 'bg-[#FAF8F4] border-primary' : 'bg-white border-d9' }}"
                data-course-accordion
                data-open="{{ $isExpanded ? 'true' : 'false' }}">

                <button type="button"
                    class="w-full flex items-start justify-between gap-3 px-4 py-3.5 text-start"
                    data-course-accordion-toggle
                    aria-expanded="{{ $isExpanded ? 'true' : 'false' }}">

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

                        <span class="min-w-0 flex flex-col gap-1">
                            <span class="font-bold text-16px text-black leading-snug">{{ $chapter['title'] }}</span>
                            <span
                                class="font-medium text-12px text-gray leading-snug {{ $isExpanded ? '' : 'hidden' }}"
                                data-course-accordion-subtitle>
                                {{ $chapterSubtitle }}
                            </span>
                        </span>
                    </span>

                    <span
                        class="icon-[tabler--chevron-down] size-5 text-black/50 shrink-0 mt-1 transition-transform duration-200 {{ $isExpanded ? 'rotate-180' : '' }}"
                        data-course-accordion-chevron></span>
                </button>

                <div class="{{ $isExpanded ? '' : 'hidden' }} px-4 pb-4"
                    data-course-accordion-panel>
                    <div class="relative ms-3 ps-6 space-y-1">
                        {{-- Timeline rail --}}
                        <span class="pointer-events-none absolute top-3 bottom-3 start-[0.35rem] w-px bg-d9"
                            aria-hidden="true"></span>

                        @forelse ($chapter['items'] ?? [] as $item)
                            @php $isActive = !empty($item['active']); @endphp
                            <a href="{{ route('panel.v1.student.course.watch', ['slug' => $courseSlug]) }}"
                                class="relative flex items-center gap-3 py-2.5 font-medium text-14px text-black hover:text-primary transition">
                                <span
                                    class="absolute -start-[1.4rem] top-1/2 -translate-y-1/2 size-3 rounded-full border-2 border-[#FAF8F4] shrink-0
                                        {{ $isActive ? 'bg-primary' : 'bg-d9' }}"
                                    aria-hidden="true"></span>

                                @if (($item['type'] ?? '') === 'video')
                                    <span class="icon-[tabler--player-play] size-5 shrink-0 text-black"></span>
                                @else
                                    <span class="icon-[tabler--file-text] size-5 shrink-0 text-black"></span>
                                @endif
                                <span class="leading-snug">{{ $item['title'] }}</span>
                            </a>
                        @empty
                            <p class="font-medium text-13px text-gray py-2">لا يوجد محتوى بعد</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
