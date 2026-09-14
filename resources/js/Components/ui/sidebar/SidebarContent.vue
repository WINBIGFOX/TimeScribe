<script lang="ts" setup>
import { cn } from '@/lib/utils'
import type { HTMLAttributes } from 'vue'

const props = defineProps<{
    class?: HTMLAttributes['class']
}>()
</script>

<template>
    <div
        :class="cn('flex min-h-0 flex-1 flex-col gap-2 overflow-y-auto', props.class)"
        data-sidebar="content"
        data-slot="sidebar-content"
    >
        <slot />
    </div>
</template>
