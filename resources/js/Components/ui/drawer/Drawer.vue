<script lang="ts" setup>
// @ts-nocheck
import { useForwardPropsEmits } from 'reka-ui'
import type { DrawerRootEmits, DrawerRootProps } from 'vaul-vue'
import { DrawerRoot } from 'vaul-vue'

const props = withDefaults(defineProps<DrawerRootProps>(), {
    shouldScaleBackground: true
})

const emits = defineEmits<DrawerRootEmits>()

const forwarded = useForwardPropsEmits(props, emits)
</script>

<template>
    <DrawerRoot v-bind="forwarded" data-slot="drawer">
        <slot />
    </DrawerRoot>
</template>
