<script lang="ts" setup>
import { SelectGroup, type SelectGroupProps } from 'reka-ui'

const props = defineProps<SelectGroupProps>()
</script>

<template>
    <SelectGroup v-bind="props" data-slot="select-group">
        <slot />
    </SelectGroup>
</template>
