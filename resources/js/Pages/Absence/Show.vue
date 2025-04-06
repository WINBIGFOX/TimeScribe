<script lang="ts" setup>
import HourProgressbar from '@/Components/HourProgressbar.vue'
import { Button } from '@/Components/ui/button'
import { Absence, Date } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { useColorMode, useThrottleFn } from '@vueuse/core'
import { BriefcaseBusiness, ChevronLeft, ChevronRight, Cross, Drama, Trash, TreePalm } from 'lucide-vue-next'
import moment from 'moment/min/moment-with-locales'
import { computed } from 'vue'

const props = defineProps<{
    dayOverviews: {
        planTime: number
        workTime: number
        breakTime: number
        noWorkTime: number
    }[]
    absences: Absence[]
    holidays: Date[]
    date: string
}>()

const calendar = computed(() => {
    const startMonth = momentSelectedDate.value.clone().startOf('month')
    const startWeek = startMonth.clone().startOf('week')

    const days = Array.from({ length: 6 * 7 }, (_, i) => startWeek.clone().add(i, 'days'))

    return Array.from({ length: 6 }, (_, i) => days.slice(i * 7, i * 7 + 7))
})

const momentSelectedDate = computed(() => moment(props.date))

const addMonth = () => {
    router.visit(
        route('absence.show', {
            date: momentSelectedDate.value.add(1, 'month').format('YYYY-MM-DD')
        }),
        {
            preserveScroll: true,
            preserveState: true
        }
    )
}

const setToday = () => {
    router.visit(route('absence.show', { date: moment().format('YYYY-MM-DD') }))
}

const subtractMonth = () => {
    router.visit(
        route('absence.show', {
            date: momentSelectedDate.value.subtract(1, 'month').format('YYYY-MM-DD')
        }),
        {
            preserveScroll: true,
            preserveState: true
        }
    )
}

const onWheel = useThrottleFn(($event: WheelEvent) => {
    if ($event.deltaY > 0) {
        addMonth()
    } else {
        subtractMonth()
    }
}, 600)

const createAbsence = (type: 'vacation' | 'sick', date: string, duration?: number) => {
    router.post(
        route('absence.store', {
            date: props.date
        }),
        {
            type,
            date: date + ' 00:00:00',
            duration
        },
        {
            preserveScroll: true,
            preserveState: true
        }
    )
}

const removeAbsence = (id: number) => {
    router.delete(
        route('absence.destroy', {
            date: props.date,
            absence: id
        }),
        {
            preserveScroll: true,
            preserveState: true
        }
    )
}

const absences = computed(() =>
    props.absences.reduce(
        (acc, absence) => {
            acc[absence.date.date] = absence
            return acc
        },
        {} as Record<string, Absence>
    )
)

const holidays = computed(() =>
    props.holidays.reduce(
        (acc, holiday) => {
            acc[holiday.date] = holiday
            return acc
        },
        {} as Record<string, Date>
    )
)

const { state } = useColorMode()
const openDayView = (date: string) => {
    router.visit(
        route('overview.edit', {
            date,
            darkMode: state.value === 'dark' ? 1 : 0
        }),
        {
            preserveScroll: true,
            preserveState: true
        }
    )
}
</script>

