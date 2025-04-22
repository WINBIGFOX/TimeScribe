<script lang="ts" setup>
import { Input } from '@/Components/ui/input'
import { cn } from '@/lib/utils'
import type { HTMLAttributes } from 'vue'

const props = defineProps<{
    class?: HTMLAttributes['class']
}>()
</script>

<template>
    <Input
        :class="cn('bg-background h-8 w-full shadow-none', props.class)"
        data-sidebar="input"
        data-slot="sidebar-input"
    >
        <slot />
    </Input>
</template>
