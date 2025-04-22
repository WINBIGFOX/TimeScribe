<script lang="ts" setup>
import { PopoverTrigger, type PopoverTriggerProps } from 'reka-ui'

const props = defineProps<PopoverTriggerProps>()
</script>

<template>
    <PopoverTrigger v-bind="props" data-slot="popover-trigger">
        <slot />
    </PopoverTrigger>
</template>
