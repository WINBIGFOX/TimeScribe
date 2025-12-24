import type { Page, PageProps } from '@inertiajs/core'
import { AxiosInstance } from 'axios'
import type { trans as translate, transChoice as translateChoice } from 'laravel-vue-i18n'
import type { ComponentCustomProperties as VueComponentCustomProperties } from 'vue'
import { route as ziggyRoute } from 'ziggy-js'
import { PageProps as AppPageProps } from './'

declare global {
    interface Window {
        axios: AxiosInstance
        /* eslint-disable-next-line @typescript-eslint/no-explicit-any */
        Native: any
    }

    let route: typeof ziggyRoute
}

declare module 'vue' {
    interface ComponentCustomProperties {
        route: typeof ziggyRoute
        $page: Page<PageProps>
        $t: typeof translate
        $tChoice: typeof translateChoice
    }
}

declare module '@vue/runtime-core' {
    interface ComponentCustomProperties extends VueComponentCustomProperties {
        route: typeof ziggyRoute
        $page: Page<PageProps>
        $t: typeof translate
        $tChoice: typeof translateChoice
    }
}

declare module '@inertiajs/core' {
    interface PageProps extends AppPageProps {
        errors?: {
            confirmationModal?: string
        }
    }
}
