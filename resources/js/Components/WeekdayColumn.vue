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
    <div class="flex flex-col gap-4">
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
        />
    </div>
</template>
