<!-- Modal -->
<div class="d-none" id="extraDescriptionForm">
    <h3 class="section-title after-line font-20 text-dark-blue mb-25 p-16">{{ trans('update.add_items') }}</h3>

    <div class="js-form p-16" data-action="{{ getAdminPanelUrl() }}/webinar-extra-description/store">
        <input type="hidden" name="webinar_id" value="{{  !empty($webinar) ? $webinar->id :''  }}">
        <input type="hidden" name="type">

        <div class="js-form-groups">
            @if(!empty(getGeneralSettings('content_translate')))
                <div class="js-no-company-input form-group">
                    <label class="input-label">{{ trans('auth.language') }}</label>
                    <select name="locale" class="form-control ">
                        @foreach($userLanguages as $lang => $language)
                            <option value="{{ $lang }}" @if(mb_strtolower(request()->get('locale', app()->getLocale())) == mb_strtolower($lang)) selected @endif>{{ $language }}</option>
                        @endforeach
                    </select>
                    @error('locale')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
            @else
                <input type="hidden" name="locale" value="{{ getDefaultLocale() }}">
            @endif

            <div class="js-no-company-input form-group">
                <label class="input-label">{{ trans('public.title') }}</label>
                <input type="text" name="value" class="js-ajax-value form-control"/>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="mt-30 d-flex align-items-center justify-content-end">
            <button type="button" id="saveExtraDescription" class="btn btn-primary">{{ trans('public.save') }}</button>
            <button type="button" class="btn btn-danger ml-2 close-swl">{{ trans('public.close') }}</button>
        </div>
    </div>
</div>

@push('scripts_bottom')
    <script>
        (function () {
            var extraDescTypes = ['learning_materials', 'company_logos', 'requirements'];
            var pendingExtraDescType = null;

            $('body').on('click', '[id^="add_new_"]', function () {
                var type = this.id.replace('add_new_', '');
                if (extraDescTypes.indexOf(type) !== -1) {
                    pendingExtraDescType = type;
                }
            });

            $('body').on('click', '#saveExtraDescription', function () {
                if (!pendingExtraDescType) {
                    return;
                }

                $('.swal2-html-container input[name="type"]').val(pendingExtraDescType);
            });
        })();
    </script>
@endpush
