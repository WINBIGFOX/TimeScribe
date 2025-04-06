<script lang="ts" setup>
import { cn } from '@/lib/utils'
import { AlertDialogTitle, type AlertDialogTitleProps } from 'reka-ui'
import { computed, type HTMLAttributes } from 'vue'

const props = defineProps<AlertDialogTitleProps & { class?: HTMLAttributes['class'] }>()

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props

    return delegated
})
</script>

<template>
    <AlertDialogTitle
        :class="cn('text-lg font-semibold', props.class)"
        v-bind="delegatedProps"
        data-slot="alert-dialog-title"
    >
        <slot />
    </AlertDialogTitle>
</template>
