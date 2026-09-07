@extends('panel_v1.instructor.layouts.app')

@section('content')
@php $slug = $demoSlug ?? 'demo'; @endphp

<div class="space-y-6 pb-8">
    @component('panel_v1.instructor.components.page-header', [
        'title' => 'إدارة الدورات',
        'subtitle' => 'إليك ملخص أداء دوراتك، نشاط الطلاب، والأرباح لهذا اليوم.',
    ])
        @slot('actions')
            <a href="{{ route('panel.v1.instructor.courses.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-12px bg-color2 px-5 h-12 font-semibold text-16px text-white shrink-0 hover:opacity-95 transition">
                <span class="icon-[tabler--plus] size-5"></span>
                إنشاء دورة جديدة
            </a>
        @endslot
    @endcomponent

    <nav class="tabs tabs-bordered flex w-full overflow-x-auto border-b border-d9"
        aria-label="فلتر الدورات" role="tablist">
        <button type="button"
            class="tab active justify-center whitespace-nowrap font-semibold text-16px sm:text-18px text-gray pb-4 active-tab:text-primary active-tab:border-b-primary"
            id="c-tabs-1" data-tab="#c-panel-1" role="tab" aria-selected="true">الكل</button>
        <button type="button"
            class="tab justify-center whitespace-nowrap font-semibold text-16px sm:text-18px text-gray pb-4 active-tab:text-primary active-tab:border-b-primary"
            id="c-tabs-2" data-tab="#c-panel-2" role="tab" aria-selected="false">المحاضرات المباشرة</button>
        <button type="button"
            class="tab justify-center whitespace-nowrap font-semibold text-16px sm:text-18px text-gray pb-4 active-tab:text-primary active-tab:border-b-primary"
            id="c-tabs-3" data-tab="#c-panel-3" role="tab" aria-selected="false">الدورات المسجلة</button>
        <button type="button"
            class="tab justify-center whitespace-nowrap font-semibold text-16px sm:text-18px text-gray pb-4 active-tab:text-primary active-tab:border-b-primary"
            id="c-tabs-4" data-tab="#c-panel-4" role="tab" aria-selected="false">المسودات</button>
    </nav>
    <div id="c-panel-1" role="tabpanel">
        <div class="space-y-3">
            @foreach ($courseCards ?? [] as $index => $course)
                <article class="rounded-14px border border-d9 bg-white p-4 sm:p-5">
                    <div class="flex gap-3 sm:gap-4 items-start mb-4">
                        <div class="size-14 sm:size-16 rounded-12px bg-primary shrink-0"></div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h2 class="font-semibold text-24px text-primary mb-0.5 leading-snug">{{ $course['title'] }}</h2>
                                    <p class="font-medium text-16px text-gray">{{ $course['subtitle'] }}</p>
                                </div>

                                <div class="dropdown relative inline-flex [--auto-close:true] rtl:[--placement:bottom-end] shrink-0">
                                    <button type="button"
                                        class="dropdown-toggle size-9 rounded-full bg-[#F1F5F9] !inline-flex !items-center !justify-center border-0 hover:bg-[#E8ECEA] transition"
                                        aria-label="خيارات الدورة" id="course-menu-{{ $index }}">
                                        <span class="icon-[tabler--dots] size-5 text-gray"></span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-56 py-2 rounded-12px border border-d9 bg-white shadow-xl z-20"
                                        role="menu" aria-labelledby="course-menu-{{ $index }}">
                                        <li>
                                            <a href="{{ route('panel.v1.instructor.courses.watch', ['slug' => $slug]) }}"
                                                class="dropdown-item flex items-center gap-2 px-4 py-2.5 font-medium text-15px text-primary">
                                                <span class="icon-[tabler--minus] size-3.5 text-gray shrink-0"></span>
                                                صفحة التعلم
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#"
                                                class="dropdown-item flex items-center gap-2 px-4 py-2.5 font-medium text-15px text-primary">
                                                <span class="icon-[tabler--minus] size-3.5 text-gray shrink-0"></span>
                                                تعديل
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('panel.v1.instructor.courses.watch', ['slug' => $slug]) }}"
                                                class="dropdown-item flex items-center gap-2 px-4 py-2.5 font-medium text-15px text-primary">
                                                <span class="icon-[tabler--minus] size-3.5 text-gray shrink-0"></span>
                                                محتوى المحاضرات
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('panel.v1.instructor.courses.performance', ['slug' => $slug]) }}"
                                                class="dropdown-item flex items-center gap-2 px-4 py-2.5 font-medium text-15px text-primary">
                                                <span class="icon-[tabler--minus] size-3.5 text-gray shrink-0"></span>
                                                لوحة أداء الدورة
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('panel.v1.instructor.courses.assignments', ['slug' => $slug]) }}"
                                                class="dropdown-item flex items-center gap-2 px-4 py-2.5 font-medium text-15px text-primary">
                                                <span class="icon-[tabler--minus] size-3.5 text-gray shrink-0"></span>
                                                الحضور والغياب
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#"
                                                class="dropdown-item flex items-center gap-2 px-4 py-2.5 font-medium text-15px text-[#E11D48]">
                                                <span class="icon-[tabler--minus] size-3.5 text-[#E11D48] shrink-0"></span>
                                                حذف
                                            </a>
                                        </li>
                                    </ul>
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

                    <p class="font-semibold text-14px text-primary mb-1.5">{{ $course['progress'] }}% متوسط معدل التقدم</p>
                    <div class="h-2 rounded-full bg-[#EFEFEF] overflow-hidden w-full">
                        <div class="h-full bg-primary rounded-full" style="width: {{ $course['progress'] }}%"></div>
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
