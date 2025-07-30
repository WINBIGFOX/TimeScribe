import './bootstrap'

import DefaultLayout from '@/Layouts/DefaultLayout.vue'

import BasicLayout from '@/Layouts/BasicLayout.vue'
import { createInertiaApp } from '@inertiajs/vue3'
import * as Sentry from '@sentry/vue'
import { modal } from 'inertia-modal'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { i18nVue } from 'laravel-vue-i18n'
import { createApp, DefineComponent, h } from 'vue'
import VueApexCharts from 'vue3-apexcharts'
import { ZiggyVue } from 'ziggy-js'

createInertiaApp({
    title: (title) => title,
    resolve: async (name) => {
        const page = await resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./Pages/**/*.vue')
        )
        if (
            name === 'MenuBar' ||
            name.startsWith('Welcome') ||
            name.startsWith('Updater') ||
            name.startsWith('FlyTimer')
        ) {
            page.default.layout = BasicLayout
        } else {
            page.default.layout = DefaultLayout
        }
        return page
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(modal, {
                resolve: (name: string) =>
                    resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob<DefineComponent>('./Pages/**/*.vue'))
            })
            .use(plugin)
            .use(ZiggyVue)
            .use(VueApexCharts)
        app.use(i18nVue, {
            fallbackLang: 'en',
            resolve: async (lang: string) => {
                const languages = import.meta.glob('../../lang/*.json')
                if (typeof languages[`../../lang/${lang}.json`] === 'function') {
                    return await languages[`../../lang/${lang}.json`]()
                }
            },
            onLoad: () => {
                if (!app._container) {
                    app.mount(el)
                }
            }
        })
        Sentry.init({
            app,
            release: import.meta.env.VITE_APP_VERSION,
            dsn: import.meta.env.VITE_APP_SENTRY_VUE_DSN
        })
    },
    progress: {
        color: '#00C9DB'
    }
}).then()
