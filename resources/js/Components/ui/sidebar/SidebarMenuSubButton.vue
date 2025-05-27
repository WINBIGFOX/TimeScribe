<script lang="ts" setup>
import { cn } from '@/lib/utils'
import type { PrimitiveProps } from 'reka-ui'
import { Primitive } from 'reka-ui'
import type { HTMLAttributes } from 'vue'

const props = withDefaults(
    defineProps<
        PrimitiveProps & {
            size?: 'sm' | 'md'
            isActive?: boolean
            class?: HTMLAttributes['class']
        }
    >(),
    {
        as: 'a',
        size: 'md'
    }
)
</script>

<template>
    <Primitive
        :as="as"
        :as-child="asChild"
        :class="
            cn(
                'text-sidebar-foreground ring-sidebar-ring hover:bg-sidebar-accent hover:text-sidebar-accent-foreground active:bg-sidebar-accent active:text-sidebar-accent-foreground [&>svg]:text-sidebar-accent-foreground flex min-h-7 min-w-0 -translate-x-px items-center gap-2 overflow-hidden rounded-md px-2 outline-hidden focus-visible:ring-2 disabled:pointer-events-none disabled:opacity-50 aria-disabled:pointer-events-none aria-disabled:opacity-50 [&>span:last-child]:truncate [&>svg]:size-4 [&>svg]:shrink-0',
                'data-[active=true]:bg-sidebar-accent data-[active=true]:text-sidebar-accent-foreground',
                size === 'sm' && 'text-xs',
                size === 'md' && 'text-sm',
                'group-data-[collapsible=icon]:hidden',
                props.class
            )
        "
        :data-active="isActive"
        :data-size="size"
        data-sidebar="menu-sub-button"
        data-slot="sidebar-menu-sub-button"
    >
        <slot />
    </Primitive>
</template>
