<script lang="ts" setup>
import { usePage } from '@inertiajs/vue3'
import moment from 'moment/min/moment-with-locales'

moment.locale(usePage().props.js_locale)
if (window.Native) {
    window.Native.on('App\\Events\\LocaleChanged', () => {
        window.location.reload()
    })
}
</script>

<template>
    <slot />
</template>
