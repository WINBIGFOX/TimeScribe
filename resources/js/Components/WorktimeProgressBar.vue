<script setup lang="ts">
import { secToFormat } from '@/lib/utils';
import { Absence } from '@/types';
import {
    BriefcaseBusiness,
    ClockArrowDown,
    ClockArrowUp,
    Coffee,
    Cross,
    TreePalm,
} from 'lucide-vue-next';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        plan?: number;
        fallbackPlan?: number;
        workTime?: number;
        breakTime?: number;
        activeWork?: boolean;
        absences: Absence[];
    }>(),
    {
        plan: 0,
        fallbackPlan: 0,
        progress: 0,
        workTime: 0,
        breakTime: 0,
        activeWork: false,
    },
);

const percentageWorkTime = computed(() => {
    if (!props.plan) {
        return 0;
    }
    return Math.min((props.workTime / (props.plan * 60 * 60)) * 100, 100);
});

const timePlanDifference = computed(() => {
    return props.workTime - props.plan * 60 * 60;
});

const percentageOverTime = computed(() => {
    return Math.max(
        Math.min(
            (timePlanDifference.value / (props.fallbackPlan * 60 * 60)) * 100,
            100,
        ),
        0,
    );
});
</script>

<template>
    <div class="flex grow flex-col" v-if="props.plan || props.workTime">
        <div
            class="border-muted bg-background text-muted-foreground rounded-t-lg border text-center text-xs"
        >
            {{ props.plan.toLocaleString($page.props.locale) }}
            {{ $t('app.h') }}
        </div>
        <div class="bg-muted relative grow overflow-hidden rounded-b-lg">
            <div
                class="bg-primary absolute inset-x-0 bottom-0 flex flex-col transition-all duration-300"
                :style="{
                    height: `${percentageWorkTime}%`,
                }"
            >
                <div
                    v-if="props.activeWork"
                    class="animate-progress grow bg-gradient-to-t from-transparent via-transparent to-white/40"
                />
            </div>
            <div
                v-if="percentageOverTime"
                class="absolute inset-x-0 bottom-0 flex flex-col bg-amber-400 transition-all duration-300 starting:h-0"
                :style="{
                    height: `${percentageOverTime}%`,
                }"
            >
                <div
                    v-if="props.activeWork"
                    class="animate-progress grow bg-gradient-to-t from-transparent via-transparent to-white/40"
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
            <div
                class="text-muted-foreground flex items-center justify-between gap-1 text-xs"
                v-if="props.workTime && !props.absences.length"
            >
                <BriefcaseBusiness class="size-4 shrink-0" />
                {{ secToFormat(props.workTime ?? 0, false, true) }}
            </div>
            <div
                v-if="timePlanDifference !== 0 && props.workTime"
                class="flex items-center justify-between gap-1 text-xs"
                :class="{
                    'text-green-500': timePlanDifference < 0,
                    'text-amber-400': timePlanDifference > 0,
                }"
            >
                <ClockArrowUp
                    class="size-4 shrink-0"
                    v-if="timePlanDifference > 0"
                />
                <ClockArrowDown
                    class="size-4 shrink-0"
                    v-if="timePlanDifference < 0"
                />
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
