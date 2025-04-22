<script lang="ts" setup>
import { cn } from '@/lib/utils'
import { RangeCalendarHeadCell, type RangeCalendarHeadCellProps, useForwardProps } from 'reka-ui'
import { computed, type HTMLAttributes } from 'vue'

const props = defineProps<RangeCalendarHeadCellProps & { class?: HTMLAttributes['class'] }>()

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props

    return delegated
})

const forwardedProps = useForwardProps(delegatedProps)
</script>

<template>
    <RangeCalendarHeadCell
        :class="cn('text-muted-foreground w-8 rounded-md text-[0.8rem] font-normal', props.class)"
        v-bind="forwardedProps"
        data-slot="range-calendar-head-cell"
    >
        <slot />
    </RangeCalendarHeadCell>
</template>
