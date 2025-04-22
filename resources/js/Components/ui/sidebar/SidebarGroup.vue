<script lang="ts" setup>
import { cn } from '@/lib/utils'
import type { HTMLAttributes } from 'vue'

const props = defineProps<{
    class?: HTMLAttributes['class']
}>()
</script>

<template>
    <div
        :class="cn('relative flex w-full min-w-0 flex-col p-2', props.class)"
        data-sidebar="group"
        data-slot="sidebar-group"
    >
        <slot />
    </div>
</template>
