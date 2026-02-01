<script lang="ts" setup>
import VacationListItem from '@/Components/VacationListItem.vue'
import ConfirmationDialog from '@/Components/dialogs/ConfirmationDialog.vue'
import { EmptyState } from '@/Components/ui-custom/empty-state'
import { PageHeader } from '@/Components/ui-custom/page-header'
import { TimeWheel } from '@/Components/ui-custom/time-wheel'
import { Badge } from '@/Components/ui/badge'
import { Button } from '@/Components/ui/button'
import type { VacationEntitlement, VacationEntry, VacationSummary } from '@/types'
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3'
import { useDebounceFn } from '@vueuse/core'
import { CalendarPlus, Palmtree, Pen } from 'lucide-vue-next'
import moment from 'moment/min/moment-with-locales'
import { computed, ref, watch } from 'vue'

const props = defineProps<{
    date: string
    summary: VacationSummary
    entitlement: VacationEntitlement
    entries: VacationEntry[]
    availableYears: number[]
    hasWorkSchedule: boolean
}>()

const page = usePage()
const locale = computed(() => page.props.js_locale)
const summary = computed(() => props.summary)

const form = useForm({
    year: props.entitlement.year,
    days: props.entitlement.days ?? null,
    carryover: props.entitlement.carryover ?? null
})

const pendingSync = ref(false)

const formatNumber = (value: number) => {
    return value.toLocaleString(locale.value, {
        maximumFractionDigits: 2,
        minimumFractionDigits: Number.isInteger(value) ? 0 : 2
    })
}

const remainingIsNegative = computed(() => props.summary.remaining < 0)

const triggerSave = useDebounceFn(() => {
    if (!pendingSync.value) {
        pendingSync.value = true
        return
    }

    router.flushAll()
    form.post(route('absence.vacation-entitlement.update'), {
        preserveScroll: true,
        preserveState: true
    })
}, 600)

watch(
    () => [form.days, form.carryover],
    () => {
        triggerSave()
    }
)

watch(
    () => props.entitlement,
    (entitlement) => {
        pendingSync.value = false
        form.defaults({
            year: entitlement.year,
            days: entitlement.days ?? null,
            carryover: entitlement.carryover ?? null
        })
        form.year = entitlement.year
        form.days = entitlement.days ?? null
        form.carryover = entitlement.carryover ?? null
    },
    { deep: true }
)

const entries = computed(() => props.entries)
const hasEntries = computed(() => entries.value.length > 0)
</script>

