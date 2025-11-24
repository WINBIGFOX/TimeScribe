<script lang="ts" setup>
import { cn } from '@/lib/utils'
import type { HTMLAttributes } from 'vue'

const props = defineProps<{
    class?: HTMLAttributes['class']
    title?: string
}>()
</script>

<template>
    <div
        data-slot="page-header"
        :class="
            cn('bg-background/50 sticky inset-x-0 top-0 z-10 -mx-8 mb-3 px-8 pt-4 pb-1 backdrop-blur-sm', props.class)
        "
    >
        <div class="flex h-8 items-center justify-between gap-4">
            <div class="text-foreground/80 text-base font-medium">{{ props.title }}</div>
            <slot />
        </div>
    </div>
</template>
