<script lang="ts" setup>
import type { DrawerCloseProps } from 'vaul-vue'
import { DrawerClose } from 'vaul-vue'

const props = defineProps<DrawerCloseProps>()
</script>

<template>
    <DrawerClose v-bind="props" data-slot="drawer-close">
        <slot />
    </DrawerClose>
</template>
