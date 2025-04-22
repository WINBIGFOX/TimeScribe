<script lang="ts" setup>
import { cn } from '@/lib/utils'
import type { HTMLAttributes } from 'vue'

const props = defineProps<{
    class?: HTMLAttributes['class']
}>()
</script>

<template>
    <li
        :class="cn('group/menu-sub-item relative', props.class)"
        data-sidebar="menu-sub-item"
        data-slot="sidebar-menu-sub-item"
    >
        <slot />
    </li>
</template>
