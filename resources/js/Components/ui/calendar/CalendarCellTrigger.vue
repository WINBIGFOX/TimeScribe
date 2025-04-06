<script lang="ts" setup>
import { buttonVariants } from '@/Components/ui/button'
import { cn } from '@/lib/utils'
import { CalendarCellTrigger, type CalendarCellTriggerProps, useForwardProps } from 'reka-ui'
import { computed, type HTMLAttributes } from 'vue'

const props = withDefaults(defineProps<CalendarCellTriggerProps & { class?: HTMLAttributes['class'] }>(), {
    as: 'button'
})

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props

    return delegated
})

const forwardedProps = useForwardProps(delegatedProps)
</script>

<template>
    <CalendarCellTrigger
        :class="
            cn(
                buttonVariants({ variant: 'ghost' }),
                'size-8 cursor-default p-0 font-normal aria-selected:opacity-100',
                '[&[data-today]:not([data-selected])]:bg-accent [&[data-today]:not([data-selected])]:text-accent-foreground',
                // Selected
                'data-[selected]:bg-primary data-[selected]:text-primary-foreground data-[selected]:hover:bg-primary data-[selected]:hover:text-primary-foreground data-[selected]:focus:bg-primary data-[selected]:focus:text-primary-foreground data-[selected]:opacity-100',
                // Disabled
                'data-[disabled]:text-muted-foreground data-[disabled]:opacity-50',
                // Unavailable
                'data-[unavailable]:line-through',
                // Outside months
                'data-[outside-view]:text-muted-foreground',
                props.class
            )
        "
        v-bind="forwardedProps"
        data-slot="calendar-cell-trigger"
    >
        <slot />
    </CalendarCellTrigger>
</template>
