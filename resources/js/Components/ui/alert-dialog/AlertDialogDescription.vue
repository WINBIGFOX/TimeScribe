<script lang="ts" setup>
import { cn } from '@/lib/utils'
import { AlertDialogDescription, type AlertDialogDescriptionProps } from 'reka-ui'
import { computed, type HTMLAttributes } from 'vue'

const props = defineProps<AlertDialogDescriptionProps & { class?: HTMLAttributes['class'] }>()

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props

    return delegated
})
</script>

<template>
    <AlertDialogDescription
        :class="cn('text-muted-foreground text-sm', props.class)"
        v-bind="delegatedProps"
        data-slot="alert-dialog-description"
    >
        <slot />
    </AlertDialogDescription>
</template>
