import { initStudentCalendar } from './student-calendar.js';
import { initAssignmentSubmitModal } from './student-assignment-modal.js';
import { initCoursePlayer } from './student-course-player.js';

document.documentElement.classList.add('panel-v1-ready');

document.addEventListener('DOMContentLoaded', () => {
    initStudentCalendar();
    initAssignmentSubmitModal();
    initCoursePlayer();
});
