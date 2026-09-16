@php
    $is = fn (string $pattern) => request()->routeIs($pattern);
    $navLink = 'instructor-nav-link relative flex items-center gap-3 px-3 py-2.5 rounded-10px text-e8 font-medium text-16px hover:bg-white/10 transition-colors';
    $navActive = 'bg-[#03B5A14A]';
    $navTitle = 'instructor-nav-section px-3 pt-4 first:pt-1 pb-2 text-color2 text-16px font-medium whitespace-nowrap overflow-hidden transition-all duration-300';

    // Full static icon-[...] classes required so @iconify/tailwind can scan them
    $navSections = [
        [
            'title' => 'الرئيسية',
            'items' => [
                ['label' => 'لوحة التحكم', 'icon' => 'icon-[tabler--layout-dashboard]', 'route' => 'panel.v1.instructor.home', 'active' => $is('panel.v1.instructor.home')],
                ['label' => 'الدورات', 'icon' => 'icon-[tabler--book]', 'route' => 'panel.v1.instructor.courses', 'active' => $is('panel.v1.instructor.courses') || $is('panel.v1.instructor.courses.*')],
                ['label' => 'حزم الدورات والباقات', 'icon' => 'icon-[tabler--package]', 'route' => 'panel.v1.instructor.bundles', 'active' => $is('panel.v1.instructor.bundles') || $is('panel.v1.instructor.bundles.*')],
                ['label' => 'الجلسات الاستشارية', 'icon' => 'icon-[tabler--video]', 'route' => 'panel.v1.instructor.consultations', 'active' => $is('panel.v1.instructor.consultations')],
                ['label' => 'تقويم الأحداث', 'icon' => 'icon-[tabler--calendar]', 'href' => '#', 'active' => false],
            ],
        ],
        [
            'title' => 'إدارة التعليم والطلاب',
            'items' => [
                ['label' => 'التكليفات', 'icon' => 'icon-[tabler--clipboard-list]', 'route' => 'panel.v1.instructor.assignments', 'active' => $is('panel.v1.instructor.assignments') || $is('panel.v1.instructor.assignments.review')],
                ['label' => 'الاختبارات', 'icon' => 'icon-[tabler--file-check]', 'route' => 'panel.v1.instructor.quizzes', 'active' => $is('panel.v1.instructor.quizzes') || $is('panel.v1.instructor.quizzes.*')],
                ['label' => 'تعليقات الدورات', 'icon' => 'icon-[tabler--message]', 'href' => '#', 'active' => false],
                ['label' => 'قائمة الطلاب', 'icon' => 'icon-[tabler--users]', 'route' => 'panel.v1.instructor.students', 'active' => $is('panel.v1.instructor.students')],
                ['label' => 'الشهادات', 'icon' => 'icon-[tabler--certificate]', 'route' => 'panel.v1.instructor.certificates', 'active' => $is('panel.v1.instructor.certificates')],
            ],
        ],
        [
            'title' => 'قسم المالية والتسويق',
            'items' => [
                ['label' => 'المحفظة والمبيعات', 'icon' => 'icon-[tabler--wallet]', 'route' => 'panel.v1.instructor.finance', 'active' => $is('panel.v1.instructor.finance')],
                ['label' => 'المستحقات والسحب', 'icon' => 'icon-[tabler--cash]', 'route' => 'panel.v1.instructor.payouts', 'active' => $is('panel.v1.instructor.payouts')],
                ['label' => 'التسويق والقسائم', 'icon' => 'icon-[tabler--ticket]', 'route' => 'panel.v1.instructor.marketing', 'active' => $is('panel.v1.instructor.marketing')],
            ],
        ],
        [
            'title' => 'قسم الإعدادات والدعم',
            'items' => [
                ['label' => 'الإعدادات', 'icon' => 'icon-[tabler--settings]', 'route' => 'panel.v1.instructor.settings', 'active' => $is('panel.v1.instructor.settings')],
                ['label' => 'المساعدة والدعم', 'icon' => 'icon-[tabler--help-circle]', 'route' => 'panel.v1.instructor.support', 'active' => $is('panel.v1.instructor.support')],
                ['label' => 'اللوحة القديمة', 'icon' => 'icon-[tabler--layout-dashboard]', 'href' => '/panel', 'active' => false],
                ['label' => 'تسجيل الخروج', 'icon' => 'icon-[tabler--logout]', 'href' => '/logout', 'active' => false, 'danger' => true],
            ],
        ],
    ];
@endphp

{{-- Mobile: off-canvas drawer with slide animation. Desktop (lg+): fixed sidebar. --}}
<aside id="instructor-layout-toggle"
    class="instructor-dashboard-sidebar fixed inset-y-0 start-0 z-[60] h-full w-[280px] max-w-[85vw]
           -translate-x-full rtl:translate-x-full
           data-[mobile-open=true]:translate-x-0 rtl:data-[mobile-open=true]:translate-x-0
           transition-transform duration-300 ease-out
           lg:z-50 lg:translate-x-0 rtl:lg:translate-x-0 lg:transition-[width]
           !bg-primary !text-white shadow-xl lg:shadow-none"
    data-mobile-open="false"
    aria-label="قائمة المدرب"
    tabindex="-1">

    <div class="h-full border-e border-white/10 p-0 !bg-primary !text-white">
        <div class="flex h-full max-h-full flex-col !bg-primary !text-white">
            <button type="button"
                class="btn btn-text btn-circle btn-sm absolute end-3 top-3 text-white hover:bg-white/10 lg:hidden"
                aria-label="إغلاق"
                data-instructor-sidebar-close>
                <span class="icon-[tabler--x] size-5"></span>
            </button>

            <div class="instructor-sidebar-brand flex items-center justify-center px-4 pt-4 pb-2 overflow-hidden">
                <a href="{{ route('panel.v1.instructor.home') }}" class="block shrink-0">
                    <img src="{{ ($panelInstructorImg ?? asset('assets/panel_v1/img/instructor')) . '/logo.webp' }}"
                        alt="QIEC"
                        width="91"
                        height="114"
                        class="instructor-sidebar-logo object-cover transition-all duration-300"
                        decoding="async">
                </a>
            </div>

            <nav class="instructor-sidebar-scroll h-full overflow-y-auto overflow-x-hidden px-3 py-4" aria-label="تنقل المدرب">
                @foreach ($navSections as $section)
                    <p class="{{ $navTitle }}" data-sidebar-section>{{ $section['title'] }}</p>
                    <ul class="flex flex-col gap-1 mb-8">
                        @foreach ($section['items'] as $item)
                            @php
                                $href = !empty($item['route']) ? route($item['route']) : ($item['href'] ?? '#');
                                $danger = !empty($item['danger']);
                            @endphp
                            <li>
                                <a href="{{ $href }}"
                                    data-tooltip="{{ $item['label'] }}"
                                    aria-label="{{ $item['label'] }}"
                                    class="{{ $navLink }} {{ !empty($item['active']) ? $navActive : '' }} {{ $danger ? 'text-[#F87171]' : '' }}">
                                    <span class="{{ $item['icon'] }} size-5 shrink-0 {{ $danger ? 'text-[#EF4444]' : 'text-white' }}"></span>
                                    <span class="instructor-nav-label whitespace-nowrap overflow-hidden transition-all duration-300" data-sidebar-label>
                                        {{ $item['label'] }}
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endforeach
            </nav>
        </div>
    </div>
</aside>
