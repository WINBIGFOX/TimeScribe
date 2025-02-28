<script setup lang="ts">
import { Calendar } from '@/Components/ui/calendar';
import WeekdayColumn from '@/Components/WeekdayColumn.vue';
import WorktimeProgressBar from '@/Components/WorktimeProgressBar.vue';
import { secToFormat } from '@/lib/utils';
import { Date, WeekdayObject } from '@/types';
import { Head, router, usePoll } from '@inertiajs/vue3';
import { CalendarDate, type DateValue } from '@internationalized/date';
import { useColorMode } from '@vueuse/core';
import { ArrowLeftToLine, ClockArrowDown, ClockArrowUp } from 'lucide-vue-next';
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
    weekPlan?: number;
    weekFallbackPlan?: number;
    weekDatesWithTimestamps: string[];
    holidays: Date[];
    balance: number;
    lastCalendarWeek: number;
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
    new CalendarDate(routeDate.year(), routeDate.month() + 1, routeDate.date()),
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
        dateObject.month() + 1,
        dateObject.date(),
    );
};

const isDateUnavailable: CalendarRootProps['isDateUnavailable'] = (
    date: DateValue,
) =>
    props.holidays.filter((holiday) => holiday.date === date.toString())
        .length > 0;
usePoll(10000);

const { state } = useColorMode();

const openDayView = (date: string) => {
    router.visit(
        route('overview.edit', {
            date,
            darkMode: state.value === 'dark' ? 1 : 0,
        }),
        {
            preserveScroll: true,
            preserveState: true,
        },
    );
};
</script>

<template>
    <Head title="TimeScribe" />

    <div
        class="sticky top-0 flex h-10 shrink-0 items-center justify-center font-medium"
        style="-webkit-app-region: drag"
    >
        Übersicht
        <div
            class="absolute top-0 right-2 bottom-0 flex items-center font-normal"
        >
            <div
                class="bg-muted text-muted-foreground flex h-6 items-center rounded-full p-[2px] pl-3 text-xs leading-none"
            >
                <ArrowLeftToLine class="size-3.5" />
                <span class="mr-2 flex h-full items-center">
                    KW {{ props.lastCalendarWeek }}
                </span>
                <div
                    :class="{
                        'text-green-500': props.balance < 0,
                        'text-amber-400': props.balance > 0,
                    }"
                    class="bg-background fle flex h-full items-center gap-1 rounded-full pr-2 pl-1"
                >
                    <ClockArrowUp
                        class="size-3.5 shrink-0"
                        v-if="props.balance >= 0"
                    />
                    <ClockArrowDown
                        class="size-4 shrink-0"
                        v-if="props.balance < 0"
                    />
                    {{ secToFormat(props.balance, false, true, true, true) }}
                </div>
            </div>
        </div>
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
                    @dblclick="openDayView(props.weekdays.monday.date.date)"
                    weekday-name="Mo"
                    :weekday="props.weekdays.monday"
                />

                <WeekdayColumn
                    @click="setVisitDate(props.weekdays.tuesday.date.date)"
                    @dblclick="openDayView(props.weekdays.tuesday.date.date)"
                    weekday-name="Di"
                    :weekday="props.weekdays.tuesday"
                />
                <WeekdayColumn
                    @click="setVisitDate(props.weekdays.wednesday.date.date)"
                    @dblclick="openDayView(props.weekdays.wednesday.date.date)"
                    weekday-name="Mi"
                    :weekday="props.weekdays.wednesday"
                />
                <WeekdayColumn
                    @click="setVisitDate(props.weekdays.thursday.date.date)"
                    @dblclick="openDayView(props.weekdays.thursday.date.date)"
                    weekday-name="Do"
                    :weekday="props.weekdays.thursday"
                />
                <WeekdayColumn
                    @click="setVisitDate(props.weekdays.friday.date.date)"
                    @dblclick="openDayView(props.weekdays.friday.date.date)"
                    weekday-name="Fr"
                    :weekday="props.weekdays.friday"
                />
                <WeekdayColumn
                    @click="setVisitDate(props.weekdays.saturday.date.date)"
                    @dblclick="openDayView(props.weekdays.saturday.date.date)"
                    weekday-name="Sa"
                    :weekday="props.weekdays.saturday"
                />
                <WeekdayColumn
                    @click="setVisitDate(props.weekdays.sunday.date.date)"
                    @dblclick="openDayView(props.weekdays.sunday.date.date)"
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
                v-if="props.weekPlan"
                :progress="
                    (props.weekWorkTime / (props.weekPlan * 60 * 60)) * 100
                "
                :plan="props.weekPlan"
                :fallback-plan="props.weekFallbackPlan"
                :work-time="props.weekWorkTime"
                :break-time="props.weekBreakTime"
                :absences="[]"
            />
        </div>
    </div>
</template>
