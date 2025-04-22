<script lang="ts" setup>
import { cn } from '@/lib/utils'
import { DialogTitle, type DialogTitleProps } from 'reka-ui'
import { computed, type HTMLAttributes } from 'vue'

const props = defineProps<DialogTitleProps & { class?: HTMLAttributes['class'] }>()

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props

    return delegated
})
</script>

<template>
    <DialogTitle
        :class="cn('text-foreground font-semibold', props.class)"
        v-bind="delegatedProps"
        data-slot="sheet-title"
    >
        <slot />
    </DialogTitle>
</template>
