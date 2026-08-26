import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/landing_v1.css',
                'resources/js/landing_v1.js',
                'resources/css/panel_v1/student.css',
                'resources/js/panel_v1/student.js',
                'resources/css/panel_v1/instructor.css',
                'resources/js/panel_v1/instructor.js',
                'resources/css/panel_v1/admin.css',
                'resources/js/panel_v1/admin.js',
            ],
            refresh: true,
        }),
    ],
});
