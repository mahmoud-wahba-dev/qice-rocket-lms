import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/landing_v1.css', 'resources/js/landing_v1.js'],
            refresh: true,
        }),
    ],
});
