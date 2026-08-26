@php
    $authUser = $authUser ?? auth()->user();
    $userName = $authUser->full_name ?? ($authUser->name ?? 'المدرب');
@endphp

<header class="panel-v1-panel-header">
    <div class="panel-v1-panel-header__actions">
        <button type="button" class="panel-v1-btn panel-v1-btn--accent">+ إنشاء دورة جديدة</button>
    </div>
    <div class="panel-v1-panel-header__user">
        <button type="button" class="panel-v1-icon-btn" aria-label="الإشعارات">تنبيه</button>
        <button type="button" class="panel-v1-icon-btn" aria-label="المفضلة">مفضل</button>
        <button type="button" class="panel-v1-icon-btn" aria-label="السلة">سلة</button>
        <div class="panel-v1-panel-header__profile">
            <span class="panel-v1-avatar">{{ mb_substr($userName, 0, 1) }}</span>
            <span>{{ $userName }}</span>
        </div>
    </div>
</header>
