import { initInstructorShell } from './instructor-shell.js';
import { initPanelV1FileUploads } from './file-upload.js';

document.documentElement.classList.add('panel-v1-ready');

document.addEventListener('DOMContentLoaded', () => {
    initInstructorShell();
    initPanelV1FileUploads();
});
