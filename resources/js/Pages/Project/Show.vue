<script lang="ts" setup>
import { Head } from '@inertiajs/vue3'
import moment from 'moment/min/moment-with-locales'
import { computed } from 'vue'

import SheetDialog from '@/Components/dialogs/SheetDialog.vue'
import ProjectTimestampListItem from '@/Components/ProjectTimestampListItem.vue'
import { EmptyState } from '@/Components/ui-custom/empty-state'
import BasicLayout from '@/Layouts/BasicLayout.vue'
import { secToFormat } from '@/lib/utils'
import { Enum, Project, Timestamp } from '@/types'
import { BriefcaseBusiness, CircleCheckBig, CircleEqual, CircleSlash, Timer } from 'lucide-vue-next'

defineOptions({
    layout: BasicLayout
})

const props = defineProps<{
    project: Project
    currencies: Enum
}>()

type TimestampGroup = {
    key: string
    label: string
    duration: number
    billable: {
        paid: number
        open: number
        total: number
    }
    timestamps: Timestamp[]
}

const timestampGroups = computed<TimestampGroup[]>(() => {
    const groups = new Map<string, TimestampGroup>()
    const timestamps = props.project.timestamps ?? []

    timestamps.forEach((timestamp) => {
        const key = moment(timestamp.started_at.date).format('YYYY-MM')

        let group = groups.get(key)

        if (!group) {
            group = {
                key,
                duration: 0,
                billable: {
                    paid: 0,
                    open: 0,
                    total: 0
                },
                label: moment(timestamp.started_at.date).format('MMMM YYYY'),
                timestamps: []
            }
            groups.set(key, group)
        }

        if (timestamp.paid) {
            group.billable.paid += timestamp.billable_amount ?? 0
        } else {
            group.billable.open += timestamp.billable_amount ?? 0
        }
        group.duration += timestamp.duration
        group.billable.total += timestamp.billable_amount ?? 0
        group.timestamps.push(timestamp)
    })

    return Array.from(groups.values())
})

const calcAmount = (paid: boolean) => {
    if (!props.project.hourly_rate) {
        return undefined
    }
    const duration =
        props.project.timestamps
            ?.filter((t) => t.paid === paid)
            .reduce((partialSum, a) => partialSum + a.duration, 0) ?? 0
    return ((duration / 60) * props.project.hourly_rate) / 60
}

const amountPaid = computed(() => calcAmount(true))
const amountOpen = computed(() => calcAmount(false))
</script>

<template>
    <Head title="Project show" />
    <SheetDialog size="lg" :close="$t('app.close')" scrollable>
        <template #title>
            <div class="flex items-end justify-between gap-4 pr-10">
                <div
                    :style="'--project-color: ' + (props.project.color ?? '#000000')"
                    class="inline-flex h-9 items-center justify-center gap-2 rounded-md border-l-6 border-l-(--project-color) bg-(--project-color)/10 p-4 text-sm font-medium dark:bg-(--project-color)/20"
                >
                    <div class="flex shrink-0 items-center text-xl" v-if="props.project.icon">
                        {{ props.project.icon }}
                    </div>
                    <div class="line-clamp-1">
                        {{ props.project.name }}
                    </div>
                </div>
                <div class="flex shrink-0 gap-2" v-if="props.project.hourly_rate && props.project.currency">
                    <div>
                        <span class="text-xs">{{ $t('app.open') }}</span>
                        <div class="text-background flex items-center gap-2 rounded bg-stone-500 px-2 py-1">
                            <CircleSlash class="size-5" />
                            <span class="text-sm tabular-nums">
                                {{
                                    amountOpen?.toLocaleString($page.props.js_locale, {
                                        style: 'currency',
                                        currency: props.project.currency,
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    })
                                }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <span class="text-xs">{{ $t('app.paid') }}</span>
                        <div class="text-background flex items-center gap-2 rounded bg-emerald-500 px-2 py-1">
                            <CircleCheckBig class="size-5" />
                            <span class="text-sm tabular-nums">
                                {{
                                    amountPaid?.toLocaleString($page.props.js_locale, {
                                        style: 'currency',
                                        currency: props.project.currency,
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    })
                                }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <span class="text-xs">{{ $t('app.total') }}</span>
                        <div class="text-background flex items-center gap-2 rounded bg-blue-500 px-2 py-1">
                            <CircleEqual class="size-5" />
                            <span class="text-sm tabular-nums">
                                {{
                                    props.project.billable_amount?.toLocaleString($page.props.js_locale, {
                                        style: 'currency',
                                        currency: props.project.currency,
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    })
                                }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <template v-for="group in timestampGroups" :key="group.key">
            <div class="bg-background sticky top-0 z-10 flex items-end justify-between pb-2">
                <span class="text-lg">
                    {{ group.label }}
                </span>
                <div class="flex gap-4">
                    <template v-if="props.project.hourly_rate && props.project.currency">
                        <div class="flex items-center gap-2 text-stone-500">
                            <CircleSlash class="size-4" />
                            <span class="text-xs tabular-nums">
                                {{
                                    group.billable.open.toLocaleString($page.props.js_locale, {
                                        style: 'currency',
                                        currency: props.project.currency,
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    })
                                }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 text-emerald-500">
                            <CircleCheckBig class="size-4" />
                            <span class="text-xs tabular-nums">
                                {{
                                    group.billable.paid.toLocaleString($page.props.js_locale, {
                                        style: 'currency',
                                        currency: props.project.currency,
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    })
                                }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 text-blue-500">
                            <CircleEqual class="size-4" />
                            <span class="text-xs tabular-nums">
                                {{
                                    group.billable.total.toLocaleString($page.props.js_locale, {
                                        style: 'currency',
                                        currency: props.project.currency,
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    })
                                }}
                            </span>
                        </div>
                    </template>
                    <div class="text-muted-foreground flex items-center gap-2">
                        <Timer class="size-4" />
                        <span class="text-xs tabular-nums">
                            {{
                                group.duration > 59
                                    ? secToFormat(group.duration, false, true, true)
                                    : group.duration.toFixed(0)
                            }}
                            {{ group.duration > 59 ? $t('app.h') : $t('app.s') }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="space-y-1 *:last:mb-10 last:*:last:mb-0">
                <ProjectTimestampListItem
                    v-for="timestamp in group.timestamps"
                    :key="timestamp.id"
                    :project="props.project"
                    :timestamp="timestamp"
                />
            </div>
        </template>
        <EmptyState
            v-if="timestampGroups.length === 0"
            :icon="BriefcaseBusiness"
            :title="$t('app.no hours recorded yet')"
            :description="$t('app.no work hours recorded for this project yet')"
        />
    </SheetDialog>
</template>
