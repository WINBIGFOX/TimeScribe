<script lang="ts" setup>
import { cn } from '@/lib/utils'
import { reactiveOmit } from '@vueuse/core'
import { DropdownMenuLabel, type DropdownMenuLabelProps, useForwardProps } from 'reka-ui'
import type { HTMLAttributes } from 'vue'

const props = defineProps<DropdownMenuLabelProps & { class?: HTMLAttributes['class']; inset?: boolean }>()

const delegatedProps = reactiveOmit(props, 'class', 'inset')
const forwardedProps = useForwardProps(delegatedProps)
</script>

<template>
    <DropdownMenuLabel
        :class="cn('px-2 py-1.5 text-sm font-medium data-[inset]:pl-8', props.class)"
        :data-inset="inset ? '' : undefined"
        v-bind="forwardedProps"
        data-slot="dropdown-menu-label"
    >
        <slot />
    </DropdownMenuLabel>
</template>
