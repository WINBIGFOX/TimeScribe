<script lang="ts" setup>
import { cn } from '@/lib/utils'
import { RangeCalendarCell, type RangeCalendarCellProps, useForwardProps } from 'reka-ui'
import { computed, type HTMLAttributes } from 'vue'

const props = defineProps<RangeCalendarCellProps & { class?: HTMLAttributes['class'] }>()

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props

    return delegated
})

const forwardedProps = useForwardProps(delegatedProps)
</script>

<template>
    <RangeCalendarCell
        :class="
            cn(
                '[&:has([data-selected])]:bg-accent relative p-0 text-center text-sm focus-within:relative focus-within:z-20 first:[&:has([data-selected])]:rounded-l-md last:[&:has([data-selected])]:rounded-r-md [&:has([data-selected][data-selection-end])]:rounded-r-md [&:has([data-selected][data-selection-start])]:rounded-l-md',
                props.class
            )
        "
        v-bind="forwardedProps"
        data-slot="range-calendar-cell"
    >
        <slot />
    </RangeCalendarCell>
</template>
