@php
    $authUser = $authUser ?? auth()->user();
    $userName = $authUser->full_name ?? ($authUser->name ?? 'علا محمد');
    $current = $adminCurrentDashboard ?? ['label' => 'لوحة الإدارة', 'icon' => 'icon-[tabler--layout-dashboard]'];
    $dashboards = $adminDashboards ?? [];
    $cta = $adminCta ?? null;
@endphp

<nav class="navbar min-h-14 py-0 px-2 sm:px-4 bg-white items-center gap-2">
    <div class="navbar-start gap-2 !flex !items-center">
        <button type="button"
            class="btn btn-soft btn-square btn-sm lg:hidden text-primary !inline-flex !items-center !justify-center"
            aria-haspopup="dialog"
            aria-expanded="false"
            aria-controls="admin-layout-toggle"
            data-overlay="#admin-layout-toggle">
            <span class="icon-[tabler--menu-2] size-5"></span>
        </button>

        <div class="dropdown relative inline-flex [--auto-close:true] rtl:[--placement:bottom-start]">
            <button type="button"
                class="dropdown-toggle inline-flex items-center gap-2 h-11 px-3 sm:px-4 rounded-12px bg-primary text-white font-semibold text-14px sm:text-15px hover:opacity-95 transition">
                <span class="{{ $current['icon'] ?? 'icon-[tabler--layout-dashboard]' }} size-5 shrink-0"></span>
                <span class="max-w-[11rem] sm:max-w-none truncate">{{ $current['label'] ?? '' }}</span>
                <span class="icon-[tabler--chevron-down] size-4 opacity-80"></span>
            </button>
            <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-64 py-2 rounded-14px border border-d9 bg-white shadow-xl z-[70]">
                @foreach ($dashboards as $dash)
                    <li>
                        <a href="{{ route($dash['route']) }}"
                            class="dropdown-item px-4 py-2.5 font-medium text-14px text-primary inline-flex items-center gap-2
                                {{ ($dash['key'] ?? '') === ($adminDashboard ?? '') ? 'bg-primary/5' : '' }}">
                            <span class="{{ $dash['icon'] }} size-5 shrink-0"></span>
                            {{ $dash['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="navbar-end gap-1 sm:gap-2 !flex !items-center">
        @if (!empty($cta))
            <a href="{{ $cta['href'] }}"
                class="hidden sm:inline-flex items-center justify-center h-11 px-4 rounded-12px bg-color2 text-white font-semibold text-14px hover:opacity-95 transition">
                {{ $cta['label'] }}
            </a>
        @endif

        <button type="button" class="btn btn-text btn-square btn-sm text-primary" aria-label="اللغة">
            <span class="icon-[tabler--world] size-5"></span>
        </button>
        <button type="button" class="btn btn-text btn-square btn-sm text-primary" aria-label="المفضلة">
            <span class="icon-[tabler--heart] size-5"></span>
        </button>
        <button type="button" class="btn btn-text btn-square btn-sm text-primary" aria-label="الإشعارات">
            <span class="icon-[tabler--bell] size-5"></span>
        </button>

        <div class="dropdown relative inline-flex [--auto-close:true] rtl:[--placement:bottom-end]">
            <button type="button"
                class="dropdown-toggle inline-flex items-center gap-2 rounded-full pe-1 ps-1 py-1 hover:bg-[#F5F5F0] transition">
                <span class="size-9 rounded-full bg-primary/10 center font-bold text-14px text-primary">
                    {{ mb_substr($userName, 0, 1) }}
                </span>
                <span class="hidden md:inline font-semibold text-14px text-primary">{{ $userName }}</span>
                <span class="icon-[tabler--chevron-down] size-4 text-primary/70"></span>
            </button>
            <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-44 py-2 rounded-12px border border-d9 bg-white shadow-xl z-[70]">
                <li><a href="/logout" class="dropdown-item px-4 py-2.5 font-medium text-14px text-red-500">تسجيل الخروج</a></li>
            </ul>
        </div>
    </div>
</nav>
