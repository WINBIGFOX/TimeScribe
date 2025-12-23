<script lang="ts" setup>
import { secToFormat } from '@/lib/utils'
import { Absence } from '@/types'
import { BriefcaseBusiness, ClockArrowDown, ClockArrowUp, Coffee, Cross, Drama, TreePalm } from 'lucide-vue-next'
import { computed } from 'vue'

const props = withDefaults(
    defineProps<{
        plan?: number
        fallbackPlan?: number
        workTime?: number
        breakTime?: number
        activeWork?: boolean
        absences: Absence[]
        isHoliday?: boolean
        hasWorkSchedule?: boolean
    }>(),
    {
        plan: 0,
        fallbackPlan: 0,
        progress: 0,
        workTime: 0,
        breakTime: 0,
        activeWork: false,
        isHoliday: false,
        hasWorkSchedule: false
    }
)

const percentageWorkTime = computed(() => {
    if (!props.plan) {
        return 0
    }
    return Math.min((props.workTime / (props.plan * 60 * 60)) * 100, 100)
})

const timePlanDifference = computed(() => {
    return props.workTime - props.plan * 60 * 60
})

const percentageOverTime = computed(() => {
    return Math.max(Math.min((timePlanDifference.value / (props.fallbackPlan * 60 * 60)) * 100, 100), 0)
})
</script>

<template>
    <div
        class="flex grow flex-col"
        v-if="props.plan || props.workTime || (!props.hasWorkSchedule && (props.absences.length || props.isHoliday))"
    >
        <div class="text-foreground/80 mb-2 text-center text-sm" v-if="props.hasWorkSchedule">
            {{ props.plan.toLocaleString($page.props.js_locale) }}
            {{ $t('app.h') }}
        </div>
        <div class="bg-muted relative grow overflow-hidden rounded-t-lg">
            <div
                :class="{
                    'bg-primary': !props.absences.length && !props.isHoliday,
                    'bg-rose-400': props.absences[0]?.type === 'sick',
                    'bg-emerald-500': props.absences[0]?.type === 'vacation',
                    'bg-purple-400': props.isHoliday
                }"
                :style="{
                    height: `${percentageWorkTime}%`
                }"
                class="absolute inset-x-0 bottom-0 flex flex-col transition-all duration-300"
            >
                <div
                    class="animate-progress grow bg-gradient-to-t from-transparent via-transparent to-white/40"
                    v-if="props.activeWork"
                />
            </div>
            <div
                :style="{
                    height: `${percentageOverTime}%`
                }"
                class="absolute inset-x-0 bottom-0 flex flex-col bg-amber-400 transition-all duration-300 starting:h-0"
                v-if="percentageOverTime && props.hasWorkSchedule"
            >
                <div
                    class="animate-progress grow bg-gradient-to-t from-transparent via-transparent to-white/40"
                    v-if="props.activeWork"
                />
            </div>
        </div>
        <div class="mt-2 h-14 space-y-1">
            <div v-if="props.absences.length">
                <div
                    class="flex items-center justify-center gap-1 text-xs text-emerald-500"
                    v-if="props.absences[0].type === 'vacation'"
                >
                    <TreePalm class="size-4 shrink-0" />
                    {{ $t('app.leave') }}
                </div>

                <div
                    class="flex items-center justify-center gap-1 text-xs text-rose-400"
                    v-if="props.absences[0].type === 'sick'"
                >
                    <Cross class="size-4 shrink-0" />
                    {{ $t('app.sick') }}
                </div>
            </div>
            <div v-else-if="props.isHoliday" class="flex items-center justify-center text-purple-400">
                <Drama class="size-4 shrink-0" />
            </div>
            <div
                class="text-muted-foreground flex items-center justify-between gap-1 text-xs"
                v-if="props.workTime && ((!props.absences.length && !props.isHoliday) || !props.hasWorkSchedule)"
            >
                <BriefcaseBusiness class="size-4 shrink-0" />
                {{ secToFormat(props.workTime ?? 0, false, true) }}
            </div>
            <div
                :class="{
                    'text-green-500': timePlanDifference < 0,
                    'text-amber-400': timePlanDifference > 0
                }"
                class="flex items-center justify-between gap-1 text-xs"
                v-if="timePlanDifference !== 0 && props.workTime && props.hasWorkSchedule"
            >
                <ClockArrowUp class="size-4 shrink-0" v-if="timePlanDifference > 0" />
                <ClockArrowDown class="size-4 shrink-0" v-if="timePlanDifference < 0" />
                {{ secToFormat(timePlanDifference, false, true, true, true) }}
            </div>
            <div
                class="text-muted-foreground flex items-center justify-between gap-1 text-xs"
                v-if="props.breakTime && !props.absences.length"
            >
                <Coffee class="size-4 shrink-0" />
                {{ secToFormat(props.breakTime ?? 0, false, true) }}
            </div>
        </div>
    </div>
</template>
