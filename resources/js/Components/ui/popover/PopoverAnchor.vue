<script lang="ts" setup>
import type { PopoverAnchorProps } from 'reka-ui'
import { PopoverAnchor } from 'reka-ui'

const props = defineProps<PopoverAnchorProps>()
</script>

<template>
    <PopoverAnchor v-bind="props" data-slot="popover-anchor">
        <slot />
    </PopoverAnchor>
</template>
