@extends('panel_v1.admin.layouts.app')

@section('content')
@php
    $itemValue = $values ?? [];
@endphp
@include('panel_v1.admin.components.settings-form-styles')
<div class="space-y-6 sm:space-y-8 pb-8 pv1-settings">
    @component('panel_v1.admin.components.page-header', [
        'title' => $pageTitleText ?? 'تخصيص الشريط الجانبي',
        'subtitle' => 'رابط وخلفية الشريط الجانبي للوحة المستخدم',
    ])
        @slot('actions')
            <a href="{{ $hubUrl ?? route('panel.v1.admin.system.section', ['section' => 'settings']) }}"
                class="inline-flex items-center gap-2 h-12 px-4 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition">
                <span class="icon-[tabler--arrow-right] size-4"></span>
                العودة للإعدادات
            </a>
        @endslot
    @endcomponent

    <div class="rounded-14px border border-d9 bg-white shadow-sm p-4 sm:p-6 max-w-xl">
        <form action="{{ route('panel.v1.admin.system.settings.store') }}" method="post" class="space-y-4">
            @csrf
            <input type="hidden" name="name" value="panel_sidebar">
            <input type="hidden" name="page" value="personalization">

            @if(!empty(getGeneralSettings('content_translate')))
                <div class="pv1-field">
                    <label>{{ trans('auth.language') }}</label>
                    <select name="locale" class="pv1-input">
                        @foreach(getLanguages() as $lang => $language)
                            <option value="{{ $lang }}" @if(mb_strtolower(request()->get('locale', (!empty($itemValue) and !empty($itemValue['locale'])) ? $itemValue['locale'] : app()->getLocale())) == mb_strtolower($lang)) selected @endif>{{ $language }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="locale" value="{{ getDefaultLocale() }}">
            @endif

            <div class="pv1-field">
                <label>{{ trans('admin/main.link') }}</label>
                <input type="text" name="value[link]" value="{{ (!empty($itemValue) and !empty($itemValue['link'])) ? $itemValue['link'] : old('link') }}" class="pv1-input"/>
            </div>

            <div class="pv1-field">
                <label>{{ trans('admin/main.background') }}</label>
                <input type="text" name="value[background]" id="sidebarBackground" value="{{ (!empty($itemValue) and !empty($itemValue['background'])) ? $itemValue['background'] : old('background') }}" class="pv1-input" placeholder="/store/..."/>
                <p class="text-12px text-gray mt-1">أدخل مسار صورة الخلفية (مثال: /store/sidebar.jpg)</p>
            </div>

            <button type="submit" class="pv1-btn-primary">
                <span class="icon-[tabler--device-floppy] size-4"></span>
                {{ trans('admin/main.save_change') }}
            </button>
        </form>
    </div>
</div>
@endsection
