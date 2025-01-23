<script setup lang="ts">
import { secToFormat } from '@/lib/utils';
import { BriefcaseBusiness, Coffee,TriangleAlert } from 'lucide-vue-next';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        plan?: number;
        fallbackPlan?: number;
        workTime?: number;
        breakTime?: number;
    }>(),
    {
        plan: 0,
        fallbackPlan: 0,
        progress: 0,
        workTime: 0,
        breakTime: 0,
    },
);

const percentageWorkTime = computed(() => {
    if (!props.plan) {
        return 0;
    }
    return Math.min((props.workTime / (props.plan * 60 * 60)) * 100, 100);
});

const percentageOverTime = computed(() => {
    return Math.max(
        Math.min(
            ((props.workTime - props.plan * 60 * 60) /
                (props.fallbackPlan * 60 * 60)) *
                100,
            100,
        ),
        0,
    );
});
</script>

<template>
    <div
        class="mx-auto flex w-14 grow flex-col"
        v-if="props.plan || props.workTime"
    >
        <div
            class="border-muted bg-background text-muted-foreground rounded-t-lg border text-center text-xs"
        >
            {{ props.plan.toLocaleString('de') }} Std.
        </div>
        <div class="bg-muted relative grow overflow-hidden rounded-b-lg">
            <div
                class="bg-primary absolute inset-x-0 bottom-0 transition-all duration-300"
                :style="{
                    height: `${percentageWorkTime}%`,
                }"
            />
            <div
                class="absolute inset-x-0 bottom-0 bg-amber-400 transition-all duration-300"
                :style="{
                    height: `${percentageOverTime}%`,
                }"
            />
        </div>
        <div class="mt-2 h-9 space-y-1">
            <div
                class="text-muted-foreground flex items-center gap-1 text-xs"
                v-if="props.workTime && props.workTime - props.plan * 60 * 60 < 0"
            >
                <BriefcaseBusiness class="size-4 shrink-0" />
                {{ secToFormat(props.workTime ?? 0, false, true) }}
            </div>
            <div
                v-if="props.workTime - props.plan * 60 * 60 > 0"
                class="flex items-center gap-1 text-xs text-amber-400"
            >
                <TriangleAlert class="size-4 shrink-0" />
                +{{
                    secToFormat(
                        props.workTime - props.plan * 60 * 60,
                        false,
                        true,
                        true
                    )
                }}
            </div>
            <div
                class="text-muted-foreground flex items-center gap-1 text-xs"
                v-if="props.breakTime"
            >
                <Coffee class="size-4 shrink-0" />
                {{ secToFormat(props.breakTime ?? 0, false, true) }}
            </div>
        </div>
    </div>
</template>
