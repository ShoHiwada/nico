import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    base: '/build/',
    server: {
        host: '0.0.0.0',
        hmr: {
          host: process.env.VITE_DEV_SERVER_HOST || 'localhost'
        }
      },      
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
