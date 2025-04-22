<script lang="ts" setup>
import { cn } from '@/lib/utils'
import { Primitive, type PrimitiveProps } from 'reka-ui'
import type { HTMLAttributes } from 'vue'
import { type SidebarMenuButtonVariants, sidebarMenuButtonVariants } from '.'

export interface SidebarMenuButtonProps extends PrimitiveProps {
    variant?: SidebarMenuButtonVariants['variant']
    size?: SidebarMenuButtonVariants['size']
    isActive?: boolean
    class?: HTMLAttributes['class']
}

const props = withDefaults(defineProps<SidebarMenuButtonProps>(), {
    as: 'button',
    variant: 'default',
    size: 'default'
})
</script>

<template>
    <Primitive
        :as="as"
        :as-child="asChild"
        :class="cn(sidebarMenuButtonVariants({ variant, size }), props.class)"
        :data-active="isActive"
        :data-size="size"
        v-bind="$attrs"
        data-sidebar="menu-button"
        data-slot="sidebar-menu-button"
    >
        <slot />
    </Primitive>
</template>
