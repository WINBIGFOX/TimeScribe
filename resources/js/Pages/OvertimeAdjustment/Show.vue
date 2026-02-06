<script lang="ts" setup>
import Create from '@/Pages/OvertimeAdjustment/Create.vue'
import { Head, Link } from '@inertiajs/vue3'
import moment from 'moment/min/moment-with-locales'

import SheetDialog from '@/Components/dialogs/SheetDialog.vue'
import OvertimeAdjustmentListItem from '@/Components/OvertimeAdjustmentListItem.vue'
import { EmptyState } from '@/Components/ui-custom/empty-state'
import BasicLayout from '@/Layouts/BasicLayout.vue'
import { secToFormat } from '@/lib/utils'
import Edit from '@/Pages/OvertimeAdjustment/Edit.vue'
import { OvertimeAdjustment, WeekBalance, WeekdayObject } from '@/types'
import {
    ArrowRight,
    ClipboardClock,
    Clock,
    ClockArrowDown,
    ClockArrowUp,
    Diff,
    Dot,
    Equal,
    Plus
} from 'lucide-vue-next'
import { computed, ref } from 'vue'

defineOptions({
    layout: BasicLayout
})

const props = defineProps<{
    date: string
    weekBalances: WeekBalance[]
    weekdays: Record<string, WeekdayObject>
    week: number
    year: number
    balance: number
    overtimeAdjustments: OvertimeAdjustment[]
    allOvertimeAdjustments: OvertimeAdjustment[]
}>()

const weekYearString = computed(() => props.year + '-' + props.week)
const currentWeekBalance = computed(() =>
    props.weekBalances.find((weekBalance) => weekBalance.year + '-' + weekBalance.week_number === weekYearString.value)
)

type WeekBalanceGroup = {
    year: number
    weekBalances: WeekBalance[]
}

const groupWeekBalancesByYear = computed<WeekBalanceGroup[]>(() => {
    const grouped = new Map<number, WeekBalance[]>()

    for (const weekBalance of props.weekBalances) {
        if (!grouped.has(weekBalance.year)) {
            grouped.set(weekBalance.year, [weekBalance])
            continue
        }

        grouped.get(weekBalance.year)?.push(weekBalance)
    }

    return [...grouped.entries()].map(([year, balances]) => ({
        year,
        weekBalances: balances
    }))
})

const hasDateAdjustment = (date: string) => {
    const adjustment = props.overtimeAdjustments.filter(
        (overtimeAdjustment) => overtimeAdjustment.effective_date.date === date
    )

    if (adjustment.length) {
        return adjustment[0].type === 'absolute'
            ? 'absolute'
            : 'relative-' + (adjustment[0].seconds < 0 ? 'negative' : 'positive')
    } else {
        return undefined
    }
}

const hasWeekAdjustment = (yearWeekString: string) => {
    const adjustment = props.allOvertimeAdjustments
        .filter((overtimeAdjustment) => overtimeAdjustment.year + '-' + overtimeAdjustment.week === yearWeekString)
        .sort((a) => (a.type === 'relative' ? 1 : -1))

    if (adjustment.length) {
        return adjustment[0].type === 'absolute'
            ? 'absolute'
            : 'relative-' + (adjustment[0].seconds < 0 ? 'negative' : 'positive')
    } else {
        return undefined
    }
}
const createModal = ref(false)
const createModalDate = ref(props.date)
const openCreateModal = (date: string) => {
    createModalDate.value = date
    createModal.value = true
}

const editModal = ref(false)
const editModalOvertimeAdjustment = ref<OvertimeAdjustment>()

const openEditModal = (overtimeAdjustment: OvertimeAdjustment) => {
    editModalOvertimeAdjustment.value = overtimeAdjustment
    editModal.value = true
}

const closeEditModal = () => {
    editModal.value = false
    setTimeout(() => (editModalOvertimeAdjustment.value = undefined), 300)
}
</script>

