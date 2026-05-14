import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { Buffer } from 'buffer';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    define: {
        // Polyfill Buffer for browser (required by circomlibjs)
        global: 'globalThis',
    },
    resolve: {
        alias: {
            buffer: 'buffer',
        },
    },
    optimizeDeps: {
        esbuildOptions: {
            define: {
                global: 'globalThis',
            },
        },
    },
    build: {
        rollupOptions: {
            output: {
                // Preserve original filenames for .wasm and .zkey assets
                assetFileNames: (assetInfo) => {
                    if (assetInfo.name && (assetInfo.name.endsWith('.wasm') || assetInfo.name.endsWith('.zkey'))) {
                        return '[name][extname]';
                    }
                    return 'assets/[name]-[hash][extname]';
                },
            },
        },
    },
    // Exclude .wasm and .zkey from Vite's default asset handling.
    // Files in public/zkp/ (vote.wasm, vote_final.zkey, vkey.json) are
    // served as-is by the web server without any Vite transformation.
    assetsInclude: [],
});
