<script setup lang="ts">
import { Button } from '@/Components/ui/button';
import { Absence, Date } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { useColorMode, useThrottleFn } from '@vueuse/core';
import {
    BriefcaseBusiness,
    ChevronLeft,
    ChevronRight,
    Cross,
    Drama,
    Trash,
    TreePalm,
} from 'lucide-vue-next';
import moment from 'moment/min/moment-with-locales';
import { computed } from 'vue';

moment.locale('de');

const props = defineProps<{
    absences: Absence[];
    holidays: Date[];
    workdaysPlan: Record<
        | 'monday'
        | 'tuesday'
        | 'wednesday'
        | 'thursday'
        | 'friday'
        | 'saturday'
        | 'sunday',
        number
    >;
    date: string;
}>();

const calendar = computed(() => {
    const startMonth = momentSelectedDate.value.clone().startOf('month');
    const startWeek = startMonth.clone().startOf('isoWeek');

    const days = Array.from({ length: 6 * 7 }, (_, i) =>
        startWeek.clone().add(i, 'days'),
    );

    return Array.from({ length: 6 }, (_, i) => days.slice(i * 7, i * 7 + 7));
});

const momentSelectedDate = computed(() => moment(props.date));

const addMonth = () => {
    router.visit(
        route('absence.show', {
            date: momentSelectedDate.value.add(1, 'month').format('YYYY-MM-DD'),
        }),
        {
            preserveScroll: true,
            preserveState: true,
        },
    );
};

const setToday = () => {
    router.visit(
        route('absence.show', { date: moment().format('YYYY-MM-DD') }),
    );
};

const subtractMonth = () => {
    router.visit(
        route('absence.show', {
            date: momentSelectedDate.value
                .subtract(1, 'month')
                .format('YYYY-MM-DD'),
        }),
        {
            preserveScroll: true,
            preserveState: true,
        },
    );
};

const onWheel = useThrottleFn(($event: WheelEvent) => {
    if ($event.deltaY > 0) {
        addMonth();
    } else {
        subtractMonth();
    }
}, 600);

const createAbsence = (
    type: 'vacation' | 'sick',
    date: string,
    duration?: number,
) => {
    router.post(
        route('absence.store', {
            date: props.date,
        }),
        {
            type,
            date: date + ' 00:00:00',
            duration,
        },
        {
            preserveScroll: true,
            preserveState: true,
        },
    );
};

const removeAbsence = (id: number) => {
    router.delete(
        route('absence.destroy', {
            date: props.date,
            absence: id,
        }),
        {
            preserveScroll: true,
            preserveState: true,
        },
    );
};

const absences = computed(() =>
    props.absences.reduce(
        (acc, absence) => {
            acc[absence.date.date] = absence;
            return acc;
        },
        {} as Record<string, Absence>,
    ),
);

const holidays = computed(() =>
    props.holidays.reduce(
        (acc, holiday) => {
            acc[holiday.date] = holiday;
            return acc;
        },
        {} as Record<string, Date>,
    ),
);

const workdaysPlan = computed(() => {
    return Object.values(props.workdaysPlan);
});

useColorMode();
</script>

