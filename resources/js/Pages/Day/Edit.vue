<script setup lang="ts">
import Timeline from '@/Components/Timeline.vue';
import TimestampTypeBadge from '@/Components/TimestampTypeBadge.vue';
import { insertColon, secToFormat } from '@/lib/utils';
import { Timestamp } from '@/types';
import { Head } from '@inertiajs/vue3';
import { useColorMode } from '@vueuse/core';
import {
    BriefcaseBusiness,
    Clock,
    Coffee,
    MoveRight,
    Plus,
    Timer,
} from 'lucide-vue-next';

const props = defineProps<{
    date: string;
    timestamps: Timestamp[];
    dayWorkTime: number;
    dayBreakTime: number;
    dayPlan?: number;
    dayFallbackPlan?: number;
    dayNoWorkTime: number;
}>();

const color = useColorMode();
</script>

<template>
    <Head title="Stempeluhr" />

    <div
        class="flex h-10 shrink-0 items-center justify-center font-medium"
        style="-webkit-app-region: drag"
    >
        Montag {{ props.date }}
    </div>
    <div class="flex grow flex-col overflow-hidden select-none">
        <Timeline
            :timestamps="props.timestamps"
            class="mx-4 mb-4 shrink-0"
            :overtime="
                Math.max(props.dayWorkTime - (props.dayPlan ?? 0) * 60 * 60, 0)
            "
        />
        <div class="bg-muted/50 flex gap-2 px-4 py-2">
            <TimestampTypeBadge type="work" :duration="props.dayWorkTime" />
            <TimestampTypeBadge type="break" :duration="props.dayBreakTime" />
            <TimestampTypeBadge type="noWork" :duration="props.dayNoWorkTime" />
            <TimestampTypeBadge
                type="overtime"
                :duration="
                    Math.max(
                        props.dayWorkTime - (props.dayPlan ?? 0) * 60 * 60,
                        0,
                    )
                "
            />
            <TimestampTypeBadge
                type="plan"
                :duration="(props.dayPlan ?? 0) * 60 * 60"
            />
        </div>
        <div class="bg-muted grow overflow-auto">
            <div class="space-y-1 py-4">
                <div
                    class="border-muted-foreground mx-10 border-l-3 border-dotted"
                >
                    <div
                        class="text-muted-foreground flex items-center gap-1 py-1 pl-4 text-sm"
                    >
                        <Plus class="size-4" />
                        Zeit hinzufügen
                    </div>
                </div>

                <template
                    v-for="(timestamp, index) in props.timestamps"
                    :key="timestamp.id"
                >
                    <div
                        v-if="
                            index > 0 &&
                            timestamp.started_at.formatted !==
                                props.timestamps[index - 1].ended_at?.formatted
                        "
                        class="border-muted-foreground text-muted-foreground mx-10 flex gap-4 border-l-3 border-dotted py-2 pl-4 text-sm"
                    >
                        <div>
                            {{
                                parseInt(timestamp.started_at.formatted) -
                                parseInt(
                                    props.timestamps[index - 1].ended_at
                                        ?.formatted ?? '',
                                )
                            }}
                            Minuten
                        </div>
                        <div class="flex items-center gap-1">
                            <Plus class="size-4" />
                            Zeit hinzufügen
                        </div>
                    </div>
                    <div
                        class="bg-background mx-4 flex items-center justify-between gap-2.5 rounded-lg p-2.5"
                    >
                        <div
                            class="text-primary-foreground flex size-8 items-center justify-center rounded-md"
                            :class="{
                                'bg-primary': timestamp.type === 'work',
                                'bg-pink-400': timestamp.type === 'break',
                            }"
                        >
                            <BriefcaseBusiness
                                class="size-5"
                                v-if="timestamp.type === 'work'"
                            />
                            <Coffee
                                class="size-5"
                                v-if="timestamp.type === 'break'"
                            />
                        </div>
                        <div class="flex flex-1 items-center gap-1">
                            <Timer class="size-4" />
                            {{
                                secToFormat(
                                    (parseInt(
                                        timestamp.ended_at?.formatted ??
                                            timestamp.last_ping_at?.formatted ??
                                            '0',
                                    ) -
                                        parseInt(
                                            timestamp.started_at.formatted,
                                        )) *
                                        60,
                                    false,
                                    true,
                                    true,
                                )
                            }}
                            <span class="text-muted-foreground text-xs">
                                Std.
                            </span>
                        </div>

                        <div class="flex items-center gap-1">
                            <Clock class="size-4" />
                            <div>
                                {{
                                    insertColon(
                                        timestamp.started_at.formatted,
                                        timestamp.started_at.formatted.length >
                                            3
                                            ? 2
                                            : 1,
                                    )
                                }}
                            </div>
                            <MoveRight class="size-4" />
                            <div>
                                {{
                                    insertColon(
                                        (
                                            timestamp.ended_at ??
                                            timestamp.last_ping_at
                                        )?.formatted ?? '',
                                        ((
                                            timestamp.ended_at ??
                                            timestamp.last_ping_at
                                        )?.formatted.length ?? 0 > 3)
                                            ? 2
                                            : 1,
                                    )
                                }}
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>
