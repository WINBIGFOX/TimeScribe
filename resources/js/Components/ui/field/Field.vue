<script lang="ts" setup>
import { cn } from '@/lib/utils'
import type { HTMLAttributes } from 'vue'
import type { FieldVariants } from '.'
import { fieldVariants } from '.'

const props = defineProps<{
    class?: HTMLAttributes['class']
    orientation?: FieldVariants['orientation']
}>()
</script>

<template>
    <div
        :class="cn(fieldVariants({ orientation }), props.class)"
        :data-orientation="orientation"
        role="group"
        data-slot="field"
    >
        <slot />
    </div>
</template>
