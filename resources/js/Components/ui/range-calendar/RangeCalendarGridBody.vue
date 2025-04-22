<script lang="ts" setup>
import { RangeCalendarGridBody, type RangeCalendarGridBodyProps } from 'reka-ui'

const props = defineProps<RangeCalendarGridBodyProps>()
</script>

<template>
    <RangeCalendarGridBody v-bind="props" data-slot="range-calendar-grid-body">
        <slot />
    </RangeCalendarGridBody>
</template>
