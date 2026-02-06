<script lang="ts" setup>
import { secToFormat } from '@/lib/utils'
import {
    BriefcaseBusiness,
    ChevronsLeftRightEllipsis,
    ClockArrowUp,
    Coffee,
    Cross,
    Diff,
    Drama,
    TreePalm
} from 'lucide-vue-next'
import { computed } from 'vue'

const props = defineProps<{
    type: string
    duration?: number
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

const durationLabel = computed(() => secToFormat(props.duration ?? 0, true, true, true))
</script>

<template>
    <div :class="badgeColor" class="flex items-center gap-2 rounded-lg px-4 py-2">
        <component :is="badgeIcon" class="size-5" />

        <div class="space-y-1">
            <div class="text-xs leading-none">{{ $t(badgeTitle) }}</div>
            <div class="text-sm leading-none font-bold tabular-nums" v-if="props.duration !== undefined">
                {{ durationLabel }}
                {{ durationLabel.includes(':') ? $t('app.h') : $t('app.min') }}
            </div>
        </div>
    </div>
</template>
