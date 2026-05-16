<script lang="ts" setup>
import { cn } from '@/lib/utils'
import type { HTMLAttributes } from 'vue'

const props = defineProps<{
    class?: HTMLAttributes['class']
    variant?: 'legend' | 'label'
}>()
</script>

<template>
    <legend
        :class="cn('mb-3 font-medium', 'data-[variant=legend]:text-base', 'data-[variant=label]:text-sm', props.class)"
        :data-variant="variant"
        data-slot="field-legend"
    >
        <slot />
    </legend>
</template>
