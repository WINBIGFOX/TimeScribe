<script lang="ts" setup>
import { DropdownMenuSub, type DropdownMenuSubEmits, type DropdownMenuSubProps, useForwardPropsEmits } from 'reka-ui'

const props = defineProps<DropdownMenuSubProps>()
const emits = defineEmits<DropdownMenuSubEmits>()

const forwarded = useForwardPropsEmits(props, emits)
</script>

<template>
    <DropdownMenuSub v-bind="forwarded" data-slot="dropdown-menu-sub">
        <slot />
    </DropdownMenuSub>
</template>
