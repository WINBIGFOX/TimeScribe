import tailwindcss from '@tailwindcss/vite'
import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'
import i18n from 'laravel-vue-i18n/vite'
import path from 'path'
import { defineConfig } from 'vite'

export default defineConfig({
    resolve: {
        alias: {
            'inertia-modal': path.resolve('vendor/emargareten/inertia-modal'),
            'ziggy-js': path.resolve('vendor/tightenco/ziggy')
        }
    },
    plugins: [
        i18n('lang'),
        tailwindcss(),
        laravel({
            input: ['resources/js/app.ts', 'resources/css/app.css'],
            refresh: true
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false
                }
            }
        })
    ],
    build: {
        chunkSizeWarningLimit: 1000,
        rollupOptions: {
            output: {
                entryFileNames: '[hash].js',
                chunkFileNames: `[hash].js`,
                assetFileNames: `[hash].[ext]`
            }
        }
    }
})
