@php
    $is = fn (string $pattern) => request()->routeIs($pattern);
    $navLink = 'flex items-center gap-3 px-3 py-2.5 rounded-10px text-e8 font-medium text-16px hover:bg-white/10 transition';
    $navActive = 'bg-[#03B5A14A]';
    $navTitle = 'px-3 pt-4 first:pt-1 pb-2 text-color2 text-16px font-medium ';
@endphp

{{-- FlyonUI overlay drawer: start = right in RTL; visible from lg via Tailwind --}}
<aside id="instructor-layout-toggle"
    class="overlay overlay-open:translate-x-0 drawer drawer-start inset-y-0 start-0 end-auto hidden h-full w-[280px] max-w-[85vw]
           [--auto-close:lg] [--overlay-backdrop:true]
           lg:z-50 lg:!block lg:!translate-x-0 lg:!shadow-none
           !bg-primary !text-white"
    aria-label="قائمة المدرب"
    tabindex="-1">

    <div class="drawer-body !bg-primary h-full border-e border-white/10 p-0 !text-white">
        <div class="flex h-full max-h-full flex-col !bg-primary !text-white">
            <button type="button"
                class="btn btn-text btn-circle btn-sm absolute end-3 top-3 text-white hover:bg-white/10 lg:hidden"
                aria-label="إغلاق"
                data-overlay="#instructor-layout-toggle">
                <span class="icon-[tabler--x] size-5"></span>
            </button>

            {{-- Brand --}}
            <div class="flex items-center px-4 pt-4 pb-2">
                <a href="{{ route('panel.v1.instructor.home') }}" class="block">
                    <img src="{{ ($panelInstructorImg ?? asset('assets/panel_v1/img/instructor')) . '/logo.webp' }}"
                        alt="QIEC"
                        width="91"
                        height="114"
                        class=" object-cover"
                        decoding="async">
                </a>
            </div>

            {{-- Nav (plain list — no FlyonUI .menu so Tailwind owns colors) --}}
            <nav class="instructor-sidebar-scroll h-full overflow-y-auto px-3 py-4" aria-label="تنقل المدرب">
                <p class="{{ $navTitle }}">الرئيسية</p>
                <ul class="flex flex-col gap-1 mb-8">
                    <li>
                        <a href="{{ route('panel.v1.instructor.home') }}"
                            class="{{ $navLink }} {{ $is('panel.v1.instructor.home') ? $navActive : '' }}">
                            <span class="icon-[tabler--layout-dashboard] size-5 shrink-0 text-white"></span>
                            لوحة التحكم
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('panel.v1.instructor.courses') }}"
                            class="{{ $navLink }} {{ $is('panel.v1.instructor.courses') || $is('panel.v1.instructor.courses.*') ? $navActive : '' }}">
                            <span class="icon-[tabler--book] size-5 shrink-0 text-white"></span>
                            الدورات
                        </a>
                    </li>
                    <li>
                        <a href="#" class="{{ $navLink }}">
                            <span class="icon-[tabler--package] size-5 shrink-0 text-white"></span>
                            حزم الدورات والباقات
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('panel.v1.instructor.consultations') }}"
                            class="{{ $navLink }} {{ $is('panel.v1.instructor.consultations') ? $navActive : '' }}">
                            <span class="icon-[tabler--video] size-5 shrink-0 text-white"></span>
                            الجلسات الاستشارية
                        </a>
                    </li>
                    <li>
                        <a href="#" class="{{ $navLink }}">
                            <span class="icon-[tabler--calendar] size-5 shrink-0 text-white"></span>
                            تقويم الأحداث
                        </a>
                    </li>
                </ul>

                <p class="{{ $navTitle }}">إدارة التعليم والطلاب</p>
                <ul class="flex flex-col gap-1 mb-8">
                    <li>
                        <a href="{{ route('panel.v1.instructor.assignments') }}"
                            class="{{ $navLink }} {{ $is('panel.v1.instructor.assignments') || $is('panel.v1.instructor.assignments.review') ? $navActive : '' }}">
                            <span class="icon-[tabler--clipboard-list] size-5 shrink-0 text-white"></span>
                            التكليفات
                        </a>
                    </li>
                    <li>
                        <a href="#" class="{{ $navLink }}">
                            <span class="icon-[tabler--file-check] size-5 shrink-0 text-white"></span>
                            الاختبارات
                        </a>
                    </li>
                    <li>
                        <a href="#" class="{{ $navLink }}">
                            <span class="icon-[tabler--message] size-5 shrink-0 text-white"></span>
                            تعليقات الدورات
                        </a>
                    </li>
                    <li>
                        <a href="#" class="{{ $navLink }}">
                            <span class="icon-[tabler--users] size-5 shrink-0 text-white"></span>
                            قائمة الطلاب
                        </a>
                    </li>
                    <li>
                        <a href="#" class="{{ $navLink }}">
                            <span class="icon-[tabler--certificate] size-5 shrink-0 text-white"></span>
                            الشهادات
                        </a>
                    </li>
                </ul>

                <p class="{{ $navTitle }}">قسم المالية والتسويق</p>
                <ul class="flex flex-col gap-1 mb-8">
                    <li>
                        <a href="#" class="{{ $navLink }}">
                            <span class="icon-[tabler--wallet] size-5 shrink-0 text-white"></span>
                            المحفظة والمبيعات
                        </a>
                    </li>
                    <li>
                        <a href="#" class="{{ $navLink }}">
                            <span class="icon-[tabler--cash] size-5 shrink-0 text-white"></span>
                            المستحقات والسحب
                        </a>
                    </li>
                    <li>
                        <a href="#" class="{{ $navLink }}">
                            <span class="icon-[tabler--ticket] size-5 shrink-0 text-white"></span>
                            التسويق والقسائم
                        </a>
                    </li>
                </ul>

                <p class="{{ $navTitle }}">قسم الإعدادات والدعم</p>
                <ul class="flex flex-col gap-1 mb-8" >
                    <li>
                        <a href="#" class="{{ $navLink }}">
                            <span class="icon-[tabler--settings] size-5 shrink-0 text-white"></span>
                            الإعدادات
                        </a>
                    </li>
                    <li>
                        <a href="#" class="{{ $navLink }}">
                            <span class="icon-[tabler--help-circle] size-5 shrink-0 text-white"></span>
                            المساعدة والدعم
                        </a>
                    </li>
                    <li>
                        <a href="/logout"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-10px font-medium text-18px text-[#F87171] hover:bg-white/10 transition">
                            <span class="icon-[tabler--logout] size-5 shrink-0 text-[#EF4444]"></span>
                            تسجيل الخروج
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</aside>
