<script lang="ts" setup>
import { cn } from '@/lib/utils'
import type { HTMLAttributes } from 'vue'

interface SkeletonProps {
    class?: HTMLAttributes['class']
}

const props = defineProps<SkeletonProps>()
</script>

<template>
    <div :class="cn('bg-primary/10 animate-pulse rounded-md', props.class)" data-slot="skeleton" />
</template>
