<script lang="ts" setup>
import { cn } from '@/lib/utils'
import { DialogTitle, type DialogTitleProps, useForwardProps } from 'reka-ui'
import { computed, type HTMLAttributes } from 'vue'

const props = defineProps<DialogTitleProps & { class?: HTMLAttributes['class'] }>()

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props

    return delegated
})

const forwardedProps = useForwardProps(delegatedProps)
</script>

<template>
    <DialogTitle
        :class="cn('text-lg leading-none font-semibold', props.class)"
        v-bind="forwardedProps"
        data-slot="dialog-title"
    >
        <slot />
    </DialogTitle>
</template>
