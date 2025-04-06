<script lang="ts" setup>
import { cn } from '@/lib/utils'
import type { HTMLAttributes } from 'vue'

const props = defineProps<{
    class?: HTMLAttributes['class']
}>()
</script>

<template>
    <p :class="cn('text-muted-foreground text-sm', props.class)" data-slot="card-description">
        <slot />
    </p>
</template>
