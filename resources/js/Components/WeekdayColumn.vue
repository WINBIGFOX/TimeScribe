<script setup lang="ts">
import WeekdayHeader from '@/Components/WeekdayHeader.vue';
import WorktimeProgressBar from '@/Components/WorktimeProgressBar.vue';
import { WeekdayObject } from '@/types';

const props = defineProps<{
    weekday: WeekdayObject;
}>();
</script>

<template>
    <div
        class="hover:ring-offset-background hover:ring-primary/30 flex w-14 flex-col gap-4 rounded-lg transition-all hover:ring-2 hover:ring-offset-2"
    >
        <div>
            <WeekdayHeader :date="props.weekday.date.date" />
        </div>
        <WorktimeProgressBar
            :plan="props.weekday.plan"
            :fallback-plan="props.weekday.fallbackPlan"
            :work-time="props.weekday.workTime"
            :break-time="props.weekday.breakTime"
            :active-work="props.weekday.activeWork"
            :absences="props.weekday.absences"
        />
    </div>
</template>
