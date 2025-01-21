<script setup lang="ts">
import WeekdayHeader from '@/Components/WeekdayHeader.vue';
import { WeekdayObject } from '@/types';
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
            />
        </div>

        <div
            v-if="weekday.plan"
            class="relative mx-auto w-14 grow rounded-lg bg-muted"
        >
            <div
                class="absolute inset-x-0 bottom-0 rounded-lg bg-primary transition-all duration-300"
                :style="{
                    height: `${percentage}%`,
                }"
            />
        </div>
    </div>
</template>
