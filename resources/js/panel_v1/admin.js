import { initPanelV1FileUploads } from './file-upload.js';

// panel_v1 admin shell entry
document.documentElement.classList.add('panel-v1-ready');

document.addEventListener('DOMContentLoaded', () => {
    initPanelV1FileUploads();
});