<template>
    <Head title="Stempeluhr" />

    <div
        class="sticky top-0 flex h-10 shrink-0 items-center justify-center font-medium"
        style="-webkit-app-region: drag"
    >
        Abwesenheiten
    </div>
    <div class="flex items-center justify-between px-4 py-2 select-none">
        <div class="text-3xl font-bold">
            {{ momentSelectedDate.format('MMMM YYYY') }}
        </div>
        <div class="flex items-center">
            <Button
                size="icon"
                class="h-6 w-6"
                variant="outline"
                @click="subtractMonth"
            >
                <ChevronLeft />
            </Button>
            <Button class="h-6 font-light" variant="outline" @click="setToday">
                Heute
            </Button>
            <Button
                size="icon"
                class="h-6 w-6"
                variant="outline"
                @click="addMonth"
            >
                <ChevronRight />
            </Button>
        </div>
    </div>
    <div
        class="flex grow flex-col divide-y select-none"
        ref="el"
        @wheel="onWheel"
    >
        <div class="grid h-8 grid-cols-7">
            <div class="px-2 text-right font-light" :key="day" v-for="day in 7">
                {{
                    moment()
                        .weekday(day - 1)
                        .format('dd')
                }}
            </div>
        </div>

        <div
            class="grid flex-1 grid-cols-7 divide-x"
            :key="weekIndex"
            v-for="(week, weekIndex) in calendar"
        >
            <div
                :key="dayIndex"
                v-for="(day, dayIndex) in week"
                class="group relative flex flex-col gap-0.5 p-0.5"
                :class="{
                    'bg-muted text-muted-foreground dark:bg-muted/50':
                        dayIndex >= 5 || holidays[day.format('YYYY-MM-DD')],
                    'text-muted-foreground/50': !day.isSame(
                        momentSelectedDate,
                        'month',
                    ),
                }"
            >
                <div class="flex justify-between">
                    <div v-if="!holidays[day.format('YYYY-MM-DD')]">
                        <div
                            v-if="workdaysPlan[dayIndex]"
                            :class="{
                                'line-through decoration-red-500 decoration-2 opacity-25':
                                    absences[day.format('YYYY-MM-DD')],
                            }"
                            class="bg-muted text-muted-foreground mt-1 ml-1 flex items-center gap-1 rounded-full px-2 text-sm"
                        >
                            <BriefcaseBusiness class="size-4" />
                            {{ workdaysPlan[dayIndex] }} Std.
                        </div>
                    </div>
                    <Drama class="mt-1 ml-1 size-5" v-else />
                    <div
                        :class="{
                            'bg-primary text-primary-foreground': day.isSame(
                                moment(),
                                'date',
                            ),
                            'px-1': day.format('D') === '1',
                        }"
                        class="flex h-7 min-w-7 items-center justify-center rounded-full"
                    >
                        {{
                            day.format(day.format('D') === '1' ? 'D MMM' : 'D')
                        }}
                    </div>
                </div>
                <div
                    v-if="!absences[day.format('YYYY-MM-DD')]"
                    class="text-foreground hidden items-center justify-center gap-2 group-hover:flex"
                >
                    <Button
                        v-if="
                            !holidays[day.format('YYYY-MM-DD')] &&
                            workdaysPlan[dayIndex]
                        "
                        class="rounded-full"
                        variant="outline"
                        size="icon"
                        @click="
                            createAbsence('vacation', day.format('YYYY-MM-DD'))
                        "
                    >
                        <TreePalm />
                    </Button>
                    <Button
                        @click="createAbsence('sick', day.format('YYYY-MM-DD'))"
                        class="rounded-full"
                        variant="outline"
                        size="icon"
                    >
                        <Cross />
                    </Button>
                </div>
                <div
                    v-if="absences[day.format('YYYY-MM-DD')]"
                    class="group/absence relative flex grow items-start justify-center text-sm"
                >
                    <div
                        class="text-foreground absolute inset-x-1 top-0 bottom-1 flex items-center justify-center gap-2 rounded-lg opacity-0 backdrop-blur-3xl transition-all delay-200 duration-300 group-hover/absence:opacity-100"
                        @click="
                            removeAbsence(absences[day.format('YYYY-MM-DD')].id)
                        "
                    >
                        <Trash class="size-5" />
                        Entfernen
                    </div>
                    <div
                        class="text-background flex items-center gap-2 rounded-full bg-emerald-500 px-2 py-1 pr-3"
                        v-if="
                            absences[day.format('YYYY-MM-DD')].type ===
                            'vacation'
                        "
                    >
                        <TreePalm class="size-5" />
                        Urlaub
                    </div>

                    <div
                        class="text-background flex items-center gap-2 rounded-full bg-rose-400 px-2 py-1 pr-3"
                        v-if="
                            absences[day.format('YYYY-MM-DD')].type === 'sick'
                        "
                    >
                        <Cross class="size-5" />
                        Krank
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
