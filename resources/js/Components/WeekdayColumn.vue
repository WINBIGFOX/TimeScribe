<script setup lang="ts">
import WeekdayHeader from '@/Components/WeekdayHeader.vue';
import WorktimeProgressBar from '@/Components/WorktimeProgressBar.vue';
import { WeekdayObject } from '@/types';
import moment from 'moment';

const props = defineProps<{
    weekdayName: string;
    weekday: WeekdayObject;
}>();
</script>

<template>
    <div
        class="hover:ring-offset-background hover:ring-primary/30 flex w-14 flex-col gap-4 rounded-lg transition-all hover:ring-2 hover:ring-offset-2"
    >
        <div>
            <WeekdayHeader
                :day="props.weekday.date.day"
                :weekday-name="props.weekdayName"
                :active="
                    props.weekday.date.date === moment().format('YYYY-MM-DD')
                "
            />
        </div>
        <WorktimeProgressBar
            :plan="props.weekday.plan"
            :fallback-plan="props.weekday.fallbackPlan"
            :work-time="props.weekday.workTime"
            :break-time="props.weekday.breakTime"
            :active-work="props.weekday.activeWork"
        />
    </div>
</template>
