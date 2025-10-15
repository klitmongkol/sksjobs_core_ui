import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue(),
    ],
    server: {
        host: '0.0.0.0', // listen ทุก interface
        port: 5173,
        hmr: {
            host: '', // ปล่อยว่าง → Vite จะใช้ host ที่ browser เข้ามา
            protocol: 'ws', // ใช้ WebSocket สำหรับ HMR
        },
    },
    resolve: {
        alias: {
            '~bootstrap': 'bootstrap',
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
});