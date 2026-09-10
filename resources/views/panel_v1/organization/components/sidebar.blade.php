@php
    $orgLinks = [
        ['route' => 'panel.v1.organization.home', 'label' => 'الرئيسية', 'icon' => 'tabler--home'],
        ['route' => 'panel.v1.organization.users', 'params' => ['type' => 'instructors'], 'label' => 'المدربون', 'icon' => 'tabler--users'],
        ['route' => 'panel.v1.organization.users', 'params' => ['type' => 'students'], 'label' => 'المتدربون', 'icon' => 'tabler--user'],
        ['route' => 'panel.v1.organization.courses', 'label' => 'الدورات', 'icon' => 'tabler--book'],
    ];
    $currentRoute = \Illuminate\Support\Facades\Route::currentRouteName();
@endphp

<aside class="drawer-side lg:sticky lg:top-0 z-40">
    <nav class="menu bg-white border border-d9 rounded-16px p-4 m-4 space-y-1">
        @foreach ($orgLinks as $link)
            @php $isActive = $currentRoute === $link['route']; @endphp
            <a href="{{ route($link['route'], $link['params'] ?? []) }}"
                class="flex items-center gap-3 px-4 py-3 rounded-12px font-semibold text-16px {{ $isActive ? 'bg-primary text-white' : 'text-primary hover:bg-fa' }}">
                <span class="icon-[{{ $link['icon'] }}] size-5"></span>
                {{ $link['label'] }}
            </a>
        @endforeach
        <a href="/panel"
            class="flex items-center gap-3 px-4 py-3 rounded-12px font-semibold text-16px text-primary hover:bg-fa">
            <span class="icon-[tabler--layout-dashboard] size-5"></span>
            اللوحة القديمة
        </a>
        <a href="/logout"
            class="flex items-center gap-3 px-4 py-3 rounded-12px font-semibold text-16px text-[#EF4444] hover:bg-fa">
            <span class="icon-[tabler--logout] size-5"></span>
            تسجيل الخروج
        </a>
    </nav>
</aside>
