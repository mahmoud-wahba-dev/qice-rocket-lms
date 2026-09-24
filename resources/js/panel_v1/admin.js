import { initAdminShell } from './admin-shell.js';
import { initPanelV1FileUploads } from './file-upload.js';
import { initCreateCourseWizard } from './instructor-shell.js';

// panel_v1 admin shell entry — parity with instructor.js
document.documentElement.classList.add('panel-v1-ready');

document.addEventListener('DOMContentLoaded', () => {
    initAdminShell();
    initPanelV1FileUploads();
    initCreateCourseWizard(document);
});
