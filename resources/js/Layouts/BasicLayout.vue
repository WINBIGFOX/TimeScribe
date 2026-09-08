<script lang="ts" setup>
import { usePage } from '@inertiajs/vue3'
import moment from 'moment/min/moment-with-locales'
import { ConfigProvider } from 'reka-ui'
import { loadLanguageAsync } from 'laravel-vue-i18n'
import { ref, watch } from 'vue'

const page = usePage()
const renderedLocale = ref(page.props.js_locale)
watch(
    () => [page.props.language, page.props.js_locale, page.props.direction],
    () => {
        document.documentElement.lang = page.props.language.replace('_', '-')
        document.documentElement.dir = page.props.direction
        moment.locale(page.props.js_locale)
    },
    { immediate: true }
)
watch(
    () => [page.props.language, page.props.js_locale],
    async ([language, locale]) => {
        await loadLanguageAsync(language)
        renderedLocale.value = locale
    }
)
</script>

<template>
    <ConfigProvider :dir="page.props.direction" :key="renderedLocale" :locale="`${page.props.js_locale}-u-ca-gregory`">
        <slot />
    </ConfigProvider>
</template>
