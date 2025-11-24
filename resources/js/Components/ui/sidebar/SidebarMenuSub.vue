<script lang="ts" setup>
import { cn } from '@/lib/utils'
import type { HTMLAttributes } from 'vue'

const props = defineProps<{
    class?: HTMLAttributes['class']
}>()
</script>

<template>
    <ul
        :class="
            cn(
                'border-sidebar-border ml-3.5 flex min-w-0 translate-x-px flex-col gap-1 border-l pl-2.5 py-0.5',
                'group-data-[collapsible=icon]:hidden',
                props.class
            )
        "
        data-sidebar="menu-badge"
        data-slot="sidebar-menu-sub"
    >
        <slot />
    </ul>
</template>
