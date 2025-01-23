<script setup lang="ts">
import { Calendar } from '@/Components/ui/calendar';
import WeekdayColumn from '@/Components/WeekdayColumn.vue';
import WorktimeProgressBar from '@/Components/WorktimeProgressBar.vue';
import { Date, WeekdayObject } from '@/types';
import { Head, router } from '@inertiajs/vue3';
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
    (newVal) => {
        router.visit(
            route('overview.show', {
                date: `${newVal.year}-${newVal.month}-${newVal.day}`,
            }),
            {
                preserveScroll: true,
                preserveState: true,
            },
        );
    },
);

const isDateUnavailable: CalendarRootProps['isDateUnavailable'] = (date) => {
    console.log(date);
    return (
        props.holidays.filter((holiday) => {
            const day = date.day < 10 ? `0${date.day}` : date.day;
            const month = date.month < 10 ? `0${date.month}` : date.month;
            return holiday.date === `${date.year}-${month}-${day}`;
        }).length > 0
    );
};
</script>

<template>
    <Head title="Stempeluhr" />

    <div
        class="sticky top-0 flex h-10 items-center justify-center font-medium backdrop-blur"
        style="-webkit-app-region: drag"
    >
        Stempeluhr
    </div>
    <div class="flex select-none gap-4">
        <div>
            <Calendar
                fixed-weeks
                :is-date-unavailable="isDateUnavailable"
                v-model="selectedDate"
                locale="de-DE"
            />
        </div>
        <div class="flex grow flex-col">
            <div class="grid grow grid-cols-7">
                <WeekdayColumn
                    weekday-name="Mo"
                    :weekday="props.weekdays.monday"
                />

                <WeekdayColumn
                    weekday-name="Di"
                    :weekday="props.weekdays.tuesday"
                />
                <WeekdayColumn
                    weekday-name="Mi"
                    :weekday="props.weekdays.wednesday"
                />
                <WeekdayColumn
                    weekday-name="Do"
                    :weekday="props.weekdays.thursday"
                />
                <WeekdayColumn
                    weekday-name="Fr"
                    :weekday="props.weekdays.friday"
                />
                <WeekdayColumn
                    weekday-name="Sa"
                    :weekday="props.weekdays.saturday"
                />
                <WeekdayColumn
                    weekday-name="So"
                    :weekday="props.weekdays.sunday"
                />
            </div>
        </div>
        <div class="flex flex-col gap-4 px-4">
            <div class="flex h-14 flex-col items-center">
                <span class="font-medium leading-none text-muted-foreground">
                    Woche
                </span>
                <span
                    class="mt-0.5 flex grow items-center text-3xl font-bold leading-none text-foreground"
                >
                    {{ props.week }}
                </span>
            </div>
            <WorktimeProgressBar
                :progress="
                    (props.weekWorkTime / (props.weekPlan * 60 * 60)) * 100
                "
                :plan="props.weekPlan"
            />
        </div>
    </div>
</template>
