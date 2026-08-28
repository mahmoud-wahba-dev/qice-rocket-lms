const MONTH_NAMES = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December',
];

function saturdayStartOffset(year, month) {
    const firstDay = new Date(year, month - 1, 1).getDay();
    return (firstDay + 1) % 7;
}

function daysInMonth(year, month) {
    return new Date(year, month, 0).getDate();
}

function formatDateValue(year, month, day) {
    const m = String(month).padStart(2, '0');
    const d = String(day).padStart(2, '0');
    return `${year}-${m}-${d}`;
}

function renderStudentCalendar(root) {
    let year = parseInt(root.dataset.year, 10);
    let month = parseInt(root.dataset.month, 10);
    let selectedDay = parseInt(root.dataset.selected, 10);

    const titleEl = root.querySelector('[data-calendar-title]');
    const gridEl = root.querySelector('[data-calendar-grid]');
    const inputEl = root.querySelector('[data-calendar-input]');
    const prevBtn = root.querySelector('[data-calendar-prev]');
    const nextBtn = root.querySelector('[data-calendar-next]');

    const paint = () => {
        titleEl.textContent = `${MONTH_NAMES[month - 1]} ${year}`;
        gridEl.innerHTML = '';

        const totalDays = daysInMonth(year, month);
        const offset = saturdayStartOffset(year, month);

        const prevMonth = month === 1 ? 12 : month - 1;
        const prevYear = month === 1 ? year - 1 : year;
        const prevMonthDays = daysInMonth(prevYear, prevMonth);

        const nextMonth = month === 12 ? 1 : month + 1;
        const nextYear = month === 12 ? year + 1 : year;

        const cells = [];

        for (let i = offset - 1; i >= 0; i--) {
            cells.push({ day: prevMonthDays - i, muted: true, month: prevMonth, year: prevYear });
        }

        for (let day = 1; day <= totalDays; day++) {
            cells.push({ day, muted: false, month, year });
        }

        let nextDay = 1;
        while (cells.length % 7 !== 0) {
            cells.push({ day: nextDay++, muted: true, month: nextMonth, year: nextYear });
        }

        cells.forEach((cell) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = String(cell.day);
            btn.className = 'student-dash-calendar__day';

            if (cell.muted) {
                btn.classList.add('is-muted');
            }

            if (!cell.muted && cell.day === selectedDay && cell.month === month && cell.year === year) {
                btn.classList.add('is-selected');
                btn.setAttribute('aria-pressed', 'true');
            } else {
                btn.setAttribute('aria-pressed', 'false');
            }

            btn.addEventListener('click', () => {
                if (cell.muted) {
                    if (cell.month < month || cell.year < year) {
                        month = cell.month;
                        year = cell.year;
                    } else {
                        month = cell.month;
                        year = cell.year;
                    }
                    selectedDay = cell.day;
                } else {
                    selectedDay = cell.day;
                }

                root.dataset.year = String(year);
                root.dataset.month = String(month);
                root.dataset.selected = String(selectedDay);
                inputEl.value = formatDateValue(year, month, selectedDay);
                paint();
            });

            gridEl.appendChild(btn);
        });

        inputEl.value = formatDateValue(year, month, selectedDay);
    };

    prevBtn?.addEventListener('click', () => {
        if (month === 1) {
            month = 12;
            year -= 1;
        } else {
            month -= 1;
        }
        selectedDay = Math.min(selectedDay, daysInMonth(year, month));
        root.dataset.year = String(year);
        root.dataset.month = String(month);
        root.dataset.selected = String(selectedDay);
        paint();
    });

    nextBtn?.addEventListener('click', () => {
        if (month === 12) {
            month = 1;
            year += 1;
        } else {
            month += 1;
        }
        selectedDay = Math.min(selectedDay, daysInMonth(year, month));
        root.dataset.year = String(year);
        root.dataset.month = String(month);
        root.dataset.selected = String(selectedDay);
        paint();
    });

    paint();
}

export function initStudentCalendar() {
    document.querySelectorAll('#student-dash-calendar').forEach(renderStudentCalendar);
}
