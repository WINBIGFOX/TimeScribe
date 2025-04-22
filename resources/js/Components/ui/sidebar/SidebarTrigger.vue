<script lang="ts" setup>
import { Button } from '@/Components/ui/button'
import { cn } from '@/lib/utils'
import { PanelLeft } from 'lucide-vue-next'
import type { HTMLAttributes } from 'vue'
import { useSidebar } from './utils'

const props = defineProps<{
    class?: HTMLAttributes['class']
}>()

const { toggleSidebar } = useSidebar()
</script>

<template>
    <Button
        :class="cn('h-7 w-7', props.class)"
        @click="toggleSidebar"
        size="icon"
        variant="ghost"
        data-sidebar="trigger"
        data-slot="sidebar-trigger"
    >
        <PanelLeft />
        <span class="sr-only">Toggle Sidebar</span>
    </Button>
</template>
