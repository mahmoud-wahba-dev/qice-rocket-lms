<div class="qiec-topbar">
    {{-- Right (RTL start): user side --}}
    <div class="qiec-topbar__avatar">
        <img src="{{ $authUser->getAvatar(120) }}" alt="{{ $authUser->full_name }}"
            onerror="this.src='/assets/default/img/default/avatar-1.png'">
    </div>

    <div class="qiec-topbar__user">
        <p class="qiec-topbar__username">{{ $authUser->full_name }}</p>
        <p class="qiec-topbar__role">{{ $authUser->role->caption ?? '' }}</p>
    </div>

    <div class="qiec-topbar__icons">
        @include('admin.includes.header.notification')
        @include('admin.includes.header.language')
        @if(!empty(getAiContentsSettingsName('status')) && !empty(getAiContentsSettingsName('active_for_admin_panel')))
            <div class="qiec-topbar__iconbtn js-show-ai-content-drawer" title="المساعد الذكي">
                <x-iconsax-lin-cpu-charge class="icons" width="20px" height="20px"/>
            </div>
        @endif
        <div class="qiec-topbar__iconbtn" title="عن النظام">
            <x-iconsax-lin-info-circle class="icons" width="20px" height="20px"/>
        </div>
    </div>

    {{-- Dashboard pill pushed to far left --}}
    <a href="{{ getAdminPanelUrl('/') }}" class="qiec-topbar__pill">
        <x-iconsax-bul-chart-square class="icons" width="18px" height="18px"/>
        <span>لوحة إدارة النظام</span>
    </a>

    {{-- Mobile sidebar toggle --}}
    <button type="button" class="qiec-topbar__close d-lg-none" data-toggle="sidebar" aria-label="القائمة">
        <span></span><span></span><span></span>
    </button>

    {{-- Hidden functional includes (currency + full user menu kept for features) --}}
    <div class="d-none">
        @include('admin.includes.header.currency')
        @include('admin.includes.header.auth_user_info')
    </div>
</div>
