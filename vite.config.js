import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({

    server: {
        host: true, // 🔥 permite acceso desde otros dispositivos en la red local
        hmr: {
            host: '192.168.18.13', // reemplaza con tu IP local
        },
    },

    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
