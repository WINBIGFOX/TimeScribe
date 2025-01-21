<script setup lang="ts">
import { Calendar } from '@/Components/ui/calendar';
import WeekdayColumn from '@/Components/WeekdayColumn.vue';
import { WeekdayObject } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { CalendarDate, type DateValue } from '@internationalized/date';
import { useColorMode } from '@vueuse/core';
import moment from 'moment';
import { Ref, ref, watch } from 'vue';

const props = defineProps<{
    date: string;
    week: number;
    startOfWeek: string;
    endOfWeek: string;
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
</script>

<template>
    <Head title="Stempeluhr" />

    <div
        class="sticky top-0 flex h-10 items-center justify-center font-medium backdrop-blur"
        style="-webkit-app-region: drag"
    >
        Stempeluhr
    </div>
    <div class="flex gap-4">
        <div>
            <Calendar v-model="selectedDate" locale="de-DE" />
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
        <div class="px-4">
            <div class="flex flex-col items-center">
                <span class="font-medium leading-none text-muted-foreground">
                    Woche
                </span>
                <span class="mt-0.5 text-3xl font-bold leading-none text-foreground">
                    {{ props.week }}
                </span>
            </div>
        </div>
    </div>
</template>
