<script lang="ts" setup>
import { cn } from '@/lib/utils'
import type { HTMLAttributes } from 'vue'

const props = defineProps<{
    class?: HTMLAttributes['class']
}>()
</script>

<template>
    <div
        :class="cn('group/field-content flex flex-1 flex-col gap-1.5 leading-snug', props.class)"
        data-slot="field-content"
    >
        <slot />
    </div>
</template>
