<script lang="ts" setup>
import type { DrawerTriggerProps } from 'vaul-vue'
import { DrawerTrigger } from 'vaul-vue'

const props = defineProps<DrawerTriggerProps>()
</script>

<template>
    <DrawerTrigger v-bind="props" data-slot="drawer-trigger">
        <slot />
    </DrawerTrigger>
</template>
