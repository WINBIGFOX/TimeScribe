<script setup lang="ts">
import { secToFormat } from '@/lib/utils';
import {
    BriefcaseBusiness,
    ChevronsLeftRightEllipsis,
    ClockArrowUp,
    Coffee,
} from 'lucide-vue-next';

const props = defineProps<{
    type: string;
    duration: number;
}>();

const badgeDetails = {
    work: {
        title: 'Arbeitszeit',
        icon: BriefcaseBusiness,
        color: 'bg-primary text-primary-foreground',
    },
    break: {
        title: 'Pausenzeit',
        icon: Coffee,
        color: 'bg-pink-400 text-primary-foreground',
    },
    overtime: {
        title: 'Überstunden',
        icon: ClockArrowUp,
        color: 'bg-amber-400 text-primary-foreground',
    },
    noWork: {
        title: 'Auszeit',
        icon: ChevronsLeftRightEllipsis,
        color: 'bg-rose-400 text-primary-foreground',
    },
    default: {
        title: 'Unbekannt',
        icon: undefined,
        color: 'bg-muted text-muted-foreground',
    },
};

const {
    title: badgeTitle,
    icon: badgeIcon,
    color: badgeColor,
} = badgeDetails[props.type] || badgeDetails.default;

const durationLabel = secToFormat(props.duration, true, true, true);
const durationType = durationLabel.includes(':') ? 'Std.' : 'Min.';
</script>

<template>
    <div
        class="flex items-center gap-2 rounded-xl px-4 py-2"
        :class="badgeColor"
    >
        <Component :is="badgeIcon" class="size-5" />

        <div class="space-y-1">
            <div class="text-xs leading-none">{{ badgeTitle }}</div>
            <div class="text-sm leading-none font-bold">
                {{ durationLabel }} {{ durationType }}
            </div>
        </div>
    </div>
</template>
