<script lang="ts" setup>
import { DialogTrigger, type DialogTriggerProps } from 'reka-ui'

const props = defineProps<DialogTriggerProps>()
</script>

<template>
    <DialogTrigger v-bind="props" data-slot="sheet-trigger">
        <slot />
    </DialogTrigger>
</template>
