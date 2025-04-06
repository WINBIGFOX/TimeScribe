<script lang="ts" setup>
import { cn } from '@/lib/utils'
import { reactiveOmit } from '@vueuse/core'
import {
    TooltipArrow,
    TooltipContent,
    type TooltipContentEmits,
    type TooltipContentProps,
    TooltipPortal,
    useForwardPropsEmits
} from 'reka-ui'
import type { HTMLAttributes } from 'vue'

defineOptions({
    inheritAttrs: false
})

const props = withDefaults(defineProps<TooltipContentProps & { class?: HTMLAttributes['class'] }>(), {
    sideOffset: 4
})

const emits = defineEmits<TooltipContentEmits>()

const delegatedProps = reactiveOmit(props, 'class')
const forwarded = useForwardPropsEmits(delegatedProps, emits)
</script>

<template>
    <TooltipPortal>
        <TooltipContent
            :class="
                cn(
                    'bg-primary text-primary-foreground animate-in fade-in-0 zoom-in-95 data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=closed]:zoom-out-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2 z-50 w-fit rounded-md px-3 py-1.5 text-xs text-balance',
                    props.class
                )
            "
            v-bind="{ ...forwarded, ...$attrs }"
            data-slot="tooltip-content"
        >
            <slot />

            <TooltipArrow
                class="bg-primary fill-primary z-50 size-2.5 translate-y-[calc(-50%_-_2px)] rotate-45 rounded-[2px]"
            />
        </TooltipContent>
    </TooltipPortal>
</template>
