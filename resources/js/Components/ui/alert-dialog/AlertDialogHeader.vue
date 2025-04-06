<script lang="ts" setup>
import { cn } from '@/lib/utils'
import type { HTMLAttributes } from 'vue'

const props = defineProps<{
    class?: HTMLAttributes['class']
}>()
</script>

<template>
    <div :class="cn('flex flex-col gap-2 text-center sm:text-left', props.class)" data-slot="alert-dialog-header">
        <slot />
    </div>
</template>
