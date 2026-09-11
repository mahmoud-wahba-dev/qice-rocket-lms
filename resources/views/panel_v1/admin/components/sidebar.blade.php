@php
    $navLink = 'flex items-center gap-3 px-3 py-2.5 rounded-10px text-white/90 font-medium text-15px hover:bg-white/10 transition';
    $navActive = 'bg-[#03B5A14A] text-white';
    $navTitle = 'px-3 pt-4 first:pt-1 pb-2 text-color2 text-14px font-medium';
    $activeKey = $adminActive ?? 'home';
    $groups = $adminNav ?? [];
@endphp

<aside id="admin-layout-toggle"
    class="overlay overlay-open:translate-x-0 drawer drawer-start inset-y-0 start-0 end-auto hidden h-full w-[280px] max-w-[85vw]
           [--auto-close:lg] [--overlay-backdrop:true]
           lg:z-50 lg:!block lg:!translate-x-0 lg:!shadow-none
           !bg-primary !text-white"
    aria-label="قائمة الإدارة"
    tabindex="-1">

    <div class="drawer-body !bg-primary h-full border-e border-white/10 p-0 !text-white">
        <div class="flex h-full max-h-full flex-col !bg-primary !text-white">
            <button type="button"
                class="btn btn-text btn-circle btn-sm absolute end-3 top-3 text-white hover:bg-white/10 lg:hidden"
                aria-label="إغلاق"
                data-overlay="#admin-layout-toggle">
                <span class="icon-[tabler--x] size-5"></span>
            </button>

            <div class="flex items-center px-4 pt-4 pb-3">
                <a href="{{ route('panel.v1.admin.education.home') }}" class="block">
                    <img src="{{ asset('assets/landing_v1/logo_nav.svg') }}"
                        alt="QIEC"
                        class="h-12 brightness-0 invert"
                        decoding="async">
                </a>
            </div>

            <nav class="admin-sidebar-scroll h-full overflow-y-auto px-3 py-3 pb-8" aria-label="تنقل الإدارة">
                @foreach ($groups as $group)
                    <p class="{{ $navTitle }}">{{ $group['title'] }}</p>
                    <ul class="flex flex-col gap-1 mb-5">
                        @foreach ($group['items'] as $item)
                            @php
                                $href = route($item['route'], $item['params'] ?? []);
                                $isActive = ($item['key'] ?? '') === $activeKey;
                            @endphp
                            <li>
                                <a href="{{ $href }}"
                                    class="{{ $navLink }} {{ $isActive ? $navActive : '' }}">
                                    <span class="{{ $item['icon'] ?? 'icon-[tabler--circle]' }} size-5 shrink-0 text-white"></span>
                                    {{ $item['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endforeach
            </nav>
        </div>
    </div>
</aside>
