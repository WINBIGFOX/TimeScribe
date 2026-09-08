<script lang="ts" setup>
import { cn } from '@/lib/utils'
import { ChevronRight } from '@lucide/vue'
import { reactiveOmit } from '@vueuse/core'
import { DropdownMenuSubTrigger, type DropdownMenuSubTriggerProps, useForwardProps } from 'reka-ui'
import type { HTMLAttributes } from 'vue'

const props = defineProps<DropdownMenuSubTriggerProps & { class?: HTMLAttributes['class']; inset?: boolean }>()

const delegatedProps = reactiveOmit(props, 'class', 'inset')
const forwardedProps = useForwardProps(delegatedProps)
</script>

<template>
    <DropdownMenuSubTrigger
        :class="
            cn(
                'focus:bg-accent focus:text-accent-foreground data-[state=open]:bg-accent data-[state=open]:text-accent-foreground flex cursor-default items-center rounded-sm px-2 py-1.5 text-sm outline-hidden select-none data-[inset]:ps-8',
                props.class
            )
        "
        v-bind="forwardedProps"
        data-slot="dropdown-menu-sub-trigger"
    >
        <slot />
        <ChevronRight class="ms-auto size-4 rtl:-scale-x-100" />
    </DropdownMenuSubTrigger>
</template>
