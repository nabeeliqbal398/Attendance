import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    css: {
        devSourcemap: true,
    },
    server: {
        // Defaults unchanged; docker-compose sets VITE_HOST / VITE_HMR_HOST for the node container.
        host: process.env.VITE_HOST || '127.0.0.1',
        cors: true,
        hmr: {
            host: process.env.VITE_HMR_HOST || '127.0.0.1',
        },
        watch: process.env.CHOKIDAR_USEPOLLING ? { usePolling: true } : undefined,
    },
    build: {
        chunkSizeWarningLimit: 1000,
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules')) {
                        return 'vendor';
                    }
                },
            },
        },
    },
});
