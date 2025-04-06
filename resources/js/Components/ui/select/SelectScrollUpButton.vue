<script lang="ts" setup>
import { cn } from '@/lib/utils'
import { ChevronUp } from 'lucide-vue-next'
import { SelectScrollUpButton, type SelectScrollUpButtonProps, useForwardProps } from 'reka-ui'
import { computed, type HTMLAttributes } from 'vue'

const props = defineProps<SelectScrollUpButtonProps & { class?: HTMLAttributes['class'] }>()

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props

    return delegated
})

const forwardedProps = useForwardProps(delegatedProps)
</script>

<template>
    <SelectScrollUpButton
        :class="cn('flex cursor-default items-center justify-center py-1', props.class)"
        v-bind="forwardedProps"
        data-slot="select-scroll-up-button"
    >
        <slot>
            <ChevronUp class="size-4" />
        </slot>
    </SelectScrollUpButton>
</template>
