<script lang="ts" setup>
import { cn } from '@/lib/utils'
import type { PrimitiveProps } from 'reka-ui'
import { Primitive } from 'reka-ui'
import { computed, type HTMLAttributes } from 'vue'
import { type BadgeVariants, badgeVariants } from '.'

const props = defineProps<
    PrimitiveProps & {
        variant?: BadgeVariants['variant']
        class?: HTMLAttributes['class']
    }
>()

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props

    return delegated
})
</script>

<template>
    <Primitive :class="cn(badgeVariants({ variant }), props.class)" v-bind="delegatedProps" data-slot="badge">
        <slot />
    </Primitive>
</template>
