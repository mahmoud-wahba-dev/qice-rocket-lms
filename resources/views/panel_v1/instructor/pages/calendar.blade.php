@extends('panel_v1.instructor.layouts.app')

@section('content')
@php
    $monthsAr = [1=>'يناير',2=>'فبراير',3=>'مارس',4=>'أبريل',5=>'مايو',6=>'يونيو',7=>'يوليو',8=>'أغسطس',9=>'سبتمبر',10=>'أكتوبر',11=>'نوفمبر',12=>'ديسمبر'];
    $year = (int) ($calendarYear ?? now()->year);
    $month = (int) ($calendarMonth ?? now()->month);
    $selected = (int) ($calendarSelected ?? now()->day);
    $eventDates = array_values($calendarEventDates ?? []);
    $allEvents = array_values($calendarEvents ?? []);
    $dayEvents = $dayEvents ?? [];
    $upcoming = $upcomingEvents ?? [];
@endphp

<div class="space-y-6 pb-8" id="instructor-calendar-page"
    data-year="{{ $year }}"
    data-month="{{ $month }}"
    data-selected="{{ $selected }}">

    <script type="application/json" id="instructor-calendar-events">@json($allEvents)</script>
    <script type="application/json" id="instructor-calendar-dates">@json($eventDates)</script>
    <script type="application/json" id="instructor-calendar-months">@json(array_values($monthsAr))</script>

    {{-- Iconify safelist --}}
    <span class="hidden" aria-hidden="true">
        <span class="icon-[tabler--video]"></span>
        <span class="icon-[tabler--broadcast]"></span>
        <span class="icon-[tabler--player-play]"></span>
        <span class="icon-[tabler--ticket]"></span>
        <span class="icon-[tabler--calendar]"></span>
        <span class="icon-[tabler--calendar-off]"></span>
        <span class="icon-[tabler--bell]"></span>
        <span class="icon-[tabler--chevron-right]"></span>
        <span class="icon-[tabler--chevron-left]"></span>
    </span>

    @component('panel_v1.instructor.components.page-header', [
        'title' => 'تقويم الأحداث',
        'subtitle' => 'جلساتك المباشرة والاستشارات والمواعيد القادمة في مكان واحد',
    ])
        @slot('actions')
            <a href="{{ route('panel.v1.instructor.consultations') }}"
                class="inline-flex items-center gap-2 h-11 px-4 rounded-12px border border-d9 font-semibold text-14px text-primary hover:bg-fa transition">
                الجلسات الاستشارية
            </a>
        @endslot
    @endcomponent

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-5">
        <aside class="xl:col-span-3 border border-d9 rounded-14px bg-white p-5">
            <div class="mb-4">
                <h2 class="font-bold text-16px text-primary">اختر تاريخًا</h2>
                <p class="font-medium text-13px text-gray mt-1">الأيام ذات النقطة تحتوي أحداثًا</p>
            </div>

            <div class="flex items-center justify-between mb-4">
                <button type="button" data-cal-prev
                    class="size-9 rounded-full bg-fa center text-gray hover:text-primary transition" aria-label="الشهر السابق">
                    <span class="icon-[tabler--chevron-right] size-5"></span>
                </button>
                <p class="font-bold text-16px text-primary" data-cal-title></p>
                <button type="button" data-cal-next
                    class="size-9 rounded-full bg-fa center text-gray hover:text-primary transition" aria-label="الشهر التالي">
                    <span class="icon-[tabler--chevron-left] size-5"></span>
                </button>
            </div>

            <div class="grid grid-cols-7 gap-y-1 text-center mb-1" aria-hidden="true">
                @foreach (['س','ح','ن','ث','ر','خ','ج'] as $d)
                    <span class="font-medium text-12px text-gray py-1">{{ $d }}</span>
                @endforeach
            </div>
            <div class="grid grid-cols-7 gap-y-1 text-center" data-cal-grid dir="ltr"></div>

            <div class="mt-5 flex flex-wrap items-center gap-3 font-medium text-12px text-gray">
                <span class="inline-flex items-center gap-1.5">
                    <span class="size-5 rounded-6px bg-primary border border-primary"></span>
                    يوم محدد
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="size-5 rounded-6px bg-[#E8F5E9] border border-[#A7F3D0]"></span>
                    يحتوي أحداثًا
                </span>
            </div>
            <p class="mt-3 font-medium text-12px text-gray" data-cal-count>
                {{ count($eventDates) }} يومًا بأحداث · {{ count($allEvents) }} حدثًا
            </p>
        </aside>

        <section class="xl:col-span-6 border border-d9 rounded-14px bg-white p-5 sm:p-6">
            <div class="mb-5 pb-4 border-b border-d9">
                <h2 class="font-bold text-18px text-primary">
                    أحداث يوم
                    <span class="text-color2" data-cal-selected-label>{{ $selectedDateLabel ?? '' }}</span>
                </h2>
                <p class="font-medium text-13px text-gray mt-1">الجلسات المنتهية تظهر بدون رابط انضمام</p>
            </div>

            <div class="space-y-3" data-cal-day-list>
                @forelse ($dayEvents as $event)
                    @include('panel_v1.instructor.components.calendar-event-card', ['event' => $event])
                @empty
                    <div class="py-14 text-center">
                        <span class="icon-[tabler--calendar-off] size-12 text-d9 mx-auto mb-3 block"></span>
                        <p class="font-semibold text-16px text-gray">لا توجد أحداث في هذا اليوم</p>
                        <p class="font-medium text-13px text-gray mt-1">اختر يومًا عليه نقطة من التقويم</p>
                    </div>
                @endforelse
            </div>
        </section>

        <aside class="xl:col-span-3 border border-d9 rounded-14px bg-white p-5">
            <div class="mb-4 pb-3 border-b border-d9">
                <h2 class="font-bold text-16px text-primary">الأحداث القادمة</h2>
                <p class="font-medium text-13px text-gray mt-1">لم تنتهِ بعد</p>
            </div>

            <div class="space-y-3">
                @forelse ($upcoming as $event)
                    <button type="button"
                        class="w-full flex items-center gap-3 rounded-12px bg-[#FAFAF4] border border-d9 p-3 text-start hover:border-primary/40 transition"
                        data-cal-jump="{{ $event['date'] }}">
                        <span class="size-12 rounded-10px bg-white border border-d9 flex flex-col items-center justify-center shrink-0">
                            <span class="font-bold text-16px text-primary leading-none">{{ dateTimeFormat($event['timestamp'], 'j', false) }}</span>
                            <span class="font-medium text-11px text-gray">{{ $monthsAr[(int) dateTimeFormat($event['timestamp'], 'n', false)] ?? '' }}</span>
                        </span>
                        <span class="min-w-0">
                            <span class="block font-semibold text-13px text-primary truncate">{{ $event['type_label'] }}</span>
                            <span class="block font-medium text-12px text-gray truncate mt-0.5">{{ $event['title'] }}</span>
                            @if (!empty($event['time_label']))
                                <span class="block font-medium text-11px text-color2 mt-0.5">{{ $event['time_label'] }}</span>
                            @endif
                        </span>
                    </button>
                @empty
                    <p class="font-medium text-14px text-gray text-center py-8">لا توجد أحداث قادمة</p>
                @endforelse
            </div>
        </aside>
    </div>
