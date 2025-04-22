<script lang="ts" setup>
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger
} from '@/Components/ui/dropdown-menu'

import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuAction,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar
} from '@/Components/ui/sidebar'
import { Folder, Forward, type LucideIcon, MoreHorizontal, Trash2 } from 'lucide-vue-next'

defineProps<{
    projects: {
        name: string
        url: string
        icon: LucideIcon
    }[]
}>()

const { isMobile } = useSidebar()
</script>

<template>
    <SidebarGroup class="group-data-[collapsible=icon]:hidden">
        <SidebarGroupLabel>Projects</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem :key="item.name" v-for="item in projects">
                <SidebarMenuButton as-child>
                    <a :href="item.url">
                        <component :is="item.icon" />
                        <span>{{ item.name }}</span>
                    </a>
                </SidebarMenuButton>
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <SidebarMenuAction show-on-hover>
                            <MoreHorizontal />
                            <span class="sr-only">More</span>
                        </SidebarMenuAction>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent
                        :align="isMobile ? 'end' : 'start'"
                        :side="isMobile ? 'bottom' : 'right'"
                        class="w-48 rounded-lg"
                    >
                        <DropdownMenuItem>
                            <Folder class="text-muted-foreground" />
                            <span>View Project</span>
                        </DropdownMenuItem>
                        <DropdownMenuItem>
                            <Forward class="text-muted-foreground" />
                            <span>Share Project</span>
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem>
                            <Trash2 class="text-muted-foreground" />
                            <span>Delete Project</span>
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </SidebarMenuItem>
            <SidebarMenuItem>
                <SidebarMenuButton>
                    <MoreHorizontal />
                    <span>More</span>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