<template>
    <Head title="Overtime-Adjustment show" />
    <SheetDialog size="lg" :title="$t('app.overtime adjustment overview')" :close="$t('app.close')">
        <div v-if="props.weekBalances.length" class="flex grow gap-4 overflow-hidden">
            <div class="max-w-80 shrink-0 overflow-y-auto">
                <template v-for="yearGroup in groupWeekBalancesByYear" :key="yearGroup.year">
                    <div
                        class="bg-background sticky z-10"
                        :class="{
                            'top-0 bottom-14': yearGroup.year >= Number(props.year),
                            'top-22 pt-4': yearGroup.year < Number(props.year)
                        }"
                    >
                        <div class="bg-muted flex h-8 items-center rounded-t-lg px-4">
                            {{ yearGroup.year }}
                        </div>
                    </div>
                    <Link
                        as="div"
                        preserve-scroll
                        preserve-state
                        :href="route('overtime-adjustment.show', { date: weekBalance.start_date.date })"
                        v-for="weekBalance in yearGroup.weekBalances"
                        :key="weekBalance.id"
                        :data-adjustment-type="hasWeekAdjustment(weekBalance.year + '-' + weekBalance.week_number)"
                        class="bg-background border-border/50 border-x border-b data-[adjustment-type]:[background-image:repeating-linear-gradient(45deg,transparent_0_.75rem,color-mix(in_srgb,var(--linear-color)_15%,transparent)_.75rem_1.5rem)] data-[adjustment-type=absolute]:[--linear-color:var(--color-lime-400)] data-[adjustment-type=relative-negative]:[--linear-color:var(--color-green-500)] data-[adjustment-type=relative-positive]:[--linear-color:var(--color-amber-400)]"
                        :class="{
                            'sticky top-8 bottom-2 z-10':
                                weekBalance.year + '-' + weekBalance.week_number === weekYearString
                        }"
                    >
                        <div
                            class="flex h-12 items-center gap-4 px-4 py-2"
                            :class="{
                                'bg-primary/10 ring-primary/60 rounded ring-1':
                                    weekBalance.year + '-' + weekBalance.week_number === weekYearString
                            }"
                        >
                            <div class="flex flex-col items-center">
                                <span class="text-lg leading-none font-semibold">
                                    {{ weekBalance.week_number }}
                                </span>
                                <span class="text-muted-foreground text-xs leading-none">{{ $t('app.week') }}</span>
                            </div>
                            <div class="grid flex-1 grid-cols-2 gap-4">
                                <div class="flex flex-col items-end">
                                    <div
                                        class="flex items-center gap-2 text-sm text-nowrap tabular-nums"
                                        :class="{
                                            'text-amber-400': weekBalance.balance > 0,
                                            'text-green-500': weekBalance.balance < 0,
                                            'text-muted-foreground': weekBalance.balance === 0
                                        }"
                                    >
                                        {{ weekBalance.balance > 0 ? '+' : ''
                                        }}{{ secToFormat(weekBalance.balance, false, true, true) }}
                                        {{ $t('app.h') }}
                                        <ClockArrowUp class="size-4" v-if="weekBalance.balance > 0" />
                                        <Clock class="size-4" v-if="weekBalance.balance === 0" />
                                        <ClockArrowDown class="size-4" v-if="weekBalance.balance < 0" />
                                    </div>
                                    <span class="text-muted-foreground line-clamp-1 text-xs leading-none break-all">{{
                                        $t('app.overtime')
                                    }}</span>
                                </div>
                                <div class="flex flex-col items-end" v-if="weekBalance.end_balance">
                                    <div
                                        class="flex items-center justify-end gap-2 text-right text-sm text-lime-500 tabular-nums"
                                    >
                                        {{ secToFormat(weekBalance.end_balance, false, true, true) }}
                                        {{ $t('app.h') }}
                                        <Diff class="size-4" />
                                    </div>
                                    <span class="text-muted-foreground line-clamp-1 text-xs leading-none break-all">
                                        {{ $t('app.time balance') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </Link>
                    <div
                        class="bg-background"
                        :class="{
                            'sticky top-20 bottom-0': yearGroup.year === Number(props.year),
                            'sticky bottom-0': yearGroup.year !== Number(props.year),
                            'pb-4 not-last:sticky not-last:bottom-22': yearGroup.year > Number(props.year)
                        }"
                    >
                        <div class="bg-muted h-2 rounded-b-lg"></div>
                    </div>
                </template>
            </div>
            <div class="flex flex-1 flex-col gap-4 overflow-hidden border-l pl-4">
                <div class="text-muted-foreground z-10 -mb-2 flex h-4 items-center gap-1.5 text-sm">
                    <div class="shrink-0">{{ $t('app.week') }} {{ props.week }}</div>
                    <Dot class="size-4" />
                    <div class="truncate">
                        {{ moment(props.date).startOf('week').format('LL') }}
                    </div>
                    <ArrowRight class="size-4" />
                    <div class="truncate">
                        {{ moment(props.date).endOf('week').format('LL') }}
                    </div>
                </div>
                <div
                    class="bg-sidebar border-muted flex items-center justify-around rounded-lg border px-1 py-2 text-sm"
                    v-if="currentWeekBalance"
                >
                    <div class="flex min-w-0 flex-col items-center gap-1">
                        <span class="w-full truncate text-center font-medium">{{ $t('app.week start') }}</span>
                        <div class="bg-background border-muted rounded border px-2 py-1">
                            <div class="flex items-center gap-2 text-sm text-lime-500 tabular-nums">
                                {{ secToFormat(currentWeekBalance.start_balance, false, true, true) }}
                                {{ $t('app.h') }}
                                <Diff class="size-4" />
                            </div>
                        </div>
                    </div>
                    <Plus class="size-4" />
                    <div class="flex min-w-0 flex-col items-center gap-1">
                        <span class="w-full truncate text-center font-medium">{{ $t('app.overtime') }}</span>
                        <div class="bg-background border-muted rounded border px-2 py-1">
                            <div
                                class="flex items-center gap-2 text-sm tabular-nums"
                                :class="{
                                    'text-amber-400': currentWeekBalance.balance > 0,
                                    'text-green-500': currentWeekBalance.balance < 0,
                                    'text-muted-foreground': currentWeekBalance.balance === 0
                                }"
                            >
                                {{ secToFormat(currentWeekBalance.balance, false, true, true) }}
                                {{ $t('app.h') }}
                                <ClockArrowUp class="size-4" v-if="currentWeekBalance.balance > 0" />
                                <Clock class="size-4" v-if="currentWeekBalance.balance === 0" />
                                <ClockArrowDown class="size-4" v-if="currentWeekBalance.balance < 0" />
                            </div>
                        </div>
                    </div>
                    <Equal class="size-4" />
                    <div class="flex min-w-0 flex-col items-center gap-1">
                        <span class="w-full truncate overflow-hidden text-center font-medium">
                            {{ $t('app.week end') }}
                        </span>
                        <div class="bg-background border-muted rounded border px-2 py-1">
                            <div class="flex items-center gap-2 text-sm text-lime-500 tabular-nums">
                                {{ secToFormat(currentWeekBalance.end_balance, false, true, true) }}
                                {{ $t('app.h') }}
                                <Diff class="size-4" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-2">
                    <div
                        v-for="weekday in props.weekdays"
                        :key="weekday.date.formatted"
                        :data-adjustment-type="hasDateAdjustment(weekday.date.date)"
                        :class="{
                            'ring-primary ring-2 ring-inset': weekday.date.date === moment().format('YYYY-MM-DD')
                        }"
                        class="group bg-sidebar border-muted text-muted-foreground relative flex flex-1 flex-col overflow-clip rounded-lg border text-center data-[adjustment-type]:[background-image:repeating-linear-gradient(45deg,transparent_0_.75rem,color-mix(in_srgb,var(--linear-color)_15%,transparent)_.75rem_1.5rem)] data-[adjustment-type=absolute]:[--linear-color:var(--color-lime-400)] data-[adjustment-type=relative-negative]:[--linear-color:var(--color-green-500)] data-[adjustment-type=relative-positive]:[--linear-color:var(--color-amber-400)]"
                    >
                        <div
                            @click="openCreateModal(weekday.date.date)"
                            class="bg-background/50 text-foreground absolute inset-0 flex items-center justify-center opacity-0 backdrop-blur-xs transition-opacity duration-300 group-hover:opacity-100 group-data-[adjustment-type]:hidden"
                        >
                            <ClipboardClock class="size-6" />
                        </div>
                        <div class="flex h-14 flex-col items-center justify-center">
                            <span class="text-foreground leading-none font-medium">
                                {{ moment(weekday.date.date).format('dd') }}
                            </span>
                            <span class="mt-0.5 text-xs leading-none">
                                {{ moment(weekday.date.date).format('Do') }}
                            </span>
                        </div>
                        <div
                            class="border-t py-1 text-xs"
                            :class="{
                                'text-amber-400': weekday.workTime - (weekday.plan ?? 0) * 3600 > 0,
                                'text-green-500': weekday.workTime - (weekday.plan ?? 0) * 3600 < 0,
                                'text-muted-foreground': weekday.workTime - (weekday.plan ?? 0) * 3600 === 0
                            }"
                        >
                            {{ secToFormat(weekday.workTime - (weekday.plan ?? 0) * 3600, false, true, true) }}
                        </div>
                    </div>
                </div>

                <EmptyState
                    v-if="!props.overtimeAdjustments.length"
                    :icon="ClipboardClock"
                    class="py-2 text-sm"
                    :action-click="() => openCreateModal(props.date)"
                    :action-label="$t('app.add an adjustment')"
                    :title="$t('app.add an adjustment')"
                    :description="$t('app.you can add an adjustment to change your overtime balance.')"
                />
                <div class="flex grow flex-col gap-2 overflow-y-auto" v-else>
                    <div class="text-foreground/80 text-sm font-medium">{{ $t('app.adjustments') }}</div>
                    <div class="flex flex-col gap-2">
                        <OvertimeAdjustmentListItem
                            v-for="overtimeAdjustment in props.overtimeAdjustments"
                            :key="overtimeAdjustment.id"
                            :overtime-adjustment="overtimeAdjustment"
                            @edit="openEditModal"
                        />
                    </div>
                </div>

                <Create
                    :date="createModalDate"
                    v-model:open="createModal"
                    :key="createModalDate"
                    @close="createModal = false"
                />
                <Edit
                    v-if="editModalOvertimeAdjustment"
                    :overtime-adjustment="editModalOvertimeAdjustment"
                    v-model:open="editModal"
                    :key="editModalOvertimeAdjustment.id"
                    @close="closeEditModal"
                />
            </div>
        </div>
        <EmptyState
            :icon="ClipboardClock"
            :title="$t('app.no time balance available')"
            :description="$t('app.no working time recorded yet')"
            v-else
        />
    </SheetDialog>
</template>
