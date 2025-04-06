<script lang="ts" setup>
import { cn } from '@/lib/utils'
import type { DrawerDescriptionProps } from 'vaul-vue'
import { DrawerDescription } from 'vaul-vue'
import { computed, type HtmlHTMLAttributes } from 'vue'

const props = defineProps<DrawerDescriptionProps & { class?: HtmlHTMLAttributes['class'] }>()

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props

    return delegated
})
</script>

<template>
    <DrawerDescription
        :class="cn('text-muted-foreground text-sm', props.class)"
        v-bind="delegatedProps"
        data-slot="drawer-description"
    >
        <slot />
    </DrawerDescription>
</template>