</div>

@push('scripts')
<script>
(() => {
    const root = document.getElementById('instructor-calendar-page');
    if (!root) return;

    const readJson = (id, fallback) => {
        const el = document.getElementById(id);
        if (!el) return fallback;
        try { return JSON.parse(el.textContent || ''); } catch (e) { return fallback; }
    };

    const months = readJson('instructor-calendar-months', []);
    const eventDates = new Set(readJson('instructor-calendar-dates', []));
    const events = readJson('instructor-calendar-events', []);
    const grid = root.querySelector('[data-cal-grid]');
    const titleEl = root.querySelector('[data-cal-title]');
    const listEl = root.querySelector('[data-cal-day-list]');
    const labelEl = root.querySelector('[data-cal-selected-label]');

    let year = Number(root.dataset.year);
    let month = Number(root.dataset.month);
    let selected = Number(root.dataset.selected);

    const pad = (n) => String(n).padStart(2, '0');
    const ymd = (y, m, d) => `${y}-${pad(m)}-${pad(d)}`;
    const esc = (s) => String(s ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');

    const eventCardHtml = (event) => {
        const past = !!event.is_past;
        const actions = [];
        if (past) {
            actions.push(`<span class="inline-flex items-center h-8 px-3 rounded-8px bg-[#FEF2F2] text-[#DC2626] font-semibold text-12px">انتهت</span>`);
        }
        if (!past && event.calendar_url) {
            actions.push(`<a href="${esc(event.calendar_url)}" target="_blank" rel="noopener" class="size-8 rounded-full bg-white border border-d9 center hover:bg-fa transition" title="إضافة للتقويم"><span class="icon-[tabler--bell] size-4 text-gray"></span></a>`);
        }
        return `
            <article class="flex items-start justify-between gap-3 rounded-12px border border-d9 ${past ? 'bg-[#F8FAFC] opacity-90' : 'bg-[#FAFAF4]'} p-4">
                <div class="flex items-start gap-3 min-w-0">
                    <span class="size-11 rounded-10px bg-white border border-d9 center shrink-0">
                        <span class="${esc(event.icon || 'icon-[tabler--calendar]')} size-5 ${past ? 'text-gray' : 'text-primary'}"></span>
                    </span>
                    <div class="min-w-0 text-start">
                        <p class="font-semibold text-13px ${past ? 'text-gray' : 'text-color2'} mb-0.5">${esc(event.type_label || '')}</p>
                        <h3 class="font-bold text-15px text-primary leading-snug">${esc(event.title || '')}</h3>
                        <p class="font-medium text-13px text-gray mt-1">${esc(event.subtitle || '')}</p>
                    </div>
                </div>
                <div class="flex flex-col items-end gap-2 shrink-0">
                    ${event.time_label ? `<span class="inline-flex rounded-8px bg-white border border-d9 px-2.5 py-1 font-semibold text-12px text-primary whitespace-nowrap">${esc(event.time_label)}</span>` : ''}
                    <div class="flex items-center gap-1.5">${actions.join('')}</div>
                </div>
            </article>`;
    };

    const renderDayList = (dateStr) => {
        const dayItems = events.filter((e) => e.date === dateStr);
        if (labelEl) {
            const [y, m, d] = dateStr.split('-');
            labelEl.textContent = `${y}/${m}/${d}`;
        }
        if (!listEl) return;
        if (!dayItems.length) {
            listEl.innerHTML = `
                <div class="py-14 text-center">
                    <span class="icon-[tabler--calendar-off] size-12 text-d9 mx-auto mb-3 block"></span>
                    <p class="font-semibold text-16px text-gray">لا توجد أحداث في هذا اليوم</p>
                    <p class="font-medium text-13px text-gray mt-1">اختر يومًا عليه نقطة من التقويم</p>
                </div>`;
            return;
        }
        listEl.innerHTML = dayItems.map(eventCardHtml).join('');
    };

    const renderGrid = () => {
        if (!grid || !titleEl) return;
        titleEl.textContent = `${months[month - 1] || ''} ${year}`;
        grid.innerHTML = '';

        const first = new Date(year, month - 1, 1);
        const startPad = (first.getDay() + 1) % 7; // Saturday-start
        const daysInMonth = new Date(year, month, 0).getDate();

        for (let i = 0; i < startPad; i++) {
            const cell = document.createElement('span');
            cell.className = 'py-2';
            grid.appendChild(cell);
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const dateStr = ymd(year, month, day);
            const hasEvent = eventDates.has(dateStr);
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.dataset.day = String(day);
            btn.setAttribute('aria-label', dateStr + (hasEvent ? ' (أحداث)' : ''));

            let cls = 'relative size-10 mx-auto rounded-10px font-semibold text-14px transition inline-flex items-center justify-center border ';
            if (day === selected && hasEvent) {
                cls += 'bg-primary text-white border-primary shadow-sm ';
            } else if (day === selected) {
                cls += 'bg-primary text-white border-primary ';
            } else if (hasEvent) {
                cls += 'bg-[#E8F5E9] text-primary border-[#A7F3D0] hover:bg-[#D1FAE5] ';
            } else {
                cls += 'text-gray border-transparent hover:bg-fa hover:border-d9 ';
            }
            btn.className = cls;

            const num = document.createElement('span');
            num.textContent = String(day);
            btn.appendChild(num);

            if (hasEvent) {
                const dot = document.createElement('span');
                dot.className = day === selected
                    ? 'absolute bottom-1 left-1/2 -translate-x-1/2 size-1.5 rounded-full bg-white'
                    : 'absolute bottom-1 left-1/2 -translate-x-1/2 size-1.5 rounded-full bg-[#059669]';
                btn.appendChild(dot);
            }

            btn.addEventListener('click', () => {
                selected = day;
                renderGrid();
                renderDayList(dateStr);
            });
            grid.appendChild(btn);
        }
    };

    root.querySelector('[data-cal-prev]')?.addEventListener('click', () => {
        month -= 1;
        if (month < 1) { month = 12; year -= 1; }
        selected = Math.min(selected, new Date(year, month, 0).getDate());
        renderGrid();
        renderDayList(ymd(year, month, selected));
    });
    root.querySelector('[data-cal-next]')?.addEventListener('click', () => {
        month += 1;
        if (month > 12) { month = 1; year += 1; }
        selected = Math.min(selected, new Date(year, month, 0).getDate());
        renderGrid();
        renderDayList(ymd(year, month, selected));
    });

    root.querySelectorAll('[data-cal-jump]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const dateStr = btn.getAttribute('data-cal-jump');
            if (!dateStr) return;
            const [y, m, d] = dateStr.split('-').map(Number);
            year = y; month = m; selected = d;
            renderGrid();
            renderDayList(dateStr);
            listEl?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    });

    renderGrid();
})();
</script>
@endpush
@endsection
