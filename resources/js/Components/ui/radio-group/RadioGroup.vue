<script setup lang="ts">
import { cn } from '@/lib/utils'
import { reactiveOmit } from '@vueuse/core'
import type { RadioGroupRootEmits, RadioGroupRootProps } from 'reka-ui'
import { RadioGroupRoot, useForwardPropsEmits } from 'reka-ui'
import type { HTMLAttributes } from 'vue'

const props = defineProps<RadioGroupRootProps & { class?: HTMLAttributes['class'] }>()
const emits = defineEmits<RadioGroupRootEmits>()

const delegatedProps = reactiveOmit(props, 'class')

const forwarded = useForwardPropsEmits(delegatedProps, emits)
</script>

<template>
    <RadioGroupRoot
        v-slot="slotProps"
        data-slot="radio-group"
        :class="cn('grid gap-3', props.class)"
        v-bind="forwarded"
    >
        <slot v-bind="slotProps" />
    </RadioGroupRoot>
</template>
