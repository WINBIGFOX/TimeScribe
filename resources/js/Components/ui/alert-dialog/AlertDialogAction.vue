<script lang="ts" setup>
import { buttonVariants } from '@/Components/ui/button'
import { cn } from '@/lib/utils'
import { AlertDialogAction, type AlertDialogActionProps } from 'reka-ui'
import { computed, type HTMLAttributes } from 'vue'

const props = defineProps<AlertDialogActionProps & { class?: HTMLAttributes['class'] }>()

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props

    return delegated
})
</script>

<template>
    <AlertDialogAction :class="cn(buttonVariants(), props.class)" v-bind="delegatedProps">
        <slot />
    </AlertDialogAction>
</template>
