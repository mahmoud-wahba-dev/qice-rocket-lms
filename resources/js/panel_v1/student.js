import { initStudentCalendar } from './student-calendar.js';

document.documentElement.classList.add('panel-v1-ready');

document.addEventListener('DOMContentLoaded', () => {
    initStudentCalendar();
});
