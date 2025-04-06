<script lang="ts" setup>
import { cn } from '@/lib/utils'
import type { HTMLAttributes } from 'vue'

const props = defineProps<{
    class?: HTMLAttributes['class']
}>()
</script>

<template>
    <h3 :class="cn('leading-none font-semibold', props.class)" data-slot="card-title">
        <slot />
    </h3>
</template>
