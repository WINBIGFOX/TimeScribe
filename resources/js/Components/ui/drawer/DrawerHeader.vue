<script lang="ts" setup>
import { cn } from '@/lib/utils'
import type { HtmlHTMLAttributes } from 'vue'

const props = defineProps<{
    class?: HtmlHTMLAttributes['class']
}>()
</script>

<template>
    <div :class="cn('flex flex-col gap-1.5 p-4', props.class)" data-slot="drawer-header">
        <slot />
    </div>
</template>
