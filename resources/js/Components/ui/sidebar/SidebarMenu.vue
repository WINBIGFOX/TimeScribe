<script lang="ts" setup>
import { cn } from '@/lib/utils'
import type { HTMLAttributes } from 'vue'

const props = defineProps<{
    class?: HTMLAttributes['class']
}>()
</script>

<template>
    <ul :class="cn('flex w-full min-w-0 flex-col gap-1', props.class)" data-sidebar="menu" data-slot="sidebar-menu">
        <slot />
    </ul>
</template>
