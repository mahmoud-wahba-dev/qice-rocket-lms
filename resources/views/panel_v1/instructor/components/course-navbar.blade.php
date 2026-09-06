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
                    <li><a href="{{ route('panel.v1.instructor.courses') }}" class="dropdown-item px-4 py-2.5 font-medium text-14px text-primary">صفحة الدورة</a></li>
                    <li><a href="#" class="dropdown-item px-4 py-2.5 font-medium text-14px text-primary">منتدى الدورة</a></li>
                    <li><a href="#" class="dropdown-item px-4 py-2.5 font-medium text-14px text-primary">احصل على مساعدة</a></li>
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
