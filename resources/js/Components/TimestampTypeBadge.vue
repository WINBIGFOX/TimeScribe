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

const badgeTitle = (() => {
    switch (props.type) {
        case 'work':
            return 'Arbeitszeit';
        case 'break':
            return 'Pausenzeit';
        case 'overtime':
            return 'Überstunden';
        case 'plan':
            return 'Regelarbeitszeit';
        case 'noWork':
            return 'Auszeit';
        default:
            return 'Unbekannt';
    }
})();

const badgeIcon = (() => {
    switch (props.type) {
        case 'work':
            return BriefcaseBusiness;
        case 'break':
            return Coffee;
        case 'overtime':
            return ClockArrowUp;
        case 'noWork':
            return ChevronsLeftRightEllipsis;
        default:
            return undefined;
    }
})();

const badgeColor = (() => {
    switch (props.type) {
        case 'work':
            return 'bg-primary text-primary-foreground';
        case 'break':
            return 'bg-pink-400 text-primary-foreground';
        case 'overtime':
            return 'bg-amber-400 text-primary-foreground';
        case 'noWork':
            return 'bg-rose-400 text-primary-foreground';
        default:
            return 'bg-muted text-muted-foreground';
    }
})();

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
