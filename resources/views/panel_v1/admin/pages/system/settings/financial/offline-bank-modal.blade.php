<div id="addOfflineBankForm" data-action="{{ $storeUrl ?? route('panel.v1.admin.system.settings.offline-banks.store') }}">
    @if(!empty(getGeneralSettings('content_translate')))
        <div class="pv1-field">
            <label>{{ trans('auth.language') }}</label>
            <select name="locale" class="pv1-input">
                @foreach(getLanguages() as $lang => $language)
                    <option value="{{ $lang }}" @if(($locale ?? app()->getLocale()) == mb_strtolower($lang)) selected @endif>{{ $language }}</option>
                @endforeach
            </select>
        </div>
    @else
        <input type="hidden" name="locale" value="{{ getDefaultLocale() }}">
    @endif

    <div class="pv1-field">
        <label>{{ trans('admin/main.title') }}</label>
        <input type="text" name="title" value="{{ (!empty($editBank) and !empty($editBank->translate($locale))) ? $editBank->translate($locale)->title : '' }}" class="js-ajax-title pv1-input"/>
    </div>

    <div class="pv1-field">
        <label>{{ trans('admin/main.logo') }}</label>
        <input type="text" name="logo" id="bankLogo" value="{{ (!empty($editBank)) ? $editBank->logo : '' }}" class="js-ajax-logo pv1-input" placeholder="/store/..."/>
        <p class="text-12px text-gray mt-1">أدخل مسار صورة الشعار (مثال: /store/banks/logo.png)</p>
    </div>

    <div class="flex items-center justify-between gap-3 mb-3">
        <h4 class="font-bold text-15px text-primary">{{ trans('update.specifications') }}</h4>
        <button type="button" class="js-add-specification inline-flex items-center gap-1.5 h-9 px-3 rounded-12px border border-d9 font-semibold text-13px text-primary">
            <span class="icon-[tabler--plus] size-4"></span>
            {{ trans('update.add_specification') }}
        </button>
    </div>

    <div class="js-specifications-lists space-y-3 mb-5">
        @if(!empty($editBank))
            @foreach($editBank->specifications as $specification)
                <div class="js-specification-card grid grid-cols-1 sm:grid-cols-[1fr_1fr_auto] gap-3 items-end">
                    <div class="pv1-field mb-0">
                        <label>{{ trans('update.specification') }}</label>
                        <input type="text" name="specifications[{{ $specification->id }}][name]" class="pv1-input"
                            value="{{ (!empty($specification->translate($locale))) ? $specification->translate($locale)->name : '' }}">
                    </div>
                    <div class="pv1-field mb-0">
                        <label>{{ trans('update.value') }}</label>
                        <input type="text" name="specifications[{{ $specification->id }}][value]" class="pv1-input" value="{{ $specification->value }}">
                    </div>
                    <button type="button" class="js-remove-specification pv1-btn-danger h-12">حذف</button>
                </div>
            @endforeach
        @endif
    </div>

    <div class="flex items-center justify-end gap-2">
        <button type="button" class="close-swl h-11 px-4 rounded-12px border border-d9 font-semibold text-14px text-primary">{{ trans('admin/main.cancel') }}</button>
        <button type="button" class="js-save-bank pv1-btn-primary">{{ trans('admin/main.save') }}</button>
    </div>
</div>
