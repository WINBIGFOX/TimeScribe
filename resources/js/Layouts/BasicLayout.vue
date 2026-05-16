<script lang="ts" setup>
import { setTimeDisplayFormat } from '@/lib/utils'
import { usePage } from '@inertiajs/vue3'
import moment from 'moment/min/moment-with-locales'
import { watch } from 'vue'

const page = usePage()

moment.locale(page.props.js_locale)
watch(
    () => page.props.time_display_format,
    (format) => {
        setTimeDisplayFormat(format)
    },
    { immediate: true }
)

if (window.Native) {
    window.Native.on('App\\Events\\LocaleChanged', () => {
        window.location.reload()
    })
}
</script>

<template>
    <slot />
</template>
