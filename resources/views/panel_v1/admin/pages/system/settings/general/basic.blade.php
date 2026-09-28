@php
    if (!empty($itemValue) and !is_array($itemValue)) {
        $itemValue = json_decode($itemValue, true);
    }
@endphp

<div class="pv1-tab-pane @if(empty($social)) is-active @endif" id="basic" role="tabpanel" aria-labelledby="basic-tab">
    <div class="row">
        <div class="col-12 col-md-6">
            <form action="{{ route('panel.v1.admin.system.settings.store') }}" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="page" value="general">
                <input type="hidden" name="name" value="general">

                <div class="pv1-field">
                    <label>{{ trans('admin/main.site_name') }}</label>
                    <input type="text" name="value[site_name]" value="{{ (!empty($itemValue) and !empty($itemValue['site_name'])) ? $itemValue['site_name'] : old('site_name') }}" class="pv1-input "/>
                </div>

                <div class="pv1-field">
                    <label>{{ trans('admin/main.site_email') }}</label>
                    <input type="text" name="value[site_email]" value="{{ (!empty($itemValue) and !empty($itemValue['site_email'])) ? $itemValue['site_email'] : old('site_email') }}" class="pv1-input "/>
                </div>

                <div class="pv1-field">
                    <label>{{ trans('admin/main.site_phone') }}</label>
                    <input type="text" name="value[site_phone]" value="{{ (!empty($itemValue) and !empty($itemValue['site_phone'])) ? $itemValue['site_phone'] : old('site_phone') }}" class="pv1-input "/>
                </div>


                <div class="pv1-field">
                    <label class="input-label d-block">{{ trans('admin/main.register_method') }}</label>
                    <select name="value[register_method]" class="pv1-input">
                        <option value="mobile" @if(!empty($itemValue) and !empty($itemValue['register_method']) and $itemValue['register_method'] == 'mobile') selected @endif>{{ trans('admin/main.sms') }}</option>
                        <option value="email" @if(!empty($itemValue) and !empty($itemValue['register_method']) and $itemValue['register_method'] == 'email') selected @endif>{{ trans('admin/main.email') }}</option>
                    </select>
                </div>

                <div class="pv1-field">
                    <label class="input-label d-block">{{ trans('update.default_time_zone') }}</label>
                    <select name="value[default_time_zone]" class="pv1-input select2">
                        <option value="" disabled @if(empty($itemValue) or empty($itemValue['default_time_zone'])) selected @endif>{{ trans('admin/main.select') }}</option>
                        @foreach(getListOfTimezones() as $timezone)
                            <option value="{{ $timezone }}" @if(!empty($itemValue) and !empty($itemValue['default_time_zone']) and $itemValue['default_time_zone'] == $timezone) selected @endif>{{ $timezone }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="pv1-field">
                    <label class="input-label d-block">{{ trans('update.date_format') }}</label>
                    <select name="value[date_format]" class="pv1-input">
                        <option value="textual" @if(!empty($itemValue) and !empty($itemValue['date_format']) and $itemValue['date_format'] == 'textual') selected @endif>{{ trans('update.textual') }} (18 Dec 2021)</option>
                        <option value="numerical" @if(!empty($itemValue) and !empty($itemValue['date_format']) and $itemValue['date_format'] == 'numerical') selected @endif>{{ trans('update.numerical') }} (18/12/2021)</option>
                    </select>
                </div>

                <div class="pv1-field">
                    <label class="input-label d-block">{{ trans('update.time_format') }}</label>
                    <select name="value[time_format]" class="pv1-input">
                        <option value="24_hours" @if(!empty($itemValue) and !empty($itemValue['time_format']) and $itemValue['time_format'] == '24_hours') selected @endif>{{ trans('update.24_hours') }}</option>
                        <option value="12_hours" @if(!empty($itemValue) and !empty($itemValue['time_format']) and $itemValue['time_format'] == '12_hours') selected @endif>{{ trans('update.12_hours') }} (AM/PM)</option>
                    </select>
                </div>

                   <div class="pv1-field">
                    <label class="input-label d-block">{{ trans('admin/main.site_language') }}</label>
                    <select name="value[site_language]" class="pv1-input select2" data-placeholder="{{ trans('admin/main.site_language') }}">
                        <option value=""></option>
                        @foreach(getLanguages() as $key => $language)
                            <option value="{{ $key }}" @if((!empty($itemValue) and !empty($itemValue['site_language'])) and $itemValue['site_language'] == $key) selected @endif >{{ $language }}</option>
                        @endforeach
                    </select>
                    <div class="text-gray-500 text-small mt-1">{{ trans('admin/main.default_language_hint') }}</div>
                </div>

                 <div class="pv1-field">
                    <label class="input-label d-block">{{ trans('admin/main.guest_default_language') }}</label>
                    <select name="value[visitors_default_language]" class="pv1-input">
                        <option value="default" @if(empty($itemValue) or empty($itemValue['visitors_default_language']) or $itemValue['visitors_default_language'] == 'default') selected @endif>{{ trans('admin/main.use_default_language') }}</option>
                        <option value="detect_ip" @if(!empty($itemValue) and !empty($itemValue['visitors_default_language']) and $itemValue['visitors_default_language'] == 'detect_ip') selected @endif>{{ trans('admin/main.use_ip_language') }}</option>
                    </select>
                    <div class="text-gray-500 text-small mt-1">{{ trans('admin/main.guest_default_language_hint') }}</div>
                </div>

                <div class="pv1-field">
                    <label class="input-label d-block">{{ trans('admin/main.user_languages_lists') }}</label>
                    <select name="value[user_languages][]" multiple class="pv1-input select2" data-placeholder="{{ trans('admin/main.user_languages_lists') }}">
                        <option value=""></option>
                        @foreach(getLanguages() as $key => $language)
                            <option value="{{ $key }}" @if((!empty($itemValue) and !empty($itemValue['user_languages']) and is_array($itemValue['user_languages'])) and in_array($key, $itemValue['user_languages'])) selected @endif >{{ $language }}</option>
                        @endforeach
                    </select>
                    <div class="text-gray-500 text-small mt-1">{{ trans('admin/main.user_languages_lists_hint') }}</div>
                </div>

                <div class="pv1-field">
                    <label class="input-label d-block">{{ trans('admin/main.rtl_languages') }}</label>
                    <select name="value[rtl_languages][]" multiple class="pv1-input select2" data-placeholder="{{ trans('admin/main.rtl_languages') }}">
                        <option value=""></option>
                        @foreach(getLanguages() as $key => $language)
                            <option value="{{ $key }}" @if((!empty($itemValue) and !empty($itemValue['rtl_languages']) and is_array($itemValue['rtl_languages'])) and in_array($key, $itemValue['rtl_languages'])) selected @endif >{{ $language }}</option>
                        @endforeach
                    </select>
                    <div class="text-gray-500 text-small mt-1">{{ trans('admin/main.rtl_languages_hint') }}</div>
                </div>

                <div class="pv1-field">
                    <label class="input-label">{{ trans('admin/main.fav_icon') }}</label>
                    <div class="pv1-input-group">
                        
                        <input type="text" name="value[fav_icon]" id="fav_icon" value="{{ (!empty($itemValue) and !empty($itemValue['fav_icon'])) ? $itemValue['fav_icon'] : old('fav_icon') }}" class="pv1-input" placeholder="{{ trans('admin/main.fav_icon_placeholder') }}"/>
                    </div>
                </div>

                <div class="pv1-field">
                    <label class="input-label">{{ trans('admin/main.logo') }}</label>
                    <div class="pv1-input-group">
                        
                        <input type="text" name="value[logo]" id="logo" value="{{ (!empty($itemValue) and !empty($itemValue['logo'])) ? $itemValue['logo'] : old('logo') }}" class="pv1-input" placeholder="{{ trans('admin/main.logo_placeholder') }}"/>
                    </div>
                </div>

                <div class="pv1-field">
                    <label class="input-label">{{ trans('update.dark_mode_logo') }}</label>
                    <div class="pv1-input-group">
                        
                        <input type="text" name="value[dark_mode_logo]" id="dark_mode_logo" value="{{ (!empty($itemValue) and !empty($itemValue['dark_mode_logo'])) ? $itemValue['dark_mode_logo'] : old('dark_mode_logo') }}" class="pv1-input" placeholder="{{ trans('admin/main.logo_placeholder') }}"/>
                    </div>
                </div>


                <div class="pv1-field pv1-switch-wrapes-stacked">
                    <label class="pv1-switch-wrap pl-0">
                        <input type="hidden" name="value[rtl_layout]" value="0">
                        <input type="checkbox" name="value[rtl_layout]" id="rtlSwitch" value="1" {{ (!empty($itemValue) and !empty($itemValue['rtl_layout']) and $itemValue['rtl_layout']) ? 'checked="checked"' : '' }} class="pv1-switch"/>
                        <span class="pv1-switch-indicator"></span>
                        <label class="pv1-switch-label mb-0 cursor-pointer" for="rtlSwitch">{{ trans('admin/main.rtl_layout') }}</label>
                    </label>
                </div>

                <div class="pv1-field pv1-switch-wrapes-stacked">
                    <label class="pv1-switch-wrap pl-0">
                        <input type="hidden" name="value[preloading]" value="0">
                        <input type="checkbox" name="value[preloading]" id="preloadingSwitch" value="1" {{ (!empty($itemValue) and !empty($itemValue['preloading']) and $itemValue['preloading']) ? 'checked="checked"' : '' }} class="pv1-switch"/>
                        <span class="pv1-switch-indicator"></span>
                        <label class="pv1-switch-label mb-0 cursor-pointer" for="preloadingSwitch">{{ trans('admin/main.preloading') }}</label>
                    </label>
                </div>


                <div class="pv1-field pv1-switch-wrapes-stacked">
                    <label class="pv1-switch-wrap pl-0">
                        <input type="hidden" name="value[content_translate]" value="0">
                        <input type="checkbox" name="value[content_translate]" id="contentTranslate" value="1" {{ (!empty($itemValue) and !empty($itemValue['content_translate']) and $itemValue['content_translate']) ? 'checked="checked"' : '' }} class="pv1-switch"/>
                        <span class="pv1-switch-indicator"></span>
                        <label class="pv1-switch-label mb-0 cursor-pointer" for="contentTranslate">{{ trans('update.multi_language_content') }}</label>
                    </label>
                    <div class="text-gray-500 text-small mt-1">{{ trans('update.multi_language_content_hint') }}</div>
                </div>

                <div class="pv1-field pv1-switch-wrapes-stacked">
                    <label class="pv1-switch-wrap pl-0">
                        <input type="hidden" name="value[app_debugbar]" value="0">
                        <input type="checkbox" name="value[app_debugbar]" id="appDebugbarSwitch" value="1" {{ (!empty($itemValue) and !empty($itemValue['app_debugbar']) and $itemValue['app_debugbar']) ? 'checked="checked"' : '' }} class="pv1-switch"/>
                        <span class="pv1-switch-indicator"></span>
                        <label class="pv1-switch-label mb-0 cursor-pointer" for="appDebugbarSwitch">{{ trans('update.app_debugbar') }}</label>
                    </label>
                    <div class="text-gray-500 text-small mt-1">{{ trans('update.app_debugbar_hint') }}</div>
                </div>


                <button type="submit" class="pv1-btn-primary">{{ trans('admin/main.save_change') }}</button>
            </form>
        </div>
    </div>
</div>

