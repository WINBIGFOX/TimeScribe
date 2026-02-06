<script lang="ts" setup>
import TimestampTypeBadge from '@/Components/TimestampTypeBadge.vue'
import WeekdayColumn from '@/Components/WeekdayColumn.vue'
import WorktimeProgressBar from '@/Components/WorktimeProgressBar.vue'
import { PageHeader } from '@/Components/ui-custom/page-header'
import { TimeWheel } from '@/Components/ui-custom/time-wheel'
import { Button } from '@/Components/ui/button'
import { WeekdayObject } from '@/types'
import { Head, Link, router } from '@inertiajs/vue3'
import moment from 'moment/min/moment-with-locales'

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
    start_balance: number
    lastCalendarWeek: number
    hasWorkSchedules: boolean
    weekdays: Record<string, WeekdayObject>
}>()

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

const reload = () => {
    router.flushAll()
    router.reload({
        showProgress: false
    })
}

if (window.Native) {
    window.Native.on('App\\Events\\TimerStarted', reload)
    window.Native.on('App\\Events\\TimerStopped', reload)
}
</script>

<template>
    <Head title="Week Overview" />
    <PageHeader :title="$t('app.weekly overview')">
        <div class="flex flex-1 items-center justify-center text-sm">
            <TimeWheel :date="props.date" route="overview.week.show" type="week" />
        </div>
        <Button
            :as="Link"
            :href="route('overview.week.show', { date: moment().format('YYYY-MM-DD') })"
            prefetch
            size="sm"
            variant="outline"
        >
            {{ $t('app.today') }}
        </Button>
    </PageHeader>

    <div class="border-border relative mb-6 flex grow gap-8 border-b">
        <div class="flex grow flex-col">
            <div class="flex grow justify-between">
                <WeekdayColumn
                    :has-work-schedule="props.hasWorkSchedules"
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
                :has-work-schedule="props.hasWorkSchedules"
                :absences="[]"
                :break-time="props.weekBreakTime"
                :fallback-plan="props.weekFallbackPlan"
                :plan="props.weekPlan"
                :progress="(props.weekWorkTime / ((props.weekPlan ?? 0) * 60 * 60)) * 100"
                :work-time="props.weekWorkTime"
                v-if="props.weekPlan || props.weekWorkTime"
            />
        </div>
        <div class="border-border absolute inset-x-0 bottom-18 border-t" />
    </div>
    <div class="flex gap-2">
        <TimestampTypeBadge :duration="props.weekWorkTime" type="work" />
        <TimestampTypeBadge :duration="props.weekBreakTime" type="break" />
        <TimestampTypeBadge
            v-if="props.hasWorkSchedules"
            :duration="Math.max(props.weekWorkTime - (props.weekPlan ?? 0) * 60 * 60, 0)"
            type="overtime"
        />
        <TimestampTypeBadge v-if="props.hasWorkSchedules" :duration="(props.weekPlan ?? 0) * 60 * 60" type="plan" />
        <Link
            class="ml-auto"
            preserve-state
            preserve-scroll
            :href="route('overtime-adjustment.show', { date: props.date })"
        >
            <TimestampTypeBadge
                v-if="props.hasWorkSchedules"
                :duration="props.start_balance + Math.max(props.weekWorkTime - (props.weekPlan ?? 0) * 60 * 60, 0)"
                type="balance"
            />
        </Link>
    </div>
</template>
