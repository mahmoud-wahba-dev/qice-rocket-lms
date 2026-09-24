@php
    $slug = $course['slug'] ?? null;
    $courseId = $course['id'] ?? null;
    $status = $course['status'] ?? '';
    $isDraft = $status === 'is_draft';
    $menuId = $menuId ?? ('course-menu-' . ($index ?? 0));
    $watchUrl = !empty($slug) ? route('panel.v1.instructor.courses.watch', ['slug' => $slug]) : route('panel.v1.instructor.courses');
    $performanceUrl = !empty($slug) ? route('panel.v1.instructor.courses.performance', ['slug' => $slug]) : route('panel.v1.instructor.courses');
    $assignmentsUrl = !empty($slug) ? route('panel.v1.instructor.courses.assignments', ['slug' => $slug]) : route('panel.v1.instructor.assignments');
    $editUrl = !empty($courseId)
        ? route('panel.v1.instructor.courses.create', ['step' => 1, 'draft' => $courseId])
        : $watchUrl;
@endphp

<article class="relative z-0 hover:z-30 rounded-14px border border-d9 bg-white p-4 sm:p-5 overflow-visible">
    <div class="flex gap-3 sm:gap-4 items-start mb-4">
        <div class="size-14 sm:size-16 rounded-12px bg-primary shrink-0 overflow-hidden">
            @if (!empty($course['thumbnail']))
                <img src="{{ $course['thumbnail'] }}" alt="" class="w-full h-full object-cover" loading="lazy">
            @endif
        </div>

        <div class="min-w-0 flex-1 overflow-visible">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <h2 class="font-semibold text-24px text-primary mb-0.5 leading-snug">{{ $course['title'] }}</h2>
                    <p class="font-medium text-16px text-gray">{{ $course['subtitle'] }}</p>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    @if (!empty($courseId))
                        <a href="{{ $editUrl }}"
                            class="inline-flex items-center gap-1.5 h-9 px-3 rounded-10px border border-d9 bg-white font-semibold text-13px text-primary hover:bg-[#FAFAF4] transition"
                            title="{{ $isDraft ? 'متابعة التعديل' : 'تعديل الدورة' }}">
                            <span class="icon-[tabler--pencil] size-4 text-gray shrink-0"></span>
                            {{ $isDraft ? 'متابعة التعديل' : 'تعديل' }}
                        </a>
                    @endif
                    @php
                        $courseMenuItems = [
                            ['label' => $isDraft ? 'متابعة التعديل' : 'تعديل الدورة', 'url' => $editUrl],
                        ];
                        if (!$isDraft) {
                            $courseMenuItems[] = ['label' => 'صفحة التعلم', 'url' => $watchUrl];
                            $courseMenuItems[] = ['label' => 'محتوى المحاضرات', 'url' => $watchUrl];
                            $courseMenuItems[] = ['label' => 'لوحة أداء الدورة', 'url' => $performanceUrl];
                            $courseMenuItems[] = ['label' => 'الحضور والغياب', 'url' => $assignmentsUrl];
                        }
                        if (!empty($courseId)) {
                            $courseMenuItems[] = [
                                'label' => 'حذف',
                                'action' => route('panel.v1.instructor.courses.delete', ['id' => $courseId]),
                                'confirm' => 'تعطيل هذه الدورة؟',
                                'tone' => 'danger',
                            ];
                        }
                    @endphp
                    @include('panel_v1.components.actions-dropdown', [
                        'id' => $menuId,
                        'items' => $courseMenuItems,
                        'class' => 'shrink-0',
                    ])
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-4">
        <div class="flex flex-col items-start gap-1">
            <span class="icon-[tabler--box] size-5 text-gray"></span>
            <span class="font-medium text-14px text-gray">النوع</span>
            <span class="font-semibold text-16px text-black">{{ $course['type'] }}</span>
        </div>
        <div class="flex flex-col items-start gap-1">
            <span class="icon-[tabler--chart-bar] size-5 text-gray"></span>
            <span class="font-medium text-14px text-gray">ساعات النشاط</span>
            <span class="font-semibold text-16px text-black">{{ $course['activity'] }}</span>
        </div>
        <div class="flex flex-col items-start gap-1">
            <span class="icon-[tabler--clock] size-5 text-gray"></span>
            <span class="font-medium text-14px text-gray">مدة</span>
            <span class="font-semibold text-16px text-black">{{ $course['duration'] }}</span>
        </div>
        <div class="flex flex-col items-start gap-1">
            <span class="icon-[tabler--book] size-5 text-gray"></span>
            <span class="font-medium text-14px text-gray">محاضرات</span>
            <span class="font-semibold text-16px text-black">{{ $course['lectures'] }}</span>
        </div>
        <div class="flex flex-col items-start gap-1">
            <span class="icon-[tabler--clipboard] size-5 text-gray"></span>
            <span class="font-medium text-14px text-gray">التكليفات</span>
            <span class="font-semibold text-16px text-black">{{ $course['assignments'] }}</span>
        </div>
    </div>

    <p class="font-semibold text-14px text-primary mb-1.5">{{ (int) ($course['progress'] ?? 0) }}% متوسط معدل التقدم</p>
    <div class="h-2 rounded-full bg-[#EFEFEF] overflow-hidden w-full">
        <div class="h-full bg-primary rounded-full" style="width: {{ min(100, max(0, (int) ($course['progress'] ?? 0))) }}%"></div>
    </div>
</article>
