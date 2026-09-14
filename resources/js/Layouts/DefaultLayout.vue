<script lang="ts" setup>
import AppSidebar from '@/Components/AppSidebar.vue'
import { SidebarInset, SidebarProvider } from '@/Components/ui/sidebar'
import BasicLayout from '@/Layouts/BasicLayout.vue'
import { useColorMode } from '@vueuse/core'
import { Modal } from 'inertia-modal'
useColorMode()
</script>

<template>
    <BasicLayout>
        <SidebarProvider>
            <AppSidebar :side="$page.props.direction === 'rtl' ? 'right' : 'left'" />
            <SidebarInset
                class="overflow-clip not-rtl:md:peer-data-[variant=inset]:rounded-r-lg rtl:md:peer-data-[variant=inset]:rounded-l-lg"
            >
                <div class="flex grow flex-col overflow-y-auto px-8 pb-6 not-has-data-[slot=page-header]:pt-4">
                    <slot />
                </div>
            </SidebarInset>
            <div class="absolute inset-x-0 top-0 -z-10 h-8" style="-webkit-app-region: drag" />
        </SidebarProvider>
        <Modal />
    </BasicLayout>
</template>
