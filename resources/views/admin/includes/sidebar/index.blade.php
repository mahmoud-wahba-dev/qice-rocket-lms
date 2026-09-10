<button type="button" class="sidebar-close">
    <x-iconsax-lin-add class="close-icon text-white" width="40px" height="40px"/>
</button>

<div class="main-sidebar qiec-sidebar">
    <aside id="sidebar-wrapper">
        <div class="qiec-brand">
            <img src="/assets/admin/img/qiec-logo.svg" alt="QIEC" width="64" height="64"
                onerror="this.style.display='none'">
            <p class="qiec-brand__title">لوحة التحكم</p>
        </div>

        <ul class="sidebar-menu">
            @can('admin_general_dashboard_show')
                <li class="{{ (request()->is(getAdminPanelUrl('/'))) ? 'active' : '' }}">
                    <a href="{{ getAdminPanelUrl('') }}" class="nav-link">
                    <x-iconsax-bul-chart-square class="icons" width="24px" height="24px"/>
                        <span>{{ trans('admin/main.dashboard') }}</span>
                    </a>
                </li>
            @endcan

            @can('admin_marketing_dashboard')
                <li class="{{ (request()->is(getAdminPanelUrl('/marketing', false))) ? 'active' : '' }}">
                    <a href="{{ getAdminPanelUrl('/marketing') }}" class="nav-link">
                    <x-iconsax-bul-graph class="icons" width="24px" height="24px"/>
                        <span>{{ trans('admin/main.marketing_dashboard') }}</span>
                    </a>
                </li>
            @endcan

            {{-- Education --}}
            @include('admin.includes.sidebar.education')

            {{-- Appointments --}}
            @include('admin.includes.sidebar.appointments')

            {{-- Users --}}
            @include('admin.includes.sidebar.users')

            {{-- Forum --}}
            @include('admin.includes.sidebar.forum')

            {{-- CRM --}}
            @include('admin.includes.sidebar.crm')

            {{-- Content --}}
            @include('admin.includes.sidebar.content')

            {{-- Financial --}}
            @include('admin.includes.sidebar.financial')

            {{-- Marketing --}}
            @include('admin.includes.sidebar.marketing')

            {{-- Appearance --}}
            @include('admin.includes.sidebar.appearance')

            {{-- Settings --}}
            @include('admin.includes.sidebar.settings')

            <li>
                <a class="nav-link" href="{{ getAdminPanelUrl() }}/logout">
                <x-iconsax-bul-logout class="icons text-danger" width="24px" height="24px"/>
                <span class="text-danger">{{ trans('admin/main.logout') }}</span>
                </a>
            </li>

        </ul>
        <br><br><br>
    </aside>
</div>
