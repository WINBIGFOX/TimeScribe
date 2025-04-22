<script lang="ts" setup>
import { buttonVariants } from '@/Components/ui/button'
import { cn } from '@/lib/utils'
import { ChevronRight } from 'lucide-vue-next'
import { RangeCalendarNext, type RangeCalendarNextProps, useForwardProps } from 'reka-ui'
import { computed, type HTMLAttributes } from 'vue'

const props = defineProps<RangeCalendarNextProps & { class?: HTMLAttributes['class'] }>()

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props

    return delegated
})

const forwardedProps = useForwardProps(delegatedProps)
</script>

<template>
    <RangeCalendarNext
        :class="
            cn(
                buttonVariants({ variant: 'outline' }),
                'absolute right-1',
                'size-7 bg-transparent p-0 opacity-50 hover:opacity-100',
                props.class
            )
        "
        v-bind="forwardedProps"
        data-slot="range-calendar-next-button"
    >
        <slot>
            <ChevronRight class="size-4" />
        </slot>
    </RangeCalendarNext>
</template>
