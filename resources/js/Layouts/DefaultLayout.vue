<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import moment from 'moment/min/moment-with-locales';

moment.locale(usePage().props.locale);

window.Native.on('App\\Events\\LocaleChanged', () => {
    window.location.reload();
});
</script>

<template>
    <slot />
</template>
