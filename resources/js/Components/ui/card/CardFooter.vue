<script lang="ts" setup>
import { cn } from '@/lib/utils'
import type { HTMLAttributes } from 'vue'

const props = defineProps<{
    class?: HTMLAttributes['class']
}>()
</script>

<template>
    <div :class="cn('flex items-center px-6 [.border-t]:pt-6', props.class)" data-slot="card-footer">
        <slot />
    </div>
</template>
