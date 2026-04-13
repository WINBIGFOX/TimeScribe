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
                'border-sidebar-border flex min-w-0 translate-x-px flex-col gap-1 py-0.5 not-rtl:ml-3.5 not-rtl:border-l not-rtl:pl-2.5 rtl:mr-3.5 rtl:border-r rtl:pr-2.5',
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
