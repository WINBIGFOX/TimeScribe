<script lang="ts" setup>
import { Sheet, SheetContent } from '@/Components/ui/sheet'
import SheetDescription from '@/Components/ui/sheet/SheetDescription.vue'
import SheetHeader from '@/Components/ui/sheet/SheetHeader.vue'
import SheetTitle from '@/Components/ui/sheet/SheetTitle.vue'
import { cn } from '@/lib/utils'
import type { SidebarProps } from '.'
import { SIDEBAR_WIDTH_MOBILE, useSidebar } from './utils'

defineOptions({
    inheritAttrs: false
})

const props = withDefaults(defineProps<SidebarProps>(), {
    side: 'left',
    variant: 'sidebar',
    collapsible: 'offcanvas'
})

const { isMobile, state, openMobile, setOpenMobile } = useSidebar()
</script>

<template>
    <div
        :class="cn('bg-sidebar text-sidebar-foreground flex h-full w-(--sidebar-width) flex-col', props.class)"
        v-bind="$attrs"
        v-if="collapsible === 'none'"
        data-slot="sidebar"
    >
        <slot />
    </div>

    <Sheet :open="openMobile" @update:open="setOpenMobile" v-bind="$attrs" v-else-if="isMobile">
        <SheetContent
            :side="side"
            :style="{
                '--sidebar-width': SIDEBAR_WIDTH_MOBILE
            }"
            class="bg-sidebar text-sidebar-foreground w-(--sidebar-width) p-0 [&>button]:hidden"
            data-mobile="true"
            data-sidebar="sidebar"
            data-slot="sidebar"
        >
            <SheetHeader class="sr-only">
                <SheetTitle>Sidebar</SheetTitle>
                <SheetDescription>Displays the mobile sidebar.</SheetDescription>
            </SheetHeader>
            <div class="flex h-full w-full flex-col">
                <slot />
            </div>
        </SheetContent>
    </Sheet>

    <div
        :data-collapsible="state === 'collapsed' ? collapsible : ''"
        :data-side="side"
        :data-state="state"
        :data-variant="variant"
        class="group peer text-sidebar-foreground hidden md:block"
        v-else
        data-slot="sidebar"
    >
        <!-- This is what handles the sidebar gap on desktop  -->
        <div
            :class="
                cn(
                    'relative w-(--sidebar-width) bg-transparent transition-[width] duration-200 ease-linear',
                    'group-data-[collapsible=offcanvas]:w-0',
                    'group-data-[side=right]:rotate-180',
                    variant === 'floating' || variant === 'inset'
                        ? 'group-data-[collapsible=icon]:w-[calc(var(--sidebar-width-icon)+(--spacing(4)))]'
                        : 'group-data-[collapsible=icon]:w-(--sidebar-width-icon)'
                )
            "
        />
        <div
            :class="
                cn(
                    'fixed inset-y-0 z-10 hidden h-svh w-(--sidebar-width) transition-[left,right,width] duration-200 ease-linear md:flex',
                    side === 'left'
                        ? 'left-0 group-data-[collapsible=offcanvas]:left-[calc(var(--sidebar-width)*-1)]'
                        : 'right-0 group-data-[collapsible=offcanvas]:right-[calc(var(--sidebar-width)*-1)]',
                    // Adjust the padding for floating and inset variants.
                    variant === 'floating' || variant === 'inset'
                        ? 'p-2 group-data-[collapsible=icon]:w-[calc(var(--sidebar-width-icon)+(--spacing(4))+2px)]'
                        : 'group-data-[collapsible=icon]:w-(--sidebar-width-icon) group-data-[side=left]:border-r group-data-[side=right]:border-l',
                    props.class
                )
            "
            v-bind="$attrs"
        >
            <div
                class="bg-sidebar group-data-[variant=floating]:border-sidebar-border flex h-full w-full flex-col group-data-[variant=floating]:rounded-lg group-data-[variant=floating]:border group-data-[variant=floating]:shadow-sm"
                data-sidebar="sidebar"
            >
                <slot />
            </div>
        </div>
    </div>
</template>
