<script lang="ts" setup>
import { DialogClose, type DialogCloseProps } from 'reka-ui'

const props = defineProps<DialogCloseProps>()
</script>

<template>
    <DialogClose v-bind="props" data-slot="sheet-close">
        <slot />
    </DialogClose>
</template>
