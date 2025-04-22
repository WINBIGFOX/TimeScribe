<script lang="ts" setup>
import { RangeCalendarGridHead, type RangeCalendarGridHeadProps } from 'reka-ui'

const props = defineProps<RangeCalendarGridHeadProps>()
</script>

<template>
    <RangeCalendarGridHead v-bind="props" data-slot="range-calendar-grid-head">
        <slot />
    </RangeCalendarGridHead>
</template>
