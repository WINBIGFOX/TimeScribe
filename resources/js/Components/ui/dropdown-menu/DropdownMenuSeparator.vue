<script lang="ts" setup>
import { cn } from '@/lib/utils'
import { DropdownMenuSeparator, type DropdownMenuSeparatorProps } from 'reka-ui'
import { computed, type HTMLAttributes } from 'vue'

const props = defineProps<
    DropdownMenuSeparatorProps & {
        class?: HTMLAttributes['class']
    }
>()

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props

    return delegated
})
</script>

<template>
    <DropdownMenuSeparator
        :class="cn('bg-border -mx-1 my-1 h-px', props.class)"
        v-bind="delegatedProps"
        data-slot="dropdown-menu-separator"
    />
</template>
