<script lang="ts" setup>
import TimestampTypeBadge from '@/Components/TimestampTypeBadge.vue'
import WeekdayColumn from '@/Components/WeekdayColumn.vue'
import WorktimeProgressBar from '@/Components/WorktimeProgressBar.vue'
import { TimeWheel } from '@/Components/ui-custom/time-wheel'
import { Button } from '@/Components/ui/button'
import { Date, WeekdayObject } from '@/types'
import { Head, Link, router } from '@inertiajs/vue3'
import { CalendarDate, type DateValue } from '@internationalized/date'
import moment from 'moment/min/moment-with-locales'
import { Ref, ref, watch } from 'vue'

const props = defineProps<{
    date: string
    week: number
    startOfWeek: string
    endOfWeek: string
    weekWorkTime: number
    weekBreakTime: number
    weekPlan?: number
    weekFallbackPlan?: number
    weekDatesWithTimestamps: string[]
    holidays: Date[]
    balance: number
    lastCalendarWeek: number
    weekdays: Record<string, WeekdayObject>
}>()

const routeDate = moment(props.date, 'DD.MM.YYYY')

const selectedDate = ref(new CalendarDate(routeDate.year(), routeDate.month() + 1, routeDate.date())) as Ref<DateValue>

watch(
    () => selectedDate.value,
    (newVal) => visitDate(newVal.toString())
)

const visitDate = (date: string) => {
    router.visit(
        route('overview.show', {
            date: date
        }),
        {
            preserveScroll: true,
            preserveState: true
        }
    )
}

const openDayView = (date: string) => {
    router.visit(
        route('overview.day.show', {
            date
        }),
        {
            preserveScroll: true,
            preserveState: true
        }
    )
}
</script>

<template>
    <Head title="Week Overview" />

    <div class="mb-4 flex items-center gap-4">
        <div class="text-foreground/80 text-base font-medium">{{ $t('app.weekly overview') }}</div>
        <div class="flex flex-1 items-center justify-center text-sm">
            <TimeWheel :date="props.date" route="overview.week.show" type="week" />
        </div>
        <div>
            <Button
                :as="Link"
                :href="route('overview.week.show', { date: moment().format('YYYY-MM-DD') })"
                size="sm"
                variant="outline"
            >
                {{ $t('app.today') }}
            </Button>
        </div>
    </div>
    <div class="border-border relative mb-6 flex grow gap-8 border-b">
        <div class="flex grow flex-col">
            <div class="flex grow justify-between">
                <WeekdayColumn
                    :key="weekday.date.date"
                    :weekday="weekday"
                    @click="openDayView(weekday.date.date)"
                    v-for="weekday in props.weekdays"
                />
            </div>
        </div>
        <div class="flex w-14 flex-col gap-4 pb-2">
            <div class="flex h-14 flex-col items-center">
                <span class="text-muted-foreground leading-none font-medium">
                    {{ $t('app.week') }}
                </span>
                <span class="text-foreground mt-0.5 flex grow items-center text-3xl leading-none font-bold">
                    {{ props.week }}
                </span>
            </div>
            <WorktimeProgressBar
                :absences="[]"
                :break-time="props.weekBreakTime"
                :fallback-plan="props.weekFallbackPlan"
                :plan="props.weekPlan"
                :progress="(props.weekWorkTime / (props.weekPlan * 60 * 60)) * 100"
                :work-time="props.weekWorkTime"
                v-if="props.weekPlan"
            />
        </div>
        <div class="border-border absolute inset-x-0 bottom-18 border-t" />
    </div>
    <div class="mb-6 flex gap-2">
        <TimestampTypeBadge :duration="props.weekWorkTime" type="work" />
        <TimestampTypeBadge :duration="props.weekBreakTime" type="break" />
        <TimestampTypeBadge
            :duration="Math.max(props.weekWorkTime - (props.weekPlan ?? 0) * 60 * 60, 0)"
            type="overtime"
        />
        <TimestampTypeBadge :duration="(props.weekPlan ?? 0) * 60 * 60" type="plan" />
        <TimestampTypeBadge
            :duration="props.balance + Math.max(props.weekWorkTime - (props.weekPlan ?? 0) * 60 * 60, 0)"
            class="ml-auto"
            type="balance"
        />
    </div>
</template>
