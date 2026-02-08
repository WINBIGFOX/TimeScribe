<script lang="ts" setup>
import ConfirmationDialog from '@/Components/dialogs/ConfirmationDialog.vue'
import Timeline from '@/Components/Timeline.vue'
import TimestampListItem from '@/Components/TimestampListItem.vue'
import TimestampListPlaceholderItem from '@/Components/TimestampListPlaceholderItem.vue'
import TimestampTypeBadge from '@/Components/TimestampTypeBadge.vue'
import { PageHeader } from '@/Components/ui-custom/page-header'
import { TimeWheel } from '@/Components/ui-custom/time-wheel'
import { Button } from '@/Components/ui/button'
import { Absence, Timestamp } from '@/types'
import { Head, Link, router } from '@inertiajs/vue3'
import moment from 'moment/min/moment-with-locales'

const props = defineProps<{
    date: string
    dayWorkTime: number
    dayPlan?: number
    timestamps: Timestamp[]
    absences: Absence[]
    dayBreakTime: number
    dayNoWorkTime: number
    isHoliday: boolean
    hasWorkSchedules: boolean
}>()

const calcDuration = (startTimestamp: string, endTimestamp?: string) =>
    Math.floor(moment(startTimestamp).diff(endTimestamp).valueOf() / 1000 / 60)

const startOfDay = props.date + ' 00:00:00'
const isFuture = moment().isBefore(moment(props.date, 'YYYY-MM-DD'), 'day')

const reload = () => {
    router.flushAll()
    router.reload({
        only: ['timestamps'],
        showProgress: false
    })
}

if (window.Native) {
    window.Native.on('App\\Events\\TimerStarted', reload)
    window.Native.on('App\\Events\\TimerStopped', reload)
}
</script>

<template>
    <Head title="Day Overview" />
    <PageHeader :title="$t('app.daily overview')">
        <div class="flex flex-1 items-center justify-center text-sm">
            <TimeWheel :date="props.date" route="overview.day.show" type="day" />
        </div>
        <Button
            :as="Link"
            :href="route('overview.day.show', { date: moment().format('YYYY-MM-DD') })"
            class="z-20"
            prefetch
            size="sm"
            variant="outline"
        >
            {{ $t('app.today') }}
        </Button>
    </PageHeader>
    <div class="flex grow flex-col overflow-hidden">
        <Timeline
            :date="props.date"
            :overtime="props.hasWorkSchedules ? Math.max(props.dayWorkTime - (props.dayPlan ?? 0) * 60 * 60, 0) : 0"
            :timestamps="props.timestamps"
            :work-time="props.dayWorkTime"
            class="mb-6 shrink-0"
        />
        <div class="mb-6 flex gap-2">
            <TimestampTypeBadge type="holiday" v-if="props.isHoliday" />
            <TimestampTypeBadge type="vacation" v-if="props.absences.length && props.absences[0].type === 'vacation'" />
            <TimestampTypeBadge type="sick" v-if="props.absences.length && props.absences[0].type === 'sick'" />
            <TimestampTypeBadge
                :duration="props.dayWorkTime"
                type="work"
                v-if="(!props.absences.length && !props.isHoliday) || (!props.hasWorkSchedules && props.dayWorkTime)"
            />
            <TimestampTypeBadge :duration="props.dayBreakTime" type="break" />
            <TimestampTypeBadge :duration="props.dayNoWorkTime" type="noWork" />
            <TimestampTypeBadge
                v-if="props.hasWorkSchedules"
                :duration="Math.max(props.dayWorkTime - (props.dayPlan ?? 0) * 60 * 60, 0)"
                type="overtime"
            />
            <TimestampTypeBadge v-if="props.hasWorkSchedules" :duration="(props.dayPlan ?? 0) * 60 * 60" type="plan" />
        </div>
        <div class="grow space-y-1 overflow-y-auto" scroll-region v-if="!isFuture">
            <TimestampListPlaceholderItem
                :start-of-day="startOfDay"
                v-if="props.timestamps.length === 0 || props.timestamps[0].started_at.date !== startOfDay"
            />
            <template :key="timestamp.id" v-for="(timestamp, index) in props.timestamps">
                <TimestampListPlaceholderItem
                    :duration="calcDuration(timestamp.started_at.date, props.timestamps[index - 1].ended_at?.date)"
                    :start-of-day="startOfDay"
                    :timestamp-after="timestamp"
                    :timestamp-before="props.timestamps[index - 1]"
                    v-if="
                        index > 0 && timestamp.started_at.formatted !== props.timestamps[index - 1].ended_at?.formatted
                    "
                />
                <TimestampListItem
                    :timestamp="timestamp"
                    :timestamp-before="index > 0 ? props.timestamps[index - 1] : undefined"
                />
            </template>
            <TimestampListPlaceholderItem
                :timestamp-before="props.timestamps[props.timestamps.length - 1]"
                v-if="
                    props.timestamps.length > 0 &&
                    props.timestamps[props.timestamps.length - 1].ended_at &&
                    moment(props.timestamps[props.timestamps.length - 1].ended_at?.date).format('HH:mm') !== '23:59'
                "
            />
        </div>
    </div>
    <ConfirmationDialog />
</template>
