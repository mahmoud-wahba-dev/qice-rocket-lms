@php
    if (!empty($itemValue) and !is_array($itemValue)) {
        $itemValue = json_decode($itemValue, true);
    }
@endphp

@push('styles_top')

@endpush

<div class="pv1-tab-pane" id="reminders" role="tabpanel" aria-labelledby="reminders-tab">

    <form action="{{ route('panel.v1.admin.system.settings.store', ['name' => 'reminders']) }}" method="post">
        {{ csrf_field() }}
        <input type="hidden" name="page" value="general">
        <input type="hidden" name="name" value="reminders">
                <input type="hidden" name="reminders" value="reminders">

        <div class="row">
            <div class="col-12 col-md-6">

                <div class="pv1-field">
                    <label class="input-label">{{ trans('admin/main.webinar_reminder_schedule') }}</label>
                    <input type="number" name="value[webinar_reminder_schedule]" id="webinar_reminder_schedule" value="{{ (!empty($itemValue) and !empty($itemValue['webinar_reminder_schedule'])) ? $itemValue['webinar_reminder_schedule'] : null }}" class="pv1-input"/>
                    <p class="font-12 text-gray-500 mt-1 mb-0">{{ trans('update.webinar_reminder_hint') }}</p>
                </div>

                <div class="pv1-field">
                    <label class="input-label">{{ trans('update.meeting_reminder_schedule') }}</label>
                    <input type="number" name="value[meeting_reminder_schedule]" id="meeting_reminder_schedule" value="{{ (!empty($itemValue) and !empty($itemValue['meeting_reminder_schedule'])) ? $itemValue['meeting_reminder_schedule'] : null }}" class="pv1-input"/>
                    <p class="font-12 text-gray-500 mt-1 mb-0">{{ trans('update.meeting_reminder_hint') }}</p>
                </div>

                <div class="pv1-field">
                    <label class="input-label">{{ trans('update.subscribe_reminder_schedule') }}</label>
                    <input type="number" name="value[subscribe_reminder_schedule]" id="subscribe_reminder_schedule" value="{{ (!empty($itemValue) and !empty($itemValue['subscribe_reminder_schedule'])) ? $itemValue['subscribe_reminder_schedule'] : null }}" class="pv1-input"/>
                    <p class="font-12 text-gray-500 mt-1 mb-0">{{ trans('update.subscribe_reminder_hint') }}</p>
                </div>

                <div class="pv1-field">
                    <label class="input-label">{{ trans('update.notification_before_subscription_plan_expires') }} ({{ trans('home.hours') }})</label>
                    <input type="number" name="value[notification_before_subscription_plan_expires]" id="notification_before_subscription_plan_expires" value="{{ (!empty($itemValue) and !empty($itemValue['notification_before_subscription_plan_expires'])) ? $itemValue['notification_before_subscription_plan_expires'] : '' }}" class="pv1-input"/>
                    <p class="font-12 text-gray-500 mt-1 mb-0">{{ trans('update.notification_before_subscription_plan_expires_input_hint') }}</p>
                </div>


                <div class="pv1-field">
                    <label class="input-label">{{ trans('update.event_reminder_schedule') }}</label>
                    <input type="number" name="value[event_reminder_schedule]" id="event_reminder_schedule" value="{{ (!empty($itemValue) and !empty($itemValue['event_reminder_schedule'])) ? $itemValue['event_reminder_schedule'] : null }}" class="pv1-input"/>
                    <p class="font-12 text-gray-500 mt-1 mb-0">{{ trans('update.event_reminder_schedule_input_hint') }}</p>
                </div>

                <div class="pv1-field">
                    <label class="input-label">{{ trans('update.meeting_packages_reminder_schedule') }}</label>
                    <input type="number" name="value[meeting_packages_reminder_schedule]" id="meeting_packages_reminder_schedule" value="{{ (!empty($itemValue) and !empty($itemValue['meeting_packages_reminder_schedule'])) ? $itemValue['meeting_packages_reminder_schedule'] : null }}" class="pv1-input"/>
                    <p class="font-12 text-gray-500 mt-1 mb-0">{{ trans('update.meeting_packages_reminder_schedule_input_hint') }}</p>
                </div>

            </div>
        </div>

        <button type="submit" class="pv1-btn-primary">{{ trans('admin/main.save_change') }}</button>
    </form>

</div>

@push('scripts_bottom')

@endpush
