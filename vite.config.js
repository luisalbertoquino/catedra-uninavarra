import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    // Mantener caracteres no ASCII escapados (ej. iconos de Font Awesome f135) para que se vean bien
    // aunque el servidor no declare charset=utf-8 en el CSS
    esbuild: { charset: 'ascii' },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/admin-charts.js',
            ],
            refresh: true,
        }),
    ],
});
