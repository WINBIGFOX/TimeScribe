<script lang="ts" setup>
import { cn } from '@/lib/utils'
import type { HTMLAttributes } from 'vue'

const props = defineProps<{
    class?: HTMLAttributes['class']
}>()
</script>

<template>
    <p
        :class="
            cn(
                'text-muted-foreground text-sm leading-normal font-normal group-has-[[data-orientation=horizontal]]/field:text-balance',
                'last:mt-0 nth-last-2:-mt-1 [[data-variant=legend]+&]:-mt-1.5',
                '[&>a:hover]:text-primary [&>a]:underline [&>a]:underline-offset-4',
                props.class
            )
        "
        data-slot="field-description"
    >
        <slot />
    </p>
</template>
