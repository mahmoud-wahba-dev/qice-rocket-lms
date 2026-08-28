<div class="student-dash-calendar px-6 pb-8" id="student-dash-calendar"
    data-year="{{ $calendarYear ?? now()->year }}"
    data-month="{{ $calendarMonth ?? now()->month }}"
    data-selected="{{ $calendarSelected ?? now()->day }}">

    <div class="flex items-center justify-between mb-6">
        <button type="button"
            class="student-dash-calendar__nav size-9 inline-flex items-center justify-center rounded-full text-gray hover:text-primary transition-colors"
            data-calendar-prev aria-label="الشهر السابق">
            <span class="icon-[tabler--chevron-right] size-5"></span>
        </button>

        <p class="student-dash-calendar__title font-semibold text-18px text-black" data-calendar-title></p>

        <button type="button"
            class="student-dash-calendar__nav size-9 inline-flex items-center justify-center rounded-full text-gray hover:text-primary transition-colors"
            data-calendar-next aria-label="الشهر التالي">
            <span class="icon-[tabler--chevron-left] size-5"></span>
        </button>
    </div>

    <div class="grid grid-cols-7 gap-y-2 text-center mb-2" aria-hidden="true">
        <span class="font-medium text-14px text-gray py-1">Sa</span>
        <span class="font-medium text-14px text-gray py-1">Su</span>
        <span class="font-medium text-14px text-gray py-1">Mo</span>
        <span class="font-medium text-14px text-gray py-1">Tu</span>
        <span class="font-medium text-14px text-gray py-1">We</span>
        <span class="font-medium text-14px text-gray py-1">Th</span>
        <span class="font-medium text-14px text-gray py-1">Fr</span>
    </div>

    <div class="grid grid-cols-7 gap-y-1 text-center" data-calendar-grid dir="ltr"></div>

    <input type="hidden" name="calendar_selected_date" data-calendar-input value="">
</div>