<template>
    <Head title="Absence" />

    <div
        class="sticky top-0 flex h-10 shrink-0 items-center justify-center font-medium"
        style="-webkit-app-region: drag"
    >
        {{ $t('app.absences') }}
    </div>
    <div class="flex items-center justify-between px-4 py-2 select-none">
        <div class="text-3xl font-bold">
            {{ momentSelectedDate.format('MMMM YYYY') }}
        </div>
        <div class="flex items-center">
            <Button @click="subtractMonth" class="h-6 w-6" size="icon" variant="outline">
                <ChevronLeft />
            </Button>
            <Button @click="setToday" class="h-6 font-light" variant="outline">
                {{ $t('app.today') }}
            </Button>
            <Button @click="addMonth" class="h-6 w-6" size="icon" variant="outline">
                <ChevronRight />
            </Button>
        </div>
    </div>
    <div @wheel="onWheel" class="flex grow flex-col divide-y select-none" ref="el">
        <div class="grid h-8 grid-cols-7">
            <div :key="day" class="px-2 text-right font-light" v-for="day in 7">
                {{
                    moment()
                        .weekday(day - 1)
                        .format('dd')
                }}
            </div>
        </div>

        <div :key="weekIndex" class="grid flex-1 grid-cols-7 divide-x" v-for="(week, weekIndex) in calendar">
            <div
                :class="{
                    'bg-muted text-muted-foreground dark:bg-muted/50':
                        day.day() === 0 || day.day() === 6 || holidays[day.format('YYYY-MM-DD')],
                    'text-muted-foreground/50': !day.isSame(momentSelectedDate, 'month')
                }"
                :key="dayIndex"
                @dblclick="openDayView(day.format('YYYY-MM-DD'))"
                class="group relative flex flex-col gap-0.5 p-0.5"
                v-for="(day, dayIndex) in week"
            >
                <div class="flex justify-between">
                    <div v-if="!holidays[day.format('YYYY-MM-DD')]">
                        <div
                            :class="{
                                'line-through decoration-red-500 decoration-2 opacity-25':
                                    absences[day.format('YYYY-MM-DD')]
                            }"
                            class="bg-muted text-muted-foreground mt-1 ml-1 flex items-center gap-1 rounded-full px-2 text-sm"
                            v-if="props.dayOverviews[day.format('YYYY-MM-DD')]?.planTime"
                        >
                            <BriefcaseBusiness class="size-4" />
                            {{ props.dayOverviews[day.format('YYYY-MM-DD')].planTime / 3600 }} {{ $t('app.h') }}
                        </div>
                    </div>
                    <Drama class="mt-1 ml-1 size-5" v-else />
                    <div
                        :class="{
                            'bg-primary text-primary-foreground': day.isSame(moment(), 'date'),
                            'px-1': day.format('D') === '1'
                        }"
                        class="flex h-7 min-w-7 items-center justify-center rounded-full"
                    >
                        {{ day.format(day.format('D') === '1' ? 'D MMM' : 'D') }}
                    </div>
                </div>
                <div
                    :key="day.format('YYYY-MM-DD')"
                    class="px-1"
                    v-if="
                        (props.dayOverviews[day.format('YYYY-MM-DD')]?.breakTime ||
                            props.dayOverviews[day.format('YYYY-MM-DD')]?.workTime) &&
                        day.isSameOrBefore()
                    "
                >
                    <HourProgressbar
                        :break-time="props.dayOverviews[day.format('YYYY-MM-DD')].breakTime"
                        :no-work-time="props.dayOverviews[day.format('YYYY-MM-DD')].noWorkTime"
                        :plan-time="props.dayOverviews[day.format('YYYY-MM-DD')].planTime"
                        :work-time="props.dayOverviews[day.format('YYYY-MM-DD')].workTime"
                    />
                </div>
                <div
                    class="text-foreground hidden h-full items-center justify-center gap-2 group-hover:flex"
                    v-if="!absences[day.format('YYYY-MM-DD')]"
                >
                    <Button
                        @click="createAbsence('vacation', day.format('YYYY-MM-DD'))"
                        class="rounded-full"
                        size="icon"
                        v-if="
                            !holidays[day.format('YYYY-MM-DD')] &&
                            props.dayOverviews[day.format('YYYY-MM-DD')]?.planTime
                        "
                        variant="outline"
                    >
                        <TreePalm />
                    </Button>
                    <Button
                        @click="createAbsence('sick', day.format('YYYY-MM-DD'))"
                        class="rounded-full"
                        size="icon"
                        variant="outline"
                    >
                        <Cross />
                    </Button>
                </div>
                <div
                    class="group/absence relative flex grow items-center justify-center text-sm"
                    v-if="absences[day.format('YYYY-MM-DD')]"
                >
                    <div
                        @click="removeAbsence(absences[day.format('YYYY-MM-DD')].id)"
                        class="text-foreground absolute inset-x-1 top-0 bottom-1 flex items-center justify-center gap-2 rounded-lg opacity-0 backdrop-blur-3xl transition-all delay-200 duration-300 group-hover/absence:opacity-100"
                    >
                        <Trash class="size-5" />
                        {{ $t('app.remove') }}
                    </div>
                    <div
                        class="text-background flex items-center gap-2 rounded-full bg-emerald-500 px-2 py-1 pr-3"
                        v-if="absences[day.format('YYYY-MM-DD')].type === 'vacation'"
                    >
                        <TreePalm class="size-5" />
                        {{ $t('app.leave') }}
                    </div>

                    <div
                        class="text-background flex items-center gap-2 rounded-full bg-rose-400 px-2 py-1 pr-3"
                        v-if="absences[day.format('YYYY-MM-DD')].type === 'sick'"
                    >
                        <Cross class="size-5" />
                        {{ $t('app.sick') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
