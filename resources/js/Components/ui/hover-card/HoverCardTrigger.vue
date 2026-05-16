<script lang="ts" setup>
import type { HoverCardTriggerProps } from 'reka-ui'
import { HoverCardTrigger } from 'reka-ui'

const props = defineProps<HoverCardTriggerProps>()
</script>

<template>
    <HoverCardTrigger v-bind="props" data-slot="hover-card-trigger">
        <slot />
    </HoverCardTrigger>
</template>
