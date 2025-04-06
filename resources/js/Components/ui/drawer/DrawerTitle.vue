<script lang="ts" setup>
import { cn } from '@/lib/utils'
import type { DrawerTitleProps } from 'vaul-vue'
import { DrawerTitle } from 'vaul-vue'
import { computed, type HtmlHTMLAttributes } from 'vue'

const props = defineProps<DrawerTitleProps & { class?: HtmlHTMLAttributes['class'] }>()

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props

    return delegated
})
</script>

<template>
    <DrawerTitle
        :class="cn('text-foreground font-semibold', props.class)"
        v-bind="delegatedProps"
        data-slot="drawer-title"
    >
        <slot />
    </DrawerTitle>
</template>
