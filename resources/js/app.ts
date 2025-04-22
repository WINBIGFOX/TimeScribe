import './bootstrap'

import DefaultLayout from '@/Layouts/DefaultLayout.vue'

import BasicLayout from '@/Layouts/BasicLayout.vue'
import { createInertiaApp } from '@inertiajs/vue3'
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
        if (name === 'MenuBar' || name.startsWith('Welcome')) {
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
                return await languages[`../../lang/${lang}.json`]()
            },
            onLoad: () => {
                if (!app._container) {
                    app.mount(el)
                }
            }
        })
    },
    progress: {
        color: '#00C9DB'
    }
}).then()
