<script lang="ts" setup>
import { cn } from '@/lib/utils'
import type { HTMLAttributes } from 'vue'

const props = defineProps<{
    class?: HTMLAttributes['class']
}>()
</script>

<template>
    <li :class="cn('group/menu-item relative', props.class)" data-sidebar="menu-item" data-slot="sidebar-menu-item">
        <slot />
    </li>
</template>
