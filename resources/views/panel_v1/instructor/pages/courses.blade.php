@extends('panel_v1.instructor.layouts.app')

@section('content')
@php $slug = $demoSlug ?? 'demo'; @endphp

<div class="space-y-6 pb-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="font-extrabold text-28px sm:text-32px text-primary mb-2">إدارة الدورات</h1>
            <p class="font-medium text-15px text-gray">إدارة دوراتك المنشورة والمسودات ومتابعة التقدم.</p>
        </div>
        <a href="#"
            class="inline-flex items-center justify-center gap-2 rounded-12px bg-[#C99C69] px-5 h-12 font-bold text-15px text-white shrink-0 hover:opacity-95 transition">
            <span class="icon-[tabler--plus] size-5"></span>
            إنشاء دورة جديدة
        </a>
    </div>

    <nav class="tabs tabs-bordered tabs-lg flex w-full overflow-x-auto border-b border-[#E8E8E8]"
        aria-label="فلتر الدورات" role="tablist">
        <button type="button"
            class="tab active justify-center whitespace-nowrap font-semibold text-16px sm:text-18px text-gray pb-4 active-tab:text-primary active-tab:border-b-color2 aria-selected:text-primary"
            id="c-tabs-1" data-tab="#c-panel-1" role="tab" aria-selected="true">الكل</button>
        <button type="button"
            class="tab justify-center whitespace-nowrap font-semibold text-16px sm:text-18px text-gray pb-4 active-tab:text-primary active-tab:border-b-color2"
            id="c-tabs-2" data-tab="#c-panel-2" role="tab" aria-selected="false">المحاضرات المباشرة</button>
        <button type="button"
            class="tab justify-center whitespace-nowrap font-semibold text-16px sm:text-18px text-gray pb-4 active-tab:text-primary active-tab:border-b-color2"
            id="c-tabs-3" data-tab="#c-panel-3" role="tab" aria-selected="false">الدورات المسجلة</button>
        <button type="button"
            class="tab justify-center whitespace-nowrap font-semibold text-16px sm:text-18px text-gray pb-4 active-tab:text-primary active-tab:border-b-color2"
            id="c-tabs-4" data-tab="#c-panel-4" role="tab" aria-selected="false">المسودات</button>
    </nav>

    <div id="c-panel-1" role="tabpanel">
        <div class="space-y-4">
            @foreach ($courseCards ?? [] as $index => $course)
                <article class="relative rounded-16px border border-d9 bg-white p-4 sm:p-5 shadow-sm">
                    <div class="flex gap-4">
                        <div class="size-16 sm:size-[72px] rounded-12px bg-primary shrink-0"></div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3 mb-2">
                                <div class="min-w-0">
                                    <h2 class="font-bold text-18px sm:text-20px text-primary mb-1">{{ $course['title'] }}</h2>
                                    <p class="font-medium text-13px text-gray">{{ $course['subtitle'] }}</p>
                                </div>

                                <div class="dropdown relative inline-flex [--auto-close:true] rtl:[--placement:bottom-end] shrink-0">
                                    <button type="button" class="dropdown-toggle btn btn-square btn-text btn-sm"
                                        aria-label="خيارات الدورة" id="course-menu-{{ $index }}">
                                        <span class="icon-[tabler--dots-vertical] size-5 text-primary"></span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-56 py-2 rounded-12px border border-d9 bg-white shadow-xl z-20"
                                        role="menu" aria-labelledby="course-menu-{{ $index }}">
                                        <li><a href="{{ route('panel.v1.instructor.courses.performance', ['slug' => $slug]) }}" class="dropdown-item px-4 py-2.5 font-medium text-14px text-primary">صفحة التعلم</a></li>
                                        <li><a href="#" class="dropdown-item px-4 py-2.5 font-medium text-14px text-primary">تعديل</a></li>
                                        <li><a href="#" class="dropdown-item px-4 py-2.5 font-medium text-14px text-primary">محتوى المحاضرات</a></li>
                                        <li><a href="{{ route('panel.v1.instructor.courses.performance', ['slug' => $slug]) }}" class="dropdown-item px-4 py-2.5 font-medium text-14px text-primary">لوحة أداء الدورة</a></li>
                                        <li><a href="{{ route('panel.v1.instructor.courses.assignments', ['slug' => $slug]) }}" class="dropdown-item px-4 py-2.5 font-medium text-14px text-primary">الحضور والغياب</a></li>
                                        <li><a href="#" class="dropdown-item px-4 py-2.5 font-medium text-14px text-[#E11D48]">حذف</a></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-x-4 gap-y-2 font-medium text-13px text-gray mb-4">
                                <span class="inline-flex items-center gap-1"><span class="icon-[tabler--tag] size-4"></span>{{ $course['type'] }}</span>
                                <span class="inline-flex items-center gap-1"><span class="icon-[tabler--clock] size-4"></span>{{ $course['activity'] }}</span>
                                <span class="inline-flex items-center gap-1"><span class="icon-[tabler--hourglass] size-4"></span>{{ $course['duration'] }}</span>
                                <span class="inline-flex items-center gap-1"><span class="icon-[tabler--files] size-4"></span>{{ $course['lectures'] }}</span>
                                <span class="inline-flex items-center gap-1"><span class="icon-[tabler--clipboard] size-4"></span>{{ $course['assignments'] }}</span>
                            </div>

                            <p class="font-medium text-12px text-black mb-2">{{ $course['progress'] }}% متوسط معدل التقدم</p>
                            <div class="h-2 rounded-full bg-[#F0EFEF] overflow-hidden max-w-md">
                                <div class="h-full bg-primary rounded-full" style="width: {{ $course['progress'] }}%"></div>
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>

    <div id="c-panel-2" class="hidden" role="tabpanel">
        @include('panel_v1.instructor.components.empty-state', ['title' => 'لا توجد محاضرات مباشرة حالياً'])
    </div>
    <div id="c-panel-3" class="hidden" role="tabpanel">
        @include('panel_v1.instructor.components.empty-state', ['title' => 'لا توجد دورات مسجلة إضافية'])
    </div>
    <div id="c-panel-4" class="hidden" role="tabpanel">
        @include('panel_v1.instructor.components.empty-state', ['title' => 'لا توجد مسودات'])
    </div>
</div>
@endsection
