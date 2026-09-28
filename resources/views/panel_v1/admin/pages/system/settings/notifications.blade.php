@extends('panel_v1.admin.layouts.app')

@section('content')
@php
    $itemValue = (!empty($settings) and !empty($settings['notifications'])) ? $settings['notifications']->value : [];
    if (!empty($itemValue) and !is_array($itemValue)) {
        $itemValue = json_decode($itemValue, true);
    }
    $sections = \App\Models\NotificationTemplate::$notificationTemplateAssignSetting;
    $firstKey = array_key_first($sections);
@endphp
@include('panel_v1.admin.components.settings-form-styles')
<div class="space-y-6 sm:space-y-8 pb-8 pv1-settings" data-pv1-tabs data-active="{{ $firstKey }}">
    @component('panel_v1.admin.components.page-header', [
        'title' => $pageTitleText ?? 'إعدادات الإشعارات',
        'subtitle' => 'ربط قوالب الإشعارات بأحداث المنصة',
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
        <div class="flex flex-wrap gap-2 p-3 sm:p-4 border-b border-d9 bg-[#FAFAF4] max-h-40 overflow-y-auto">
            @foreach ($sections as $section => $items)
                <button type="button" data-tab-btn="{{ $section }}"
                    class="h-10 px-3 rounded-12px font-semibold text-12px transition whitespace-nowrap {{ $loop->first ? 'bg-primary text-white' : 'bg-white text-primary border border-d9' }}">
                    {{ trans('admin/main.notification_'.$section) }}
                </button>
            @endforeach
        </div>

        <div class="p-4 sm:p-6">
            @foreach ($sections as $tab => $items)
                <div data-tab-panel="{{ $tab }}" class="max-w-xl {{ $loop->first ? '' : 'hidden' }}">
                    <form action="{{ route('panel.v1.admin.system.settings.notifications.store') }}" method="post" class="space-y-4">
                        @csrf
                        @foreach ($items as $item)
                            <div class="pv1-field">
                                <label>{{ trans('admin/main.notification_'.$item) }}</label>
                                <select name="value[{{ $item }}]" class="pv1-input">
                                    <option value="">—</option>
                                    @foreach ($notificationTemplates as $template)
                                        <option value="{{ $template->id }}" @if(!empty($itemValue) and !empty($itemValue[$item]) and $itemValue[$item] == $template->id) selected @endif>
                                            {{ $template->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                        <button type="submit" class="pv1-btn-primary">
                            <span class="icon-[tabler--device-floppy] size-4"></span>
                            {{ trans('admin/main.save_change') }}
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    document.querySelectorAll('[data-pv1-tabs]').forEach(function (root) {
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
            btn.addEventListener('click', function () { activate(btn.getAttribute('data-tab-btn')); });
        });
        activate(root.getAttribute('data-active'));
    });
})();
</script>
@endpush
@endsection
