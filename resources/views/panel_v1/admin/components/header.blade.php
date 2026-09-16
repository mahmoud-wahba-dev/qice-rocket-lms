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
            class="btn btn-soft btn-square btn-sm text-primary !inline-flex !items-center !justify-center"
            data-admin-sidebar-toggle
            aria-controls="admin-layout-toggle"
            aria-expanded="false"
            aria-label="فتح القائمة الجانبية"
            title="القائمة الجانبية">
            <span class="icon-[tabler--layout-sidebar-right-collapse] size-5" data-sidebar-icon="collapse"></span>
            <span class="icon-[tabler--layout-sidebar-right-expand] size-5 hidden" data-sidebar-icon="expand"></span>
        </button>

        <div class="relative inline-flex shrink-0" data-admin-menu>
            <button type="button"
                data-admin-menu-toggle
                aria-haspopup="menu" aria-expanded="false"
                class="inline-flex items-center gap-2 h-11 px-3 sm:px-4 rounded-12px bg-primary text-white font-semibold text-14px sm:text-15px hover:opacity-95 transition whitespace-nowrap">
                <span class="{{ $current['icon'] ?? 'icon-[tabler--layout-dashboard]' }} size-5 shrink-0"></span>
                <span class="whitespace-nowrap">{{ $current['label'] ?? '' }}</span>
                <span class="icon-[tabler--chevron-down] size-4 opacity-80 transition-transform duration-200" data-admin-menu-chevron></span>
            </button>
            <ul data-admin-menu-panel hidden
                class="absolute top-[calc(100%+8px)] start-0 min-w-64 py-2 rounded-14px border border-d9 bg-white shadow-xl z-[80]"
                role="menu">
                @foreach ($dashboards as $dash)
                    <li role="none">
                        <a href="{{ route($dash['route']) }}" role="menuitem"
                            class="dropdown-item px-4 py-2.5 font-medium text-14px text-primary inline-flex items-center gap-2 whitespace-nowrap w-full
                                {{ ($dash['key'] ?? '') === ($adminDashboard ?? '') ? 'bg-primary/5 font-bold' : '' }}">
                            <span class="{{ $dash['icon'] }} size-5 shrink-0"></span>
                            {{ $dash['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="navbar-end gap-0.5 sm:gap-1 !flex !items-center">
        @if (!empty($cta))
            <a href="{{ $cta['href'] }}"
                class="hidden sm:inline-flex items-center justify-center h-11 px-4 rounded-12px bg-color2 text-white font-semibold text-14px hover:opacity-95 transition">
                {{ $cta['label'] }}
            </a>
        @endif

        <div class="relative inline-flex shrink-0" data-admin-menu>
            <button type="button" data-admin-menu-toggle
                class="btn btn-text btn-square btn-sm text-primary !inline-flex !items-center !justify-center"
                aria-haspopup="menu" aria-expanded="false" aria-label="اللغة">
                <span class="icon-[tabler--world] size-5"></span>
            </button>
            <div data-admin-menu-panel hidden
                class="absolute top-[calc(100%+8px)] end-0 min-w-44 py-2 rounded-12px border border-d9 bg-white shadow-xl z-[80]"
                role="menu">
                <form method="POST" action="{{ route('appLocaleRoute') }}">
                    @csrf
                    <input type="hidden" name="previous_url" value="{{ url()->current() }}">
                    @foreach (($generalSettings['user_languages'] ?? [app()->getLocale()]) as $locale)
                        <button type="submit" name="locale" value="{{ $locale }}"
                            class="dropdown-item px-4 py-2.5 font-medium text-14px text-primary w-full text-start hover:bg-[#FAFAF4] {{ mb_strtolower($locale) === mb_strtolower(app()->getLocale()) ? 'bg-primary/5 font-bold' : '' }}">
                            {{ mb_strtoupper($locale) }}
                        </button>
                    @endforeach
                </form>
            </div>
        </div>

        @php
            $navbarNotifications = collect($unReadNotifications ?? []);
            $unreadCount = $navbarNotifications->count();
        @endphp
        <div class="relative inline-flex shrink-0" data-admin-menu>
            <button type="button" data-admin-menu-toggle
                class="btn btn-text btn-square btn-sm text-primary !inline-flex !items-center !justify-center relative"
                aria-haspopup="menu" aria-expanded="false" aria-label="الإشعارات">
                <span class="icon-[tabler--bell] size-5"></span>
                @if ($unreadCount > 0)
                    <span class="absolute -top-0.5 -start-0.5 flex items-center justify-center min-w-4.5 h-4.5 px-1 rounded-full bg-[#EF4444] text-white text-[10px] font-bold leading-none">
                        {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                    </span>
                @endif
            </button>
            <div data-admin-menu-panel hidden
                class="absolute top-[calc(100%+8px)] end-0 w-[22rem] max-w-[calc(100vw-2rem)] p-0 rounded-16px border border-d9 shadow-[0_12px_40px_rgba(15,76,69,0.12)] bg-white overflow-hidden z-[80]"
                role="menu">
                <div class="flex items-center justify-between gap-3 px-4 py-3 border-b border-d9 bg-fa/60">
                    <div>
                        <p class="font-bold text-15px text-primary">الإشعارات</p>
                        @if ($unreadCount > 0)
                            <p class="font-medium text-12px text-gray">{{ $unreadCount }} غير مقروء</p>
                        @endif
                    </div>
                    @if ($unreadCount > 0)
                        <form method="POST" action="{{ route('panel.v1.admin.system.notifications.mark-all-read') }}">
                            @csrf
                            <button type="submit" class="font-semibold text-12px text-color2 hover:underline bg-transparent border-0 cursor-pointer p-0">
                                تعليم الكل كمقروء
                            </button>
                        </form>
                    @endif
                </div>
                <div class="max-h-80 overflow-y-auto py-1">
                    @forelse ($navbarNotifications->take(5) as $notification)
                        <a href="{{ route('panel.v1.admin.system.section', ['section' => 'notifications']) }}"
                            class="flex items-start gap-3 px-4 py-3 hover:bg-fa transition border-b border-d9/70 last:border-b-0">
                            <span class="size-10 rounded-12px bg-primary/10 center shrink-0 mt-0.5">
                                <span class="icon-[tabler--bell] size-5 text-primary"></span>
                            </span>
                            <span class="min-w-0 flex-1 text-start">
                                <span class="block font-bold text-14px text-primary leading-snug line-clamp-1">{{ $notification->title }}</span>
                                <span class="block font-medium text-12px text-gray mt-1 line-clamp-2 leading-relaxed">{{ \Illuminate\Support\Str::limit(strip_tags($notification->message ?? ''), 90) }}</span>
                                <span class="block font-medium text-11px text-gray/80 mt-1.5">{{ date('Y/m/d H:i', (int) $notification->created_at) }}</span>
                            </span>
                            <span class="size-2 rounded-full bg-[#EF4444] shrink-0 mt-2" aria-hidden="true"></span>
                        </a>
                    @empty
                        <div class="px-4 py-10 center flex-col text-center">
                            <span class="size-12 rounded-full bg-primary/10 center mb-3">
                                <span class="icon-[tabler--bell-off] size-6 text-primary/50"></span>
                            </span>
                            <p class="font-semibold text-14px text-primary">لا توجد إشعارات جديدة</p>
                            <p class="font-medium text-12px text-gray mt-1">ستظهر إشعاراتك هنا</p>
                        </div>
                    @endforelse
                </div>
                <div class="border-t border-d9 p-3">
                    <a href="{{ route('panel.v1.admin.system.section', ['section' => 'notifications']) }}"
                        class="btn btn-primary btn-block rounded-10px h-11 font-bold text-14px">
                        عرض كل الإشعارات
                    </a>
                </div>
            </div>
        </div>

        <div class="relative inline-flex shrink-0" data-admin-menu>
            <button type="button" data-admin-menu-toggle
                class="inline-flex items-center gap-2 rounded-full pe-1 ps-1 py-1 hover:bg-[#F5F5F0] transition"
                aria-haspopup="menu" aria-expanded="false" aria-label="قائمة المستخدم">
                <span class="size-9 rounded-full bg-primary/10 center font-bold text-14px text-primary">
                    {{ mb_substr($userName, 0, 1) }}
                </span>
                <span class="hidden md:inline font-semibold text-14px text-primary">{{ $userName }}</span>
                <span class="icon-[tabler--chevron-down] size-4 text-primary/70 transition-transform duration-200" data-admin-menu-chevron></span>
            </button>
            <ul data-admin-menu-panel hidden
                class="absolute top-[calc(100%+8px)] end-0 min-w-44 py-2 rounded-12px border border-d9 bg-white shadow-xl z-[80]"
                role="menu">
                <li><a href="/logout" class="dropdown-item px-4 py-2.5 font-medium text-14px text-red-500">تسجيل الخروج</a></li>
            </ul>
        </div>
    </div>
</nav>
