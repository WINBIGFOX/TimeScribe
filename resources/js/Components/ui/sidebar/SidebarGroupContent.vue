<script lang="ts" setup>
import { cn } from '@/lib/utils'
import type { HTMLAttributes } from 'vue'

const props = defineProps<{
    class?: HTMLAttributes['class']
}>()
</script>

<template>
    <div :class="cn('w-full text-sm', props.class)" data-sidebar="group-content" data-slot="sidebar-group-content">
        <slot />
    </div>
</template>
