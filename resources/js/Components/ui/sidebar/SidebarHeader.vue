<script lang="ts" setup>
import { cn } from '@/lib/utils'
import type { HTMLAttributes } from 'vue'

const props = defineProps<{
    class?: HTMLAttributes['class']
}>()
</script>

<template>
    <div :class="cn('flex flex-col gap-2 p-2', props.class)" data-sidebar="header" data-slot="sidebar-header">
        <slot />
    </div>
</template>
