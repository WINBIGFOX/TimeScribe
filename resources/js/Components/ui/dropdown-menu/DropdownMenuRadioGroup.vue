<script lang="ts" setup>
import {
    DropdownMenuRadioGroup,
    type DropdownMenuRadioGroupEmits,
    type DropdownMenuRadioGroupProps,
    useForwardPropsEmits
} from 'reka-ui'

const props = defineProps<DropdownMenuRadioGroupProps>()
const emits = defineEmits<DropdownMenuRadioGroupEmits>()

const forwarded = useForwardPropsEmits(props, emits)
</script>

<template>
    <DropdownMenuRadioGroup v-bind="forwarded" data-slot="dropdown-menu-radio-group">
        <slot />
    </DropdownMenuRadioGroup>
</template>
