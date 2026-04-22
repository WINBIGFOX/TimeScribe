<script lang="ts" setup>
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger
} from '@/Components/ui/dropdown-menu'
import { formatDurationWithUnit } from '@/lib/utils'
import { GetTimeProjectDetails } from '@/types'
import { Link } from '@inertiajs/vue3'
import {
    BriefcaseBusiness,
    ChevronsLeftRightEllipsis,
    ClockArrowUp,
    Coffee,
    Cross,
    Diff,
    Drama,
    Tag,
    TreePalm
} from '@lucide/vue'
import { computed } from 'vue'

const props = defineProps<{
    type: string
    duration?: number
    projectDurations?: Record<string, GetTimeProjectDetails>
}>()

const badgeDetails = {
    vacation: {
        title: 'app.leave',
        icon: TreePalm,
        color: 'bg-emerald-500 text-primary-foreground'
    },
    sick: {
        title: 'app.sick',
        icon: Cross,
        color: 'bg-rose-400 text-primary-foreground'
    },
    holiday: {
        title: 'app.holiday',
        icon: Drama,
        color: 'bg-purple-400 text-primary-foreground'
    },
    work: {
        title: 'app.work hours',
        icon: BriefcaseBusiness,
        color: 'bg-primary text-primary-foreground'
    },
    break: {
        title: 'app.break time',
        icon: Coffee,
        color: 'bg-pink-400 text-primary-foreground'
    },
    overtime: {
        title: 'app.overtime',
        icon: ClockArrowUp,
        color: 'bg-amber-400 text-primary-foreground'
    },
    noWork: {
        title: 'app.idle time',
        icon: ChevronsLeftRightEllipsis,
        color: 'bg-rose-400 text-primary-foreground'
    },
    plan: {
        title: 'app.scheduled hours',
        icon: undefined,
        color: 'bg-muted text-muted-foreground'
    },
    balance: {
        title: 'app.time balance',
        icon: Diff,
        color: 'bg-lime-400 text-primary-foreground ring-lime-400 ring-2 ring-offset-background ring-offset-2  hover:bg-lime-500 hover:ring-offset-1! transition-all animate-[ringOffset_2s_ease-in-out_infinite]'
    },
    default: {
        title: 'Unbekannt',
        icon: undefined,
        color: 'bg-muted text-muted-foreground'
    }
}

const { title: badgeTitle, icon: badgeIcon, color: badgeColor } = badgeDetails[props.type] || badgeDetails.default

const durationLabel = computed(() => formatDurationWithUnit(props.duration ?? 0))
</script>

<template>
    <div :class="badgeColor" class="flex rounded-lg">
        <div class="flex items-center gap-2 px-4 py-2">
            <component :is="badgeIcon" class="size-5" />

            <div class="space-y-1">
                <div class="text-xs leading-none">{{ $t(badgeTitle) }}</div>
                <div class="text-sm leading-none font-bold tabular-nums" v-if="props.duration !== undefined">
                    <bdi>{{ durationLabel }}</bdi>
                </div>
            </div>
        </div>
        <div
            class="border-background/30 not-rtl:border-l rtl:border-r"
            v-if="props.projectDurations && Object.values(props.projectDurations).length > 0"
        >
            <DropdownMenu>
                <DropdownMenuTrigger class="hover:bg-background/20 flex h-full items-center px-1 transition-colors">
                    <Tag class="size-4" />
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" side="top">
                    <DropdownMenuLabel class="rtl:text-right">{{ $t('app.project times') }}</DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem
                        :as="Link"
                        v-for="(projectDuration, key) in props.projectDurations"
                        :href="route('project.show', { project: key })"
                        preserve-scroll
                        preserve-state
                        :key="key"
                        :style="'--project-color: ' + projectDuration.color"
                        class="flex max-w-60 flex-wrap items-stretch gap-x-8 gap-y-2 bg-(--project-color)/10 not-last:mb-1 not-rtl:border-l-6 not-rtl:border-l-(--project-color) hover:bg-(--project-color)/20! rtl:flex-row-reverse rtl:border-r-6 rtl:border-r-(--project-color) dark:bg-(--project-color)/20 dark:hover:bg-(--project-color)/30!"
                    >
                        <span class="flex items-center gap-2 text-xs font-medium rtl:flex-row-reverse">
                            <span class="text-md flex shrink-0 items-center" v-if="projectDuration.icon">
                                {{ projectDuration.icon }}
                            </span>
                            <span class="line-clamp-1">
                                {{ projectDuration.name }}
                            </span>
                        </span>
                        <span
                            class="flex flex-1 shrink-0 items-center gap-1 text-xs leading-none whitespace-nowrap tabular-nums not-rtl:justify-end"
                        >
                            <component :is="badgeIcon" class="size-4" />
                            <span
                                ><bdi>{{ formatDurationWithUnit(projectDuration.sum) }}</bdi></span
                            >
                        </span>
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    </div>
</template>
