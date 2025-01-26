<script setup lang="ts">
import { Calendar } from '@/Components/ui/calendar';
import WeekdayColumn from '@/Components/WeekdayColumn.vue';
import WorktimeProgressBar from '@/Components/WorktimeProgressBar.vue';
import { Date, WeekdayObject } from '@/types';
import { Head, router, usePoll } from '@inertiajs/vue3';
import { CalendarDate, type DateValue } from '@internationalized/date';
import { useColorMode } from '@vueuse/core';
import moment from 'moment';
import { CalendarRootProps } from 'radix-vue';
import { Ref, ref, watch } from 'vue';

const props = defineProps<{
    date: string;
    week: number;
    startOfWeek: string;
    endOfWeek: string;
    weekWorkTime: number;
    weekBreakTime: number;
    weekPlan: number;
    weekDatesWithTimestamps: string[];
    holidays: Date[];
    weekdays: {
        monday: WeekdayObject;
        tuesday: WeekdayObject;
        wednesday: WeekdayObject;
        thursday: WeekdayObject;
        friday: WeekdayObject;
        saturday: WeekdayObject;
        sunday: WeekdayObject;
    };
}>();

useColorMode();
const routeDate = moment(props.date);

const selectedDate = ref(
    new CalendarDate(routeDate.year(), routeDate.month(), routeDate.date()),
) as Ref<DateValue>;

watch(
    () => selectedDate.value,
    (newVal) => visitDate(newVal.toString()),
);

const visitDate = (date: string) => {
    router.visit(
        route('overview.show', {
            date: date,
        }),
        {
            preserveScroll: true,
            preserveState: true,
        },
    );
};

const setVisitDate = (date: string) => {
    const dateObject = moment(date);
    selectedDate.value = new CalendarDate(
        dateObject.year(),
        dateObject.month(),
        dateObject.date(),
    );
};

const isDateUnavailable: CalendarRootProps['isDateUnavailable'] = (
    date: DateValue,
) =>
    props.holidays.filter((holiday) => holiday.date === date.toString())
        .length > 0;
usePoll(10000);
</script>

<template>
    <Head title="Stempeluhr" />

    <div
        class="sticky top-0 flex h-10 items-center justify-center font-medium"
        style="-webkit-app-region: drag"
    >
        Stempeluhr
    </div>
    <div class="flex gap-4 select-none">
        <div>
            <Calendar
                fixed-weeks
                :is-date-unavailable="isDateUnavailable"
                v-model="selectedDate"
                :highlighted="props.weekDatesWithTimestamps"
                locale="de-DE"
            />
        </div>
        <div class="flex grow flex-col">
            <div class="flex grow justify-between">
                <WeekdayColumn
                    @click="setVisitDate(props.weekdays.monday.date.date)"
                    weekday-name="Mo"
                    :weekday="props.weekdays.monday"
                />

                <WeekdayColumn
                    @click="setVisitDate(props.weekdays.tuesday.date.date)"
                    weekday-name="Di"
                    :weekday="props.weekdays.tuesday"
                />
                <WeekdayColumn
                    @click="setVisitDate(props.weekdays.wednesday.date.date)"
                    weekday-name="Mi"
                    :weekday="props.weekdays.wednesday"
                />
                <WeekdayColumn
                    @click="setVisitDate(props.weekdays.thursday.date.date)"
                    weekday-name="Do"
                    :weekday="props.weekdays.thursday"
                />
                <WeekdayColumn
                    @click="setVisitDate(props.weekdays.friday.date.date)"
                    weekday-name="Fr"
                    :weekday="props.weekdays.friday"
                />
                <WeekdayColumn
                    @click="setVisitDate(props.weekdays.saturday.date.date)"
                    weekday-name="Sa"
                    :weekday="props.weekdays.saturday"
                />
                <WeekdayColumn
                    @click="setVisitDate(props.weekdays.sunday.date.date)"
                    weekday-name="So"
                    :weekday="props.weekdays.sunday"
                />
            </div>
        </div>
        <div class="mx-4 flex w-14 flex-col gap-4">
            <div class="flex h-14 flex-col items-center">
                <span class="text-muted-foreground leading-none font-medium">
                    Woche
                </span>
                <span
                    class="text-foreground mt-0.5 flex grow items-center text-3xl leading-none font-bold"
                >
                    {{ props.week }}
                </span>
            </div>
            <WorktimeProgressBar
                :progress="
                    (props.weekWorkTime / (props.weekPlan * 60 * 60)) * 100
                "
                :plan="props.weekPlan"
                :fallback-plan="props.weekPlan"
                :work-time="props.weekWorkTime"
                :break-time="props.weekBreakTime"
            />
        </div>
    </div>
</template>
