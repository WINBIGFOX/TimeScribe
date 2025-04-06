<script lang="ts" setup>
import { cn } from '@/lib/utils'
import type { HTMLAttributes } from 'vue'

const props = defineProps<{
    class?: HTMLAttributes['class']
}>()
</script>

<template>
    <div
        :class="cn('flex flex-col-reverse gap-2 sm:flex-row sm:justify-end', props.class)"
        data-slot="alert-dialog-footer"
    >
        <slot />
    </div>
</template>
