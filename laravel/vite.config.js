import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        tailwindcss(),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        vue(),
    ],
    resolve: {
        alias: {
            'vue': 'vue/dist/vue.esm-bundler.js'
        }
    },
    server: {
        host: '0.0.0.0',
        cors: true,
        hmr: {
            host: '89c520a66f8c.ngrok-free.app', // ใช้ URL ล่าสุดจาก ngrok
            protocol: 'wss'
        },
    }
});