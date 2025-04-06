<script lang="ts" setup>
import { cn } from '@/lib/utils'
import { DialogDescription, type DialogDescriptionProps, useForwardProps } from 'reka-ui'
import { computed, type HTMLAttributes } from 'vue'

const props = defineProps<DialogDescriptionProps & { class?: HTMLAttributes['class'] }>()

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props

    return delegated
})

const forwardedProps = useForwardProps(delegatedProps)
</script>

<template>
    <DialogDescription
        :class="cn('text-muted-foreground text-sm', props.class)"
        v-bind="forwardedProps"
        data-slot="dialog-description"
    >
        <slot />
    </DialogDescription>
</template>
