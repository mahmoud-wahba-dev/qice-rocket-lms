@extends('panel_v1.admin.layouts.app')

@section('content')
@php
    $itemValue = (!empty($settings) and !empty($settings['seo_metas'])) ? $settings['seo_metas']->value : [];
    if (!empty($itemValue) and !is_array($itemValue)) {
        $itemValue = json_decode($itemValue, true);
    }
    $pages = \App\Models\Setting::$pagesSeoMetas;
@endphp
@include('panel_v1.admin.components.settings-form-styles')
<div class="space-y-6 sm:space-y-8 pb-8 pv1-settings" data-pv1-tabs data-active="extra_meta_tags">
    @component('panel_v1.admin.components.page-header', [
        'title' => $pageTitleText ?? 'إعدادات SEO',
        'subtitle' => 'وسوم الميتا والوصف لكل صفحة',
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
        <div class="flex flex-wrap gap-2 p-3 sm:p-4 border-b border-d9 bg-[#FAFAF4] max-h-44 overflow-y-auto">
            <button type="button" data-tab-btn="extra_meta_tags"
                class="h-10 px-3 rounded-12px font-semibold text-12px transition whitespace-nowrap bg-primary text-white">
                {{ trans('update.extra_meta_tags') }}
            </button>
            @foreach ($pages as $page)
                <button type="button" data-tab-btn="{{ $page }}"
                    class="h-10 px-3 rounded-12px font-semibold text-12px transition whitespace-nowrap bg-white text-primary border border-d9">
                    {{ trans('admin/main.seo_metas_'.$page) }}
                </button>
            @endforeach
        </div>

        <div class="p-4 sm:p-6">
            <div data-tab-panel="extra_meta_tags" class="max-w-2xl">
                <form action="{{ route('panel.v1.admin.system.settings.seo.store') }}" method="post" class="space-y-4">
                    @csrf
                    <div class="pv1-field">
                        <label>{{ trans('update.extra_meta_tags') }}</label>
                        <textarea name="value[extra_meta_tags]" rows="6" class="pv1-input">{{ (!empty($itemValue) and !empty($itemValue['extra_meta_tags'])) ? $itemValue['extra_meta_tags'] : '' }}</textarea>
                        <p class="text-12px text-gray mt-1">{{ trans('update.extra_meta_tags_hint1') }}</p>
                        <p class="text-12px text-gray">{{ trans('update.extra_meta_tags_hint2') }}</p>
                    </div>
                    <button type="submit" class="pv1-btn-primary">{{ trans('admin/main.submit') }}</button>
                </form>
            </div>

            @foreach ($pages as $page)
                <div data-tab-panel="{{ $page }}" class="max-w-xl hidden">
                    <form action="{{ route('panel.v1.admin.system.settings.seo.store') }}" method="post" class="space-y-4">
                        @csrf
                        <div class="pv1-field">
                            <label>{{ trans('admin/main.title') }}</label>
                            <input type="text" name="value[{{ $page }}][title]" class="pv1-input"
                                value="{{ (!empty($itemValue) and !empty($itemValue[$page])) ? ($itemValue[$page]['title'] ?? '') : '' }}"/>
                        </div>
                        <div class="pv1-field">
                            <label>{{ trans('public.description') }}</label>
                            <textarea name="value[{{ $page }}][description]" rows="4" class="pv1-input">{{ (!empty($itemValue) and !empty($itemValue[$page])) ? ($itemValue[$page]['description'] ?? '') : '' }}</textarea>
                        </div>

                        @if(in_array($page, ['upcoming_courses_lists', 'bundles_lists', 'products_lists']))
                            <div class="pv1-field">
                                <label>{{ trans('update.bottom_seo_title') }}</label>
                                <input type="text" name="value[{{ $page }}][bottom_seo_title]" class="pv1-input"
                                    value="{{ (!empty($itemValue) and !empty($itemValue[$page]) and !empty($itemValue[$page]['bottom_seo_title'])) ? $itemValue[$page]['bottom_seo_title'] : '' }}"/>
                            </div>
                            <div class="pv1-field">
                                <label>{{ trans('update.bottom_seo_content') }}</label>
                                <textarea name="value[{{ $page }}][bottom_seo_content]" rows="4" class="pv1-input">{{ (!empty($itemValue) and !empty($itemValue[$page]) and !empty($itemValue[$page]['bottom_seo_content'])) ? $itemValue[$page]['bottom_seo_content'] : '' }}</textarea>
                            </div>
                        @endif

                        <label class="pv1-switch-wrap">
                            <input type="hidden" name="value[{{ $page }}][robot]" value="noindex">
                            <input type="checkbox" name="value[{{ $page }}][robot]" id="{{ $page }}Robot" value="index"
                                {{ (!empty($itemValue) and !empty($itemValue[$page]) and (empty($itemValue[$page]['robot']) or $itemValue[$page]['robot'] != 'noindex')) ? 'checked' : '' }}
                                class="pv1-switch">
                            <span class="pv1-switch-label">{{ trans('admin/main.index') }} / {{ trans('admin/main.no_index') }}</span>
                        </label>

                        <button type="submit" class="pv1-btn-primary">{{ trans('admin/main.submit') }}</button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>

    <div class="rounded-14px border border-d9 bg-white p-4 sm:p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <p class="font-bold text-14px text-primary mb-1">{{ trans('admin/main.seo_metas_hint_title_1') }}</p>
            <p class="font-medium text-13px text-gray">{{ trans('admin/main.seo_metas_hint_description_1') }}</p>
        </div>
        <div>
            <p class="font-bold text-14px text-primary mb-1">{{ trans('admin/main.seo_metas_hint_title_2') }}</p>
            <p class="font-medium text-13px text-gray">{{ trans('admin/main.seo_metas_hint_description_2') }}</p>
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
        activate(root.getAttribute('data-active') || 'extra_meta_tags');
    });
})();
</script>
@endpush
@endsection
