<script lang="ts" setup>
import WeekdayHeader from '@/Components/WeekdayHeader.vue'
import WorktimeProgressBar from '@/Components/WorktimeProgressBar.vue'
import { WeekdayObject } from '@/types'

const props = defineProps<{
    weekday: WeekdayObject
}>()
</script>

<template>
    <div
        class="hover:ring-offset-background hover:ring-primary/30 flex w-14 flex-col gap-4 rounded-lg pb-2 transition-all hover:ring-2 hover:ring-offset-2"
    >
        <div>
            <WeekdayHeader :date="props.weekday.date.date" />
        </div>
        <WorktimeProgressBar
            :absences="props.weekday.absences"
            :active-work="props.weekday.activeWork"
            :break-time="props.weekday.breakTime"
            :fallback-plan="props.weekday.fallbackPlan"
            :plan="props.weekday.plan"
            :work-time="props.weekday.workTime"
            :is-holiday="props.weekday.isHoliday"
        />
    </div>
</template>
