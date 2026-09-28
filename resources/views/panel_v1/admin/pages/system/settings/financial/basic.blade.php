@php
    if (!empty($itemValue) and !is_array($itemValue)) {
        $itemValue = json_decode($itemValue, true);
    }
@endphp

<div class="max-w-xl">
    <form action="{{ route('panel.v1.admin.system.settings.store') }}" method="post" class="space-y-4">
        @csrf
        <input type="hidden" name="page" value="financial">
        <input type="hidden" name="name" value="financial">

        <div class="pv1-field">
            <label>{{ trans('admin/main.tax') }}</label>
            <input type="text" name="value[tax]" value="{{ (!empty($itemValue) and !empty($itemValue['tax'])) ? $itemValue['tax'] : old('tax') }}" class="pv1-input"/>
        </div>

        <div class="pv1-field">
            <label>{{ trans('admin/main.minimum_payout_amount') }}</label>
            <input type="number" name="value[minimum_payout]" value="{{ (!empty($itemValue) and !empty($itemValue['minimum_payout'])) ? $itemValue['minimum_payout'] : old('minimum_payout') }}" class="pv1-input" min="0"/>
            <div class="text-gray-500 text-small mt-1">{{ trans('admin/main.minimum_payout_amount_hint') }}</div>
        </div>

        <div class="pv1-field">
            <label>{{ trans('update.price_display') }}</label>
            <select name="value[price_display]" class="pv1-input">
                <option value="only_price" @if((!empty($itemValue) and !empty($itemValue['price_display'])) and $itemValue['price_display'] == 'only_price') selected @endif>{{ trans('update.display_only_price') }}</option>
                <option value="total_price" @if((!empty($itemValue) and !empty($itemValue['price_display'])) and $itemValue['price_display'] == 'total_price') selected @endif>{{ trans('update.display_total_price') }}</option>
                <option value="price_and_tax" @if((!empty($itemValue) and !empty($itemValue['price_display'])) and $itemValue['price_display'] == 'price_and_tax') selected @endif>{{ trans('update.display_price_and_tax') }}</option>
            </select>
        </div>

        <label class="pv1-switch-wrap">
            <input type="hidden" name="value[hide_disabled_payment_gateways]" value="0">
            <input type="checkbox" name="value[hide_disabled_payment_gateways]" id="hide_disabled_payment_gatewaysSwitch" value="1"
                {{ (!empty($itemValue) and !empty($itemValue['hide_disabled_payment_gateways']) and $itemValue['hide_disabled_payment_gateways']) ? 'checked' : '' }}
                class="pv1-switch"/>
            <span class="pv1-switch-label" for="hide_disabled_payment_gatewaysSwitch">{{ trans('update.hide_disabled_payment_gateways') }}</span>
        </label>

        <button type="submit" class="pv1-btn-primary">
            <span class="icon-[tabler--device-floppy] size-4"></span>
            {{ trans('admin/main.save_change') }}
        </button>
    </form>
</div>
