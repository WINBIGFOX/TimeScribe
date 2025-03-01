import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { modal } from 'inertia-modal';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, DefineComponent, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';

createInertiaApp({
    title: (title) => title,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(modal, {
                resolve: (name: string) =>
                    resolvePageComponent(
                        `./Pages/${name}.vue`,
                        import.meta.glob<DefineComponent>('./Pages/**/*.vue'),
                    ),
            })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
}).then();
