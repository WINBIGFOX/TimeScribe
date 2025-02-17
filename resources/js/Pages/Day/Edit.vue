<script setup lang="ts">
import ConfirmationDialog from '@/Components/dialogs/ConfirmationDialog.vue';
import Timeline from '@/Components/Timeline.vue';
import TimestampListItem from '@/Components/TimestampListItem.vue';
import TimestampListPlaceholderItem from '@/Components/TimestampListPlaceholderItem.vue';
import TimestampTypeBadge from '@/Components/TimestampTypeBadge.vue';
import { Timestamp } from '@/types';
import { Head } from '@inertiajs/vue3';
import { useColorMode } from '@vueuse/core';
import { Modal } from 'inertia-modal';
import moment from 'moment';

const props = defineProps<{
    date: string;
    timestamps: Timestamp[];
    dayWorkTime: number;
    dayBreakTime: number;
    dayPlan?: number;
    dayFallbackPlan?: number;
    dayNoWorkTime: number;
}>();

useColorMode();
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
            :work-time="props.dayWorkTime"
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
                <TimestampListPlaceholderItem />
                <template
                    v-for="(timestamp, index) in props.timestamps"
                    :key="timestamp.id"
                >
                    <TimestampListPlaceholderItem
                        :duration="
                            parseInt(timestamp.started_at.formatted) -
                            parseInt(
                                props.timestamps[index - 1].ended_at
                                    ?.formatted ?? '',
                            )
                        "
                        v-if="
                            index > 0 &&
                            timestamp.started_at.formatted !==
                                props.timestamps[index - 1].ended_at?.formatted
                        "
                    />
                    <TimestampListItem :timestamp="timestamp" />
                </template>
                <TimestampListPlaceholderItem
                    v-if="
                        props.date !== moment().format('DD.MM.YYYY') &&
                        props.timestamps.length > 0
                    "
                />
            </div>
        </div>
    </div>
    <Modal />
    <ConfirmationDialog />
</template>
