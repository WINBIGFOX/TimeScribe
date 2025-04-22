<script lang="ts" setup>
import type { LucideIcon } from 'lucide-vue-next'

import {
    SidebarGroup,
    SidebarGroupContent,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem
} from '@/Components/ui/sidebar'

const props = defineProps<{
    items: {
        title: string
        url: string
        icon: LucideIcon
    }[]
}>()
</script>

<template>
    <SidebarGroup>
        <SidebarGroupContent>
            <SidebarMenu>
                <SidebarMenuItem :key="item.title" v-for="item in props.items">
                    <SidebarMenuButton as-child size="sm">
                        <a :href="item.url">
                            <component :is="item.icon" />
                            <span>{{ item.title }}</span>
                        </a>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarGroupContent>
    </SidebarGroup>
</template>
