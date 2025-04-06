<script lang="ts" setup>
import { TooltipTrigger, type TooltipTriggerProps } from 'reka-ui'

const props = defineProps<TooltipTriggerProps>()
</script>

<template>
    <TooltipTrigger v-bind="props" data-slot="tooltip-trigger">
        <slot />
    </TooltipTrigger>
</template>
