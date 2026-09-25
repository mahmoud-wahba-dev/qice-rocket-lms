@php
    // Match instructor sidebar typography (text-16px links + section titles)
    $navLink = 'admin-nav-link relative flex items-center gap-3 px-3 py-3.5 rounded-10px text-e8 font-medium text-16px hover:bg-white/10 transition-colors';
    $navActive = 'bg-[#03B5A14A]';
    $navTitle = 'admin-nav-section px-3 pt-5 first:pt-1 pb-2.5 text-color2 text-16px font-medium whitespace-nowrap overflow-hidden transition-all duration-300';
    $activeKey = $adminActive ?? 'home';
    $groups = $adminNav ?? [];

    $itemIsActive = function (array $item) use ($activeKey): bool {
        if (($item['key'] ?? '') === $activeKey) {
            return true;
        }
        foreach ($item['children'] ?? [] as $child) {
            if (($child['key'] ?? '') === $activeKey) {
                return true;
            }
        }
        return false;
    };
@endphp

{{-- بسيط + أكورديون خفيف — مجموعة واحدة مفتوحة فقط لتخفيف الزحام --}}
<aside id="admin-layout-toggle"
    class="admin-dashboard-sidebar fixed inset-y-0 start-0 z-[60] h-full w-[280px] max-w-[85vw]
           -translate-x-full rtl:translate-x-full
           data-[mobile-open=true]:translate-x-0 rtl:data-[mobile-open=true]:translate-x-0
           transition-transform duration-300 ease-out
           lg:z-50 lg:translate-x-0 rtl:lg:translate-x-0 lg:transition-[width]
           !bg-primary !text-white shadow-xl lg:shadow-none"
    data-mobile-open="false"
    aria-label="قائمة الإدارة"
    tabindex="-1">

    <div class="h-full border-e border-white/10 p-0 !bg-primary !text-white">
        <div class="flex h-full max-h-full flex-col !bg-primary !text-white">
            <button type="button"
                class="btn btn-text btn-circle btn-sm absolute end-3 top-3 text-white hover:bg-white/10 lg:hidden"
                aria-label="إغلاق"
                data-admin-sidebar-close>
                <span class="icon-[tabler--x] size-5"></span>
            </button>

            <div class="admin-sidebar-brand flex items-center justify-center px-4 pt-4 pb-3 overflow-hidden shrink-0">
                <a href="{{ route('panel.v1.admin.education.home') }}" class="block shrink-0">
                    <img src="{{ ($panelAdminImg ?? asset('assets/panel_v1/img/instructor')) . '/logo.webp' }}"
                        alt="QIEC"
                        width="91"
                        height="114"
                        class="admin-sidebar-logo object-cover transition-all duration-300 brightness-0 invert"
                        decoding="async"
                        onerror="this.onerror=null;this.src='{{ asset('assets/landing_v1/logo_nav.svg') }}';this.classList.add('h-12','w-auto');">
                </a>
            </div>

            <nav class="admin-sidebar-scroll flex-1 overflow-y-auto overflow-x-hidden px-3 pb-6" aria-label="تنقل الإدارة">
                @php
                    $isGroupActive = function ($group) use ($itemIsActive) {
                        foreach (($group['items'] ?? []) as $it) {
                            if ($itemIsActive($it)) {
                                return true;
                            }
                        }
                        return false;
                    };
                @endphp
                @foreach ($groups as $idx => $group)
                    @php $open = $isGroupActive($group); @endphp
                    <div data-admin-group data-group-index="{{ $idx }}" class="mb-3">
                        <button type="button"
                            data-admin-group-toggle
                            aria-expanded="{{ $open ? 'true' : 'false' }}"
                            class="admin-nav-group-toggle w-full flex items-center justify-between gap-2 px-2 py-2.5 rounded-8px hover:bg-white/10 transition text-start">
                            <span class="{{ $navTitle }} !p-0 flex-1 text-start" data-sidebar-section>{{ $group['title'] }}</span>
                            <span data-admin-group-chevron class="admin-nav-chevron icon-[tabler--chevron-down] size-3.5 shrink-0 text-white/60 transition-transform duration-200 {{ $open ? 'rotate-180' : '' }}"></span>
                        </button>
                        <ul data-admin-group-panel class="flex flex-col gap-2 mt-2 {{ $open ? '' : 'hidden' }}">
                            @foreach ($group['items'] as $item)
                                @php
                                    $children = $item['children'] ?? [];
                                    $hasChildren = count($children) > 0;
                                    $childActive = false;
                                    foreach ($children as $ch) {
                                        if (($ch['key'] ?? '') === $activeKey) {
                                            $childActive = true;
                                            break;
                                        }
                                    }
                                    $isActive = ($item['key'] ?? '') === $activeKey;
                                    $dropdownOpen = $hasChildren && ($isActive || $childActive);
                                    $href = route($item['route'], $item['params'] ?? []);
                                @endphp
                                <li>
                                    @if ($hasChildren)
                                        <div data-admin-nav-dropdown class="rounded-12px {{ $dropdownOpen ? 'bg-white/10' : '' }} overflow-hidden">
                                            <button type="button"
                                                data-admin-nav-dropdown-toggle
                                                data-admin-nav-parent-href="{{ $href }}"
                                                aria-expanded="{{ $dropdownOpen ? 'true' : 'false' }}"
                                                class="{{ $navLink }} w-full {{ ($isActive || $childActive) ? $navActive : '' }}">
                                                <span class="{{ $item['icon'] ?? 'icon-[tabler--circle]' }} size-5 shrink-0 text-white"></span>
                                                <span class="admin-nav-label flex-1 text-start whitespace-nowrap overflow-hidden transition-all duration-300" data-sidebar-label>{{ $item['label'] }}</span>
                                                <span data-admin-nav-dropdown-chevron class="admin-nav-chevron icon-[tabler--chevron-down] size-4 shrink-0 text-white/70 transition-transform duration-200 {{ $dropdownOpen ? 'rotate-180' : '' }}"></span>
                                            </button>
                                            <ul data-admin-nav-dropdown-panel
                                                class="admin-nav-dropdown-panel relative pe-2 pb-3 ps-3 {{ $dropdownOpen ? '' : 'hidden' }}">
                                                <span class="admin-nav-dropdown-rail pointer-events-none absolute top-1 bottom-3 start-6 w-px bg-white/35" aria-hidden="true"></span>
                                                @foreach ($children as $child)
                                                    @php
                                                        $childHref = route($child['route'], $child['params'] ?? []);
                                                        $isChildActive = ($child['key'] ?? '') === $activeKey;
                                                        $childLabel = $child['label'];
                                                        if (isset($child['count'])) {
                                                            $childLabel .= ' ('.$child['count'].')';
                                                        }
                                                    @endphp
                                                    <li>
                                                        <a href="{{ $childHref }}"
                                                            data-tooltip="{{ $childLabel }}"
                                                            aria-label="{{ $childLabel }}"
                                                            class="admin-nav-child-link relative flex items-center gap-2 ps-9 pe-3 py-2.5 rounded-10px font-medium text-15px text-white/90 hover:bg-white/10 transition-colors {{ $isChildActive ? 'bg-white/15 text-white' : '' }}">
                                                            <span class="admin-nav-label whitespace-nowrap overflow-hidden" data-sidebar-label>{{ $childLabel }}</span>
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @else
                                        <a href="{{ $href }}"
                                            data-tooltip="{{ $item['label'] }}"
                                            aria-label="{{ $item['label'] }}"
                                            class="{{ $navLink }} {{ $isActive ? $navActive : '' }}">
                                            <span class="{{ $item['icon'] ?? 'icon-[tabler--circle]' }} size-5 shrink-0 text-white"></span>
                                            <span class="admin-nav-label whitespace-nowrap overflow-hidden transition-all duration-300" data-sidebar-label>{{ $item['label'] }}</span>
                                        </a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach

                {{-- عام — ثابت بدون أكورديون (بدون اللوحة القديمة) --}}
                <div class="mt-5 pt-5 border-t border-white/10">
                    <p class="{{ $navTitle }}" data-sidebar-section>عام</p>
                    <ul class="flex flex-col gap-2 mt-2 mb-8">
                        <li>
                            <a href="/logout" data-tooltip="تسجيل الخروج" aria-label="تسجيل الخروج" class="{{ $navLink }} text-[#F87171]">
                                <span class="icon-[tabler--logout] size-5 shrink-0 text-[#EF4444]"></span>
                                <span class="admin-nav-label" data-sidebar-label>تسجيل الخروج</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </div>
</aside>
