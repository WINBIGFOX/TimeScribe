<script lang="ts" setup>
import {
    SidebarGroup,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem
} from '@/Components/ui/sidebar'
import { Link, router, usePage } from '@inertiajs/vue3'
import { AppWindowMac, ChartColumnBig, Cog, FileChartColumn, FileClock, Tag, TentTree } from 'lucide-vue-next'
import moment from 'moment/min/moment-with-locales'
import { ref } from 'vue'

const date = ref(moment(usePage().props.date ?? undefined, 'DD.MM.YYYY').format('YYYY-MM-DD'))
const current = ref(route().current())

router.on('navigate', () => {
    date.value = moment(usePage().props.date ?? undefined, 'DD.MM.YYYY').format('YYYY-MM-DD')
    current.value = route().current()
})
</script>

<template>
    <SidebarGroup>
        <SidebarMenu>
            <SidebarMenuItem>
                <SidebarMenuButton as-child>
                    <Link :href="route('home')" prefetch>
                        <ChartColumnBig />
                        {{ $t('app.overview') }}
                    </Link>
                </SidebarMenuButton>
                <SidebarMenuSub>
                    <SidebarMenuSubItem>
                        <SidebarMenuSubButton as-child>
                            <Link
                                :class="{
                                    'text-primary! font-bold': [
                                        'overview.day.show',
                                        'timestamp.create',
                                        'timestamp.edit'
                                    ].includes(current ?? '')
                                }"
                                :href="route('overview.day.show', { date })"
                                class="transition-all duration-200"
                                prefetch
                            >
                                {{ $t('app.day') }}
                            </Link>
                        </SidebarMenuSubButton>
                    </SidebarMenuSubItem>
                    <SidebarMenuSubItem>
                        <SidebarMenuSubButton as-child>
                            <Link
                                :class="{
                                    'text-primary! font-bold': current === 'overview.week.show'
                                }"
                                :href="route('overview.week.show', { date })"
                                class="transition-all duration-200"
                                prefetch
                            >
                                {{ $t('app.week') }}
                            </Link>
                        </SidebarMenuSubButton>
                    </SidebarMenuSubItem>
                    <SidebarMenuSubItem>
                        <SidebarMenuSubButton as-child>
                            <Link
                                :class="{
                                    'text-primary! font-bold': current === 'overview.month.show'
                                }"
                                :href="route('overview.month.show', { date })"
                                class="transition-all duration-200"
                                prefetch
                            >
                                {{ $t('app.month') }}
                            </Link>
                        </SidebarMenuSubButton>
                    </SidebarMenuSubItem>
                    <SidebarMenuSubItem>
                        <SidebarMenuSubButton as-child>
                            <Link
                                :class="{
                                    'text-primary! font-bold': current === 'overview.year.show'
                                }"
                                :href="route('overview.year.show', { date })"
                                class="transition-all duration-200"
                                prefetch
                            >
                                {{ $t('app.year') }}
                            </Link>
                        </SidebarMenuSubButton>
                    </SidebarMenuSubItem>
                </SidebarMenuSub>
            </SidebarMenuItem>
            <SidebarMenuItem>
                <SidebarMenuButton as-child>
                    <Link
                        :class="{
                            'text-primary! font-bold': ['project.index', 'project.edit', 'project.create'].includes(
                                current ?? ''
                            )
                        }"
                        :href="route('project.index')"
                        class="transition-all duration-200"
                        prefetch
                    >
                        <Tag />
                        {{ $t('app.projects') }}
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
            <SidebarMenuItem>
                <SidebarMenuButton as-child>
                    <Link
                        :class="{
                            'text-primary! font-bold': current === 'app-activity.index'
                        }"
                        :href="route('app-activity.index')"
                        class="transition-all duration-200"
                        prefetch
                    >
                        <AppWindowMac />
                        {{ $t('app.app activities') }}
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
            <SidebarMenuItem>
                <SidebarMenuButton as-child>
                    <Link
                        :class="{
                            'text-primary! font-bold': current === 'absence.show'
                        }"
                        :href="route('absence.show', { date })"
                        class="transition-all duration-200"
                        prefetch
                    >
                        <TentTree />
                        {{ $t('app.absences and leave') }}
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
            <SidebarMenuItem>
                <SidebarMenuButton as-child>
                    <Link
                        :class="{
                            'text-primary! font-bold': ['work-schedule.index', 'work-schedule.edit'].includes(
                                current ?? ''
                            )
                        }"
                        :href="route('work-schedule.index')"
                        class="transition-all duration-200"
                        prefetch
                    >
                        <FileClock />
                        {{ $t('app.work schedule') }}
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
            <SidebarMenuItem>
                <SidebarMenuButton as-child>
                    <Link
                        :class="{
                            'text-primary! font-bold': ['import-export.index', 'import.clockify.create'].includes(
                                current ?? ''
                            )
                        }"
                        :href="route('import-export.index')"
                        class="transition-all duration-200"
                        prefetch
                    >
                        <FileChartColumn />
                        {{ $t('app.import / export') }}
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
            <SidebarMenuItem>
                <SidebarMenuButton as-child>
                    <Link
                        :class="{
                            'text-primary! font-bold': current === 'settings.index'
                        }"
                        :href="route('settings.index')"
                        class="transition-all duration-200"
                        prefetch
                    >
                        <Cog />
                        {{ $t('app.settings') }}
                    </Link>
                </SidebarMenuButton>
                <SidebarMenuSub>
                    <SidebarMenuSubItem>
                        <SidebarMenuSubButton as-child>
                            <Link
                                :class="{
                                    'text-primary! font-bold': current === 'settings.general.edit'
                                }"
                                :href="route('settings.general.edit')"
                                class="transition-all duration-200"
                                prefetch
                            >
                                {{ $t('app.general') }}
                            </Link>
                        </SidebarMenuSubButton>
                    </SidebarMenuSubItem>
                    <SidebarMenuSubItem>
                        <SidebarMenuSubButton as-child>
                            <Link
                                :class="{
                                    'text-primary! font-bold': current === 'settings.start-stop.edit'
                                }"
                                :href="route('settings.start-stop.edit')"
                                class="transition-all duration-200"
                                prefetch
                            >
                                {{ $t('app.auto start/break') }}
                            </Link>
                        </SidebarMenuSubButton>
                    </SidebarMenuSubItem>
                </SidebarMenuSub>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
