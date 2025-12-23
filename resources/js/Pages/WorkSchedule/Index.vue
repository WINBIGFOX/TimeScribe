<script lang="ts" setup>
import { Badge } from '@/Components/ui/badge'
import { Button } from '@/Components/ui/button'
import { EmptyState } from '@/Components/ui-custom/empty-state'
import { weekdayTranslate } from '@/lib/utils'
import { WorkSchedule } from '@/types'
import { Head, Link } from '@inertiajs/vue3'
import { CalendarPlus, Pen } from 'lucide-vue-next'
import moment from 'moment/min/moment-with-locales'
import { PageHeader } from '@/Components/ui-custom/page-header'

const props = defineProps<{
    workSchedules: WorkSchedule[]
}>()

const weekdays = moment.weekdays(true)
const weekdayLabels = moment.weekdaysMin(true)

const weekTotal = (workSchedule: WorkSchedule): number => {
    return (
        workSchedule.monday +
        workSchedule.tuesday +
        workSchedule.wednesday +
        workSchedule.thursday +
        workSchedule.friday +
        workSchedule.saturday +
        workSchedule.sunday
    )
}
</script>

<template>
    <Head title="Work Schedule" />

    <PageHeader :title="$t('app.work schedule')">
        <Button
            :as="Link"
            :href="route('work-schedule.create')"
            prefetch
            preserve-scroll
            preserve-state
            size="sm"
            variant="outline"
        >
            <CalendarPlus />
            {{ $t('app.create new work schedule') }}
        </Button>
    </PageHeader>
    <div class="flex grow flex-col">
        <template v-if="props.workSchedules.length">
            <div class="flex grow flex-col gap-3">
                <div
                    :class="{
                        'bg-muted/40 border-border': !workSchedule.is_current,
                        'border-primary bg-primary/5 dark:bg-primary/10': workSchedule.is_current
                    }"
                    :key="workSchedule.id"
                    class="rounded-lg border p-4"
                    v-for="workSchedule in props.workSchedules"
                >
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex flex-col gap-1">
                            <div class="text-muted-foreground text-xs tracking-wide uppercase">
                                {{ $t('app.valid from') }}
                            </div>
                            <div class="text-foreground text-sm font-medium">
                                {{ moment(workSchedule.valid_from.formatted, 'DD.MM.YYYY').format('L') }}
                            </div>
                        </div>
                        <Badge v-if="workSchedule.is_current" class="border-primary/40 text-primary" variant="outline">
                            {{ $t('app.current work schedule') }}
                        </Badge>
                        <div class="flex items-center gap-3">
                            <div class="text-right">
                                <div class="text-muted-foreground text-xs tracking-wide uppercase">
                                    {{ $t('app.weekly work hours') }}
                                </div>
                                <div class="text-foreground text-sm font-semibold tabular-nums">
                                    {{ weekTotal(workSchedule).toLocaleString($page.props.js_locale) }}
                                    <span class="text-muted-foreground text-xs">{{ $t('app.h') }}</span>
                                </div>
                            </div>
                            <Button
                                :as="Link"
                                :href="route('work-schedule.edit', { work_schedule: workSchedule.id })"
                                class="size-8"
                                prefetch
                                preserve-scroll
                                preserve-state
                                size="icon"
                                variant="outline"
                            >
                                <Pen />
                            </Button>
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-3 text-sm sm:grid-cols-4 lg:grid-cols-7">
                        <div :key="weekday" class="flex flex-col gap-1" v-for="(weekday, index) in weekdays">
                            <div class="text-muted-foreground text-xs">
                                {{ weekdayLabels[index] }}
                            </div>
                            <div class="font-medium tabular-nums">
                                <template v-if="workSchedule[weekdayTranslate(weekday).toLowerCase()] > 0">
                                    {{
                                        workSchedule[weekdayTranslate(weekday).toLowerCase()].toLocaleString(
                                            $page.props.js_locale
                                        )
                                    }}
                                    <span class="text-muted-foreground text-xs">{{ $t('app.h') }}</span>
                                </template>
                                <span v-else>-</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
        <div class="flex flex-1 items-center justify-center" v-else>
            <EmptyState
                :action-href="route('work-schedule.create')"
                :action-label="$t('app.create new work schedule')"
                :icon="CalendarPlus"
                :title="$t('app.no work schedules yet')"
                :description="$t('app.create a work schedule to start tracking hours and vacation correctly.')"
            />
        </div>
    </div>
</template>
