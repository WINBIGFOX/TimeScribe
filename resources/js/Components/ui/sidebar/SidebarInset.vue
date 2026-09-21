<script lang="ts" setup>
import { cn } from '@/lib/utils'
import type { HTMLAttributes } from 'vue'

const props = defineProps<{
    class?: HTMLAttributes['class']
}>()
</script>

<template>
    <main
        :class="
            cn(
                'bg-background dark:border-border/70 relative flex w-full flex-1 flex-col not-rtl:border-l rtl:border-r',
                props.class
            )
        "
        data-slot="sidebar-inset"
    >
        <slot />
    </main>
</template>
