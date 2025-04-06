<script lang="ts" setup>
import { cn } from '@/lib/utils'
import { SelectSeparator, type SelectSeparatorProps } from 'reka-ui'
import { computed, type HTMLAttributes } from 'vue'

const props = defineProps<SelectSeparatorProps & { class?: HTMLAttributes['class'] }>()

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props

    return delegated
})
</script>

<template>
    <SelectSeparator
        :class="cn('bg-border pointer-events-none -mx-1 my-1 h-px', props.class)"
        v-bind="delegatedProps"
        data-slot="select-separator"
    />
</template>
