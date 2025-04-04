<script setup lang="ts">
import { Button } from '@/Components/ui/button';
import { secToFormat } from '@/lib/utils';
import { Timestamp } from '@/types';
import { Link, router, usePoll } from '@inertiajs/vue3';
import {
    BriefcaseBusiness,
    Coffee,
    MoveRight,
    Pencil,
    Timer,
    Trash,
} from 'lucide-vue-next';
import moment from 'moment/min/moment-with-locales';
import { computed } from 'vue';

const props = defineProps<{
    timestamp: Timestamp;
}>();

const duration = computed(() =>
    Math.ceil(
        moment(props.timestamp.ended_at?.date ?? moment())
            .diff(props.timestamp.started_at.date)
            .valueOf() / 1000,
    ),
);

if (duration.value < 60 && !props.timestamp.ended_at) {
    usePoll(1000);
}

const destroy = () => {
    router.delete(
        route('timestamp.destroy', {
            timestamp: props.timestamp.id,
        }),
        {
            data: {
                confirm: false,
            },
            preserveScroll: true,
            preserveState: 'errors',
        },
    );
};
</script>

<template>
    <div class="bg-background mx-4 flex items-center gap-4 rounded-lg p-2.5">
        <div
            class="text-primary-foreground flex size-8 shrink-0 items-center justify-center rounded-md"
            :class="{
                'bg-primary': props.timestamp.type === 'work',
                'bg-pink-400': props.timestamp.type === 'break',
            }"
        >
            <BriefcaseBusiness
                class="size-5"
                v-if="props.timestamp.type === 'work'"
            />
            <Coffee class="size-5" v-if="props.timestamp.type === 'break'" />
        </div>
        <div class="flex w-24 shrink-0 items-center gap-1">
            <Timer class="text-muted-foreground size-4" />
            <span class="font-medium">
                {{
                    duration > 59
                        ? secToFormat(duration, false, true, true)
                        : duration
                }}
            </span>
            <span class="text-muted-foreground text-xs">
                {{ duration > 59 ? $t('app.h') : $t('app.s') }}
            </span>
        </div>

        <div class="ml-2 flex shrink-0 items-center gap-2">
            <div class="flex min-w-16 flex-col items-center gap-1">
                <span class="text-muted-foreground text-xs leading-none">
                    {{ $t('app.start') }}
                </span>
                <span class="leading-none font-medium">
                    {{
                        moment(
                            props.timestamp.started_at.formatted,
                            'Hmm',
                        ).format('LT')
                    }}
                </span>
            </div>
            <MoveRight class="text-muted-foreground size-4" />
            <div
                class="flex min-w-16 flex-col items-center gap-1"
                v-if="props.timestamp.ended_at"
            >
                <span class="text-muted-foreground text-xs leading-none">
                    {{ $t('app.end') }}
                </span>
                <span class="leading-none font-medium">
                    {{
                        moment(
                            (
                                props.timestamp.ended_at ??
                                props.timestamp.last_ping_at
                            )?.formatted,
                            'Hmm',
                        ).format('LT')
                    }}
                </span>
            </div>
            <div
                class="bg-muted text-muted-foreground mx-1 flex items-center gap-2 rounded-lg px-3 py-1"
                v-else
            >
                <div
                    class="size-3 shrink-0 animate-pulse rounded-full bg-red-500"
                />
                {{ $t('app.now') }}
            </div>
        </div>
        <div class="flex-1 text-right" v-if="props.timestamp.ended_at">
            <Button
                v-if="
                    props.timestamp.can_start_edit ||
                    props.timestamp.can_end_edit
                "
                size="icon"
                class="text-muted-foreground size-8"
                variant="ghost"
                :as="Link"
                :href="
                    route('timestamp.edit', {
                        timestamp: props.timestamp.id,
                    })
                "
                preserve-state
                preserve-scroll
            >
                <Pencil />
            </Button>
            <Button
                size="icon"
                class="text-destructive hover:bg-destructive hover:text-destructive-foreground size-8"
                variant="ghost"
                @click="destroy"
            >
                <Trash />
            </Button>
        </div>
    </div>
</template>
