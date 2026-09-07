@php
    $courseSlug = $slug ?? ($demoSlug ?? 'demo');
@endphp

<header class="sticky top-0 z-50 bg-primary text-white shadow-sm">
    <div class="flex items-center justify-between gap-3 px-4 sm:px-6 lg:px-8 h-[4.5rem]">
        <div class="flex items-center gap-2 sm:gap-4 min-w-0">
            <button type="button"
                class="lg:hidden btn btn-text btn-square text-white hover:bg-white/10"
                data-instructor-course-sidebar-toggle aria-label="قائمة المحتوى">
                <span class="icon-[tabler--menu-2] size-6"></span>
            </button>
            <a href="{{ route('panel.v1.instructor.home') }}" class="shrink-0">
                <img src="{{ asset('assets/landing_v1/logo_nav.svg') }}" alt="QIEC"
                    class="h-10 sm:h-12 brightness-0 invert" decoding="async">
            </a>
            @if (!empty($course['title']))
                <div class="hidden md:block min-w-0 border-s border-white/20 ps-4">
                    <p class="font-bold text-15px lg:text-16px truncate leading-snug">{{ $course['title'] }}</p>
                    @if (!empty($course['subtitle']))
                        <p class="font-medium text-12px text-white/75 truncate">{{ $course['subtitle'] }}</p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-2 sm:gap-4">
            <div class="dropdown relative inline-flex [--auto-close:true] rtl:[--placement:bottom]">
                <button type="button"
                    class="dropdown-toggle inline-flex items-center gap-2 font-semibold text-14px text-white hover:bg-white/10 rounded-10px px-2 sm:px-3 py-2">
                    <span class="icon-[tabler--pencil] size-5"></span>
                    <span class="max-sm:hidden">أدوات الدورة</span>
                    <span class="icon-[tabler--chevron-down] size-4 opacity-80"></span>
                </button>
                <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-56 py-2 rounded-14px border border-d9 bg-white shadow-xl z-[60]">
                    <li>
                        <a href="{{ route('panel.v1.instructor.courses.performance', ['slug' => $courseSlug]) }}"
                            class="dropdown-item px-4 py-2.5 font-medium text-14px text-primary inline-flex items-center gap-2">
                            <span class="icon-[tabler--external-link] size-4"></span>
                            صفحة الدورة
                        </a>
                    </li>
                    <li>
                        <a href="#" class="dropdown-item px-4 py-2.5 font-medium text-14px text-primary inline-flex items-center gap-2">
                            <span class="icon-[tabler--bell] size-4"></span>
                            منتدى الدورة
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('panel.v1.instructor.settings') }}"
                            class="dropdown-item px-4 py-2.5 font-medium text-14px text-primary inline-flex items-center gap-2">
                            <span class="icon-[tabler--user] size-4"></span>
                            ملف المدرب
                        </a>
                    </li>
                    <li>
                        <a href="#" class="dropdown-item px-4 py-2.5 font-medium text-14px text-primary inline-flex items-center gap-2">
                            <span class="icon-[tabler--bell-plus] size-4"></span>
                            اضافة تاريخ تذكير
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('panel.v1.instructor.support') }}"
                            class="dropdown-item px-4 py-2.5 font-medium text-14px text-primary inline-flex items-center gap-2">
                            <span class="icon-[tabler--help-circle] size-4"></span>
                            احصل على مساعدة
                        </a>
                    </li>
                </ul>
            </div>

            <button type="button"
                class="inline-flex items-center gap-1.5 rounded-10px border border-white/25 bg-white/5 px-2 sm:px-3 py-1.5 font-semibold text-13px sm:text-14px text-white/90 cursor-default">
                <span class="icon-[tabler--lock] size-4"></span>
                الشهادة
            </button>

            @include('panel_v1.instructor.components.profile-dropdown', ['profileOnDark' => true])
        </div>
    </div>
</header>
