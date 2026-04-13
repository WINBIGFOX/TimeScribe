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
                'bg-background relative flex w-full flex-1 flex-col',
                'md:peer-data-[variant=inset]:m-2 md:peer-data-[variant=inset]:rounded-xl md:peer-data-[variant=inset]:shadow-sm not-rtl:md:peer-data-[variant=inset]:ml-0 not-rtl:md:peer-data-[variant=inset]:peer-data-[state=collapsed]:ml-2 rtl:md:peer-data-[variant=inset]:mr-0 rtl:md:peer-data-[variant=inset]:peer-data-[state=collapsed]:mr-2',
                props.class
            )
        "
        data-slot="sidebar-inset"
    >
        <slot />
    </main>
</template>
