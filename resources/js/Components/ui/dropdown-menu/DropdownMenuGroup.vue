<script lang="ts" setup>
import { DropdownMenuGroup, type DropdownMenuGroupProps } from 'reka-ui'

const props = defineProps<DropdownMenuGroupProps>()
</script>

<template>
    <DropdownMenuGroup v-bind="props" data-slot="dropdown-menu-group">
        <slot />
    </DropdownMenuGroup>
</template>
