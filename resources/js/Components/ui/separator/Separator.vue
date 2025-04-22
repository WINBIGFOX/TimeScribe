<script lang="ts" setup>
import { cn } from '@/lib/utils'
import { reactiveOmit } from '@vueuse/core'
import { Separator, type SeparatorProps } from 'reka-ui'
import type { HTMLAttributes } from 'vue'

const props = withDefaults(defineProps<SeparatorProps & { class?: HTMLAttributes['class'] }>(), {
    orientation: 'horizontal',
    decorative: true
})

const delegatedProps = reactiveOmit(props, 'class')
</script>

<template>
    <Separator
        :class="
            cn(
                `bg-border shrink-0 data-[orientation=horizontal]:h-px data-[orientation=horizontal]:w-full data-[orientation=vertical]:h-full data-[orientation=vertical]:w-px`,
                props.class
            )
        "
        v-bind="delegatedProps"
        data-slot="separator-root"
    />
</template>