<template>
    <Head title="Vacation Overview" />

    <PageHeader :title="$t('app.vacation overview')">
        <div class="flex flex-1 items-center justify-center text-sm">
            <TimeWheel :date="props.date" route="absence.vacation.index" type="year" />
        </div>
        <div class="flex items-center gap-2">
            <Button
                :as="Link"
                :href="route('absence.vacation.index', { date: moment().format('YYYY-MM-DD') })"
                prefetch
                size="sm"
                variant="outline"
            >
                {{ $t('app.today') }}
            </Button>
            <Button :as="Link" :href="route('absence.show', { date: props.date })" prefetch size="sm" variant="outline">
                {{ $t('app.calendar view') }}
            </Button>
        </div>
    </PageHeader>

    <div class="flex grow flex-col gap-4">
        <div class="flex flex-1 items-center justify-center" v-if="!props.hasWorkSchedule">
            <EmptyState
                :action-href="route('work-schedule.create')"
                :action-label="$t('app.create new work schedule')"
                :description="
                    $t('app.vacation needs a work schedule to calculate days. create one now to enable tracking.')
                "
                :title="$t('app.work schedule required')"
                :icon="CalendarPlus"
                class="h-full"
            />
        </div>
        <template v-else>
            <div class="grid grid-cols-4 gap-4">
                <div class="border-border bg-muted/40 group relative rounded-lg border p-4">
                    <div class="text-muted-foreground text-xs tracking-wide uppercase">
                        {{ $t('app.vacation allowance') }}
                    </div>
                    <div class="text-foreground mt-2 text-3xl font-semibold">
                        {{ formatNumber(summary.totalEntitlement) }}
                    </div>
                    <div class="text-muted-foreground mt-1 text-xs">
                        {{ $t('app.days') }}
                    </div>
                    <Link
                        :href="route('absence.vacation-entitlement.edit', { date: props.date })"
                        class="bg-background/50 absolute inset-0 flex flex-col items-center justify-center gap-2 rounded-lg p-4 opacity-0 backdrop-blur-xs transition-all duration-300 group-hover:opacity-100"
                    >
                        <Pen />
                        <span class="text-center text-xs">{{
                            $t('app.edit :item', { item: $t('app.vacation allowance') + ' ' + props.entitlement.year })
                        }}</span>
                    </Link>
                </div>
                <div class="border-border bg-muted/40 rounded-lg border p-4">
                    <div class="text-muted-foreground text-xs tracking-wide uppercase">
                        {{ $t('app.taken') }}
                    </div>
                    <div class="text-foreground mt-2 text-3xl font-semibold">
                        {{ formatNumber(summary.taken) }}
                    </div>
                    <div class="text-muted-foreground mt-1 text-xs">
                        {{ $t('app.days') }}
                    </div>
                </div>
                <div class="border-border bg-muted/40 rounded-lg border p-4">
                    <div class="text-muted-foreground text-xs tracking-wide uppercase">
                        {{ $t('app.planned') }}
                    </div>
                    <div class="text-foreground mt-2 text-3xl font-semibold">
                        {{ formatNumber(summary.planned) }}
                    </div>
                    <div class="text-muted-foreground mt-1 text-xs">
                        {{ $t('app.days') }}
                    </div>
                </div>
                <div
                    :class="{
                        'border-rose-400 text-rose-500': remainingIsNegative,
                        'border-border': !remainingIsNegative
                    }"
                    class="bg-muted/40 rounded-lg border p-4"
                >
                    <div class="text-muted-foreground text-xs tracking-wide uppercase">
                        {{ $t('app.remaining') }}
                    </div>
                    <div
                        :class="{
                            'text-rose-500': remainingIsNegative
                        }"
                        class="mt-2 text-3xl font-semibold"
                    >
                        {{ formatNumber(summary.remaining) }}
                    </div>
                    <div class="text-muted-foreground mt-1 text-xs">
                        {{ $t('app.days') }}
                    </div>
                </div>
            </div>

            <hr />
            <div class="flex grow flex-col">
                <div class="mb-4 flex items-center justify-between">
                    <div class="text-foreground/80 text-sm font-medium">
                        {{ $t('app.vacation entries') }}
                    </div>
                    <Badge v-if="!hasEntries" variant="outline">
                        {{ $t('app.empty') }}
                    </Badge>
                </div>
                <div v-if="hasEntries" class="flex flex-col gap-1">
                    <VacationListItem :vacation-entry="entry" :key="entry.id" v-for="entry in entries">
                        <span
                            :class="[
                                'absolute inset-y-0 left-0 w-1 bg-gradient-to-b',
                                entry.status === 'taken'
                                    ? 'from-emerald-400 via-emerald-500 to-emerald-600'
                                    : 'from-sky-400 via-sky-500 to-sky-600'
                            ]"
                        />
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between sm:gap-6">
                            <div class="flex items-center gap-4">
                                <div
                                    class="border-border/60 bg-background/80 flex size-16 shrink-0 flex-col items-center justify-center rounded-lg border text-center shadow-sm"
                                >
                                    <span class="text-2xl leading-tight font-semibold tabular-nums">
                                        {{ entry.date.formatted.split('.')[0] }}
                                    </span>
                                    <span class="text-muted-foreground text-xs leading-none uppercase">
                                        {{ entry.date.formatted.split('.')[1] }}.
                                    </span>
                                    <span class="text-muted-foreground text-[10px] leading-none uppercase">
                                        {{ entry.date.formatted.split('.')[2] }}
                                    </span>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <span class="text-muted-foreground text-xs uppercase">
                                        {{ $t('app.date') }}
                                    </span>
                                    <span class="text-lg leading-none font-semibold tabular-nums">
                                        {{ entry.date.formatted }}
                                    </span>
                                    <Badge
                                        :variant="entry.status === 'taken' ? 'default' : 'outline'"
                                        :class="{
                                            'text-background! bg-emerald-500!': entry.status === 'taken'
                                        }"
                                        class="w-fit"
                                    >
                                        {{ entry.status === 'taken' ? $t('app.taken') : $t('app.planned') }}
                                    </Badge>
                                </div>
                            </div>
                            <div class="flex flex-1 flex-wrap items-center justify-end gap-3 sm:gap-4">
                                <div
                                    class="border-border/60 bg-background/70 min-w-32 rounded-lg border px-4 py-3 text-right shadow-sm"
                                >
                                    <span class="text-muted-foreground text-xs uppercase">
                                        {{ $t('app.day equivalent') }}
                                    </span>
                                    <div class="text-lg leading-none font-semibold">
                                        {{ formatNumber(entry.day_equivalent) }}
                                    </div>
                                </div>
                                <div
                                    class="border-border/60 bg-background/70 min-w-32 rounded-lg border px-4 py-3 text-right shadow-sm"
                                >
                                    <span class="text-muted-foreground text-xs uppercase">
                                        {{ $t('app.hours') }}
                                    </span>
                                    <div class="text-lg leading-none font-semibold">
                                        {{ formatNumber(entry.hours) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </VacationListItem>
                </div>
                <div class="flex flex-1 items-center justify-center" v-else>
                    <EmptyState
                        :action-href="route('absence.show', { date: props.date })"
                        :action-label="$t('app.create vacation entry')"
                        :description="$t('app.no vacation days recorded for this year yet.')"
                        :title="$t('app.vacation entries')"
                        :icon="Palmtree"
                        class="h-full"
                    />
                </div>
            </div>
        </template>
    </div>
    <ConfirmationDialog />
</template>
