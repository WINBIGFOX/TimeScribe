<script setup lang="ts">
import { Button } from '@/Components/ui/button';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/Components/ui/tooltip';
import { insertColon, secToFormat } from '@/lib/utils';
import { Timestamp } from '@/types';
import { usePoll } from '@inertiajs/vue3';
import {
    BriefcaseBusiness,
    Coffee,
    MoveRight,
    Pencil,
    Timer,
    Trash,
} from 'lucide-vue-next';
import moment from 'moment/moment';
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

if (duration.value < 60) {
    usePoll(1000);
}
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
                {{ duration > 59 ? 'Std.' : 's' }}
            </span>
        </div>

        <div class="ml-2 flex shrink-0 items-center gap-2">
            <div class="flex w-12 flex-col items-center gap-1">
                <span class="text-muted-foreground text-xs leading-none">
                    Start
                </span>
                <span class="leading-none font-medium">
                    {{
                        insertColon(
                            props.timestamp.started_at.formatted,
                            props.timestamp.started_at.formatted.length > 3
                                ? 2
                                : 1,
                        )
                    }}
                </span>
            </div>
            <MoveRight class="text-muted-foreground size-4" />
            <div
                class="flex w-12 flex-col items-center gap-1"
                v-if="props.timestamp.ended_at"
            >
                <span class="text-muted-foreground text-xs leading-none">
                    Ende
                </span>
                <span class="leading-none font-medium">
                    {{
                        insertColon(
                            (
                                props.timestamp.ended_at ??
                                props.timestamp.last_ping_at
                            )?.formatted ?? '',
                            props.timestamp.ended_at.formatted.length > 3
                                ? 2
                                : 1,
                        )
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
                Jetzt
            </div>
        </div>
        <div class="flex-1 text-right" v-if="props.timestamp.ended_at">
            <TooltipProvider :delay-duration="0">
                <Tooltip>
                    <TooltipTrigger as-child>
                        <Button
                            size="icon"
                            class="text-muted-foreground size-8"
                            variant="ghost"
                        >
                            <Pencil />
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>
                        <p>Bearbeiten</p>
                    </TooltipContent>
                </Tooltip>
            </TooltipProvider>
            <TooltipProvider :delay-duration="0">
                <Tooltip>
                    <TooltipTrigger as-child>
                        <Button
                            size="icon"
                            class="text-destructive hover:bg-destructive hover:text-destructive-foreground size-8"
                            variant="ghost"
                        >
                            <Trash />
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>
                        <p>Löschen</p>
                    </TooltipContent>
                </Tooltip>
            </TooltipProvider>
        </div>
    </div>
</template>
