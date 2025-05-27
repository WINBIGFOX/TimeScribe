<script lang="ts" setup>
import NavMain from '@/Components/NavMain.vue'

import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    type SidebarProps
} from '@/Components/ui/sidebar'
import { Link } from '@inertiajs/vue3'
import { Bug, Power } from 'lucide-vue-next'

const props = withDefaults(defineProps<SidebarProps>(), {
    variant: 'inset'
})
</script>

<template>
    <Sidebar class="relative pt-8" v-bind="props">
        <div class="absolute inset-x-0 top-0 h-20" style="-webkit-app-region: drag" />
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem class="flex">
                    <div
                        class="bg-primary font-lobster-two text-primary-foreground flex size-8 items-center justify-center rounded-md text-lg font-bold italic"
                    >
                        TS
                    </div>

                    <div class="font-lobster-two ml-2 grid flex-1 text-left text-2xl leading-tight font-bold italic">
                        TimeScribe
                    </div>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>
        <SidebarContent>
            <NavMain />
        </SidebarContent>
        <SidebarFooter>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuItem size="lg">
                        <div class="flex h-6 items-center">
                            <SidebarMenuButton :as="Link" :href="route('quit')" class="w-auto">
                                <Power />
                            </SidebarMenuButton>

                            <SidebarMenuButton
                                :as="Link"
                                :class="{
                                    'text-primary!': route().current() === 'bug-and-feedback.index'
                                }"
                                :href="route('bug-and-feedback.index')"
                                class="w-auto"
                                prefetch
                            >
                                <Bug />
                            </SidebarMenuButton>
                            <Link
                                :href="route('updater.check')"
                                class="ml-auto space-x-1 text-left text-sm leading-tight"
                                method="post"
                                preserve-scroll
                                preserve-state
                            >
                                <span class="font-medium">{{ $t('app.version') }}</span>
                                <span class="text-xs">{{ $page.props.app_version }}</span>
                            </Link>
                        </div>
                    </SidebarMenuItem>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarFooter>
    </Sidebar>
</template>
