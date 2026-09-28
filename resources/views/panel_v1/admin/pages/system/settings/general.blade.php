@extends('panel_v1.admin.layouts.app')

@section('content')
@php
    $tabs = [
        'basic' => trans('admin/main.basic'),
        'socials' => trans('admin/main.socials'),
        'features' => trans('update.features'),
        'reminders' => trans('update.reminders'),
        'security' => trans('update.security'),
        'general_options' => trans('update.options'),
        'sms_channels' => trans('update.sms_channels'),
    ];
    $current = $activeTab ?? 'basic';
@endphp
@include('panel_v1.admin.components.settings-form-styles')
<div class="space-y-6 sm:space-y-8 pb-8 pv1-settings" data-pv1-tabs data-active="{{ $current }}">
    @component('panel_v1.admin.components.page-header', [
        'title' => $pageTitleText ?? 'الإعدادات العامة',
        'subtitle' => 'إدارة الهوية، اللغات، الأمان وخيارات المنصة',
    ])
        @slot('actions')
            <a href="{{ $hubUrl ?? route('panel.v1.admin.system.section', ['section' => 'settings']) }}"
                class="inline-flex items-center gap-2 h-12 px-4 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition">
                <span class="icon-[tabler--arrow-right] size-4"></span>
                العودة للإعدادات
            </a>
        @endslot
    @endcomponent

    <div class="rounded-14px border border-d9 bg-white shadow-sm overflow-hidden">
        <div class="flex flex-wrap gap-2 p-3 sm:p-4 border-b border-d9 bg-[#FAFAF4]" role="tablist">
            @foreach ($tabs as $key => $label)
                <button type="button" data-tab-btn="{{ $key }}"
                    class="h-10 px-4 rounded-12px font-semibold text-13px transition {{ $current === $key ? 'bg-primary text-white' : 'bg-white text-primary border border-d9' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="p-4 sm:p-6">
            <div data-tab-panel="basic" class="{{ $current === 'basic' ? '' : 'hidden' }}">
                @include('panel_v1.admin.pages.system.settings.general.basic', [
                    'itemValue' => (!empty($settings) && !empty($settings['general'])) ? $settings['general']->value : '',
                    'social' => null,
                ])
            </div>
            <div data-tab-panel="socials" class="{{ $current === 'socials' ? '' : 'hidden' }}">
                @include('panel_v1.admin.pages.system.settings.general.socials', [
                    'itemValue' => (!empty($settings) && !empty($settings['socials'])) ? $settings['socials']->value : '',
                    'social' => $social ?? null,
                    'socialKey' => $socialKey ?? null,
                ])
            </div>
            <div data-tab-panel="features" class="{{ $current === 'features' ? '' : 'hidden' }}">
                @include('panel_v1.admin.pages.system.settings.general.features', [
                    'itemValue' => (!empty($settings) && !empty($settings['features'])) ? $settings['features']->value : '',
                ])
            </div>
            <div data-tab-panel="reminders" class="{{ $current === 'reminders' ? '' : 'hidden' }}">
                @include('panel_v1.admin.pages.system.settings.general.reminders', [
                    'itemValue' => (!empty($settings) && !empty($settings['reminders'])) ? $settings['reminders']->value : '',
                ])
            </div>
            <div data-tab-panel="security" class="{{ $current === 'security' ? '' : 'hidden' }}">
                @include('panel_v1.admin.pages.system.settings.general.security', [
                    'itemValue' => (!empty($settings) && !empty($settings['security'])) ? $settings['security']->value : '',
                ])
            </div>
            <div data-tab-panel="general_options" class="{{ $current === 'general_options' ? '' : 'hidden' }}">
                @include('panel_v1.admin.pages.system.settings.general.options', [
                    'itemValue' => (!empty($settings) && !empty($settings['general_options'])) ? $settings['general_options']->value : '',
                ])
            </div>
            <div data-tab-panel="sms_channels" class="{{ $current === 'sms_channels' ? '' : 'hidden' }}">
                @include('panel_v1.admin.pages.system.settings.general.sms_channels', [
                    'itemValue' => (!empty($settings) && !empty($settings['sms_channels'])) ? $settings['sms_channels']->value : '',
                ])
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    function initTabs(root) {
        const buttons = root.querySelectorAll('[data-tab-btn]');
        const panels = root.querySelectorAll('[data-tab-panel]');
        function activate(key) {
            buttons.forEach(function (btn) {
                const on = btn.getAttribute('data-tab-btn') === key;
                btn.classList.toggle('bg-primary', on);
                btn.classList.toggle('text-white', on);
                btn.classList.toggle('bg-white', !on);
                btn.classList.toggle('text-primary', !on);
                btn.classList.toggle('border', !on);
                btn.classList.toggle('border-d9', !on);
            });
            panels.forEach(function (panel) {
                panel.classList.toggle('hidden', panel.getAttribute('data-tab-panel') !== key);
            });
        }
        buttons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                activate(btn.getAttribute('data-tab-btn'));
            });
        });
        activate(root.getAttribute('data-active') || 'basic');
    }
    document.querySelectorAll('[data-pv1-tabs]').forEach(initTabs);

    document.addEventListener('change', function (e) {
        if (!e.target) return;
        if (e.target.id === 'allow_instructor_delete_contentSwitch') {
            document.querySelectorAll('.js-content-delete-method-field').forEach(function (el) {
                el.classList.toggle('d-none', !e.target.checked);
            });
        }
        if (e.target.id === 'loginDeviceLimit') {
            document.querySelectorAll('.js-device-limit-number').forEach(function (el) {
                el.classList.toggle('d-none', !e.target.checked);
            });
        }
        if (e.target.id === 'checkMobileNumberSwitch') {
            document.querySelectorAll('.js-check-mobile-number-digits-filed').forEach(function (el) {
                el.classList.toggle('d-none', !e.target.checked);
            });
        }
    });
})();
</script>
@endpush
@endsection
