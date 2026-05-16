<script lang="ts" setup>
import { Separator } from '@/Components/ui/separator'
import { cn } from '@/lib/utils'
import type { HTMLAttributes } from 'vue'

const props = defineProps<{
    class?: HTMLAttributes['class']
}>()
</script>

<template>
    <div
        :class="cn('relative -my-2 h-5 text-sm group-data-[variant=outline]/field-group:-mb-2', props.class)"
        :data-content="!!$slots.default"
        data-slot="field-separator"
    >
        <Separator class="absolute inset-0 top-1/2" />
        <span
            class="bg-background text-muted-foreground relative mx-auto block w-fit px-2"
            v-if="$slots.default"
            data-slot="field-separator-content"
        >
            <slot />
        </span>
    </div>
</template>
