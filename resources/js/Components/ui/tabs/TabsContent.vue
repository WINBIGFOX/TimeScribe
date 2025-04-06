<script lang="ts" setup>
import { cn } from '@/lib/utils'
import { TabsContent, type TabsContentProps } from 'reka-ui'
import { computed, type HTMLAttributes } from 'vue'

const props = defineProps<TabsContentProps & { class?: HTMLAttributes['class'] }>()

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props

    return delegated
})
</script>

<template>
    <TabsContent :class="cn('flex-1 outline-none', props.class)" v-bind="delegatedProps" data-slot="tabs-content">
        <slot />
    </TabsContent>
</template>
