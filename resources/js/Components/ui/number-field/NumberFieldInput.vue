<script lang="ts" setup>
import { cn } from '@/lib/utils'
import { NumberFieldInput } from 'reka-ui'
import type { HTMLAttributes } from 'vue'

const props = defineProps<{
    class?: HTMLAttributes['class']
}>()
</script>

<template>
    <NumberFieldInput
        :class="
            cn(
                'border-input placeholder:text-muted-foreground focus-visible:ring-ring dark:bg-input/30 flex h-9 w-full rounded-md border bg-transparent py-1 text-center text-sm shadow-sm transition-colors focus-visible:ring-1 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50',
                props.class
            )
        "
        data-slot="input"
    />
</template>
