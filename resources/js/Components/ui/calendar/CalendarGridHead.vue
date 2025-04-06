<script lang="ts" setup>
import { CalendarGridHead, type CalendarGridHeadProps } from 'reka-ui'
import type { HTMLAttributes } from 'vue'

const props = defineProps<CalendarGridHeadProps & { class?: HTMLAttributes['class'] }>()
</script>

<template>
    <CalendarGridHead v-bind="props" data-slot="calendar-grid-head">
        <slot />
    </CalendarGridHead>
</template>
