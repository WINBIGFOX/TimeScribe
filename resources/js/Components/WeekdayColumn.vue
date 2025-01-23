<script setup lang="ts">
import WeekdayHeader from '@/Components/WeekdayHeader.vue';
import WorktimeProgressBar from '@/Components/WorktimeProgressBar.vue';
import { WeekdayObject } from '@/types';
import moment from 'moment';
import { computed } from 'vue';

const props = defineProps<{
    weekdayName: string;
    weekday: WeekdayObject;
}>();

const percentage = computed(() => {
    return Math.min(
        (props.weekday.workTime / (props.weekday.plan * 60 * 60)) * 100,
        100,
    );
});
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
            :progress="percentage"
            :plan="props.weekday.plan"
        />
    </div>
</template>
