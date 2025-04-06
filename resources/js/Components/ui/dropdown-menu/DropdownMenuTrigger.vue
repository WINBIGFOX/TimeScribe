<script lang="ts" setup>
import { DropdownMenuTrigger, type DropdownMenuTriggerProps, useForwardProps } from 'reka-ui'

const props = defineProps<DropdownMenuTriggerProps>()

const forwardedProps = useForwardProps(props)
</script>

<template>
    <DropdownMenuTrigger v-bind="forwardedProps" data-slot="dropdown-menu-trigger">
        <slot />
    </DropdownMenuTrigger>
</template>
