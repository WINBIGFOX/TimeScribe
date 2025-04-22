<script lang="ts" setup>
import { cn } from '@/lib/utils'
import { DialogDescription, type DialogDescriptionProps } from 'reka-ui'
import { computed, type HTMLAttributes } from 'vue'

const props = defineProps<DialogDescriptionProps & { class?: HTMLAttributes['class'] }>()

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props

    return delegated
})
</script>

<template>
    <DialogDescription
        :class="cn('text-muted-foreground text-sm', props.class)"
        v-bind="delegatedProps"
        data-slot="sheet-description"
    >
        <slot />
    </DialogDescription>
</template>
