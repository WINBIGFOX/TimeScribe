<script lang="ts" setup>
import { cn } from '@/lib/utils'
import { SelectLabel, type SelectLabelProps } from 'reka-ui'
import type { HTMLAttributes } from 'vue'

const props = defineProps<SelectLabelProps & { class?: HTMLAttributes['class'] }>()
</script>

<template>
    <SelectLabel :class="cn('px-2 py-1.5 text-sm font-medium', props.class)" data-slot="select-label">
        <slot />
    </SelectLabel>
</template>
