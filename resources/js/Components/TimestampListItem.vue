<script lang="ts" setup>
import { Button } from '@/Components/ui/button'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/Components/ui/tooltip'
import { secToFormat } from '@/lib/utils'
import { Timestamp } from '@/types'
import { Link, router } from '@inertiajs/vue3'
import { useIntervalFn } from '@vueuse/core'
import { BriefcaseBusiness, Coffee, FolderInput, FoldVertical, MoveRight, Pencil, Timer, Trash } from 'lucide-vue-next'
import moment from 'moment/min/moment-with-locales'
import { ref, watch } from 'vue'

const props = defineProps<{
    timestamp: Timestamp
    timestampBefore?: Timestamp
}>()

const calcDuration = () =>
    Math.ceil(
        moment(props.timestamp.ended_at?.date ?? moment())
            .diff(props.timestamp.started_at.date)
            .valueOf() / 1000
    )

const duration = ref(calcDuration())

if (!props.timestamp.ended_at) {
    const { pause } = useIntervalFn(() => {
        if (props.timestamp.ended_at) {
            pause()
        }
        duration.value = calcDuration()
    }, 1000)
}

const destroy = () => {
    router.delete(
        route('timestamp.destroy', {
            timestamp: props.timestamp.id
        }),
        {
            data: {
                confirm: false
            },
            preserveScroll: true,
            preserveState: 'errors'
        }
    )
}

const canMerge = () => {
    if (!props.timestampBefore) return false
    return (
        props.timestampBefore.type === props.timestamp.type &&
        props.timestampBefore.project?.id === props.timestamp.project?.id &&
        props.timestampBefore.ended_at !== null &&
        (props.timestampBefore.ended_at?.formatted === props.timestamp.started_at.formatted ||
            Math.floor(
                moment(props.timestamp.started_at.date).diff(props.timestampBefore.ended_at?.date).valueOf() / 1000 / 60
            ) === 0) &&
        props.timestampBefore.paid === props.timestamp.paid
    )
}

watch(
    () => [props.timestamp.started_at.date, props.timestamp.ended_at?.date],
    () => {
        duration.value = calcDuration()
    }
)
</script>

<template>
    <div class="bg-sidebar relative flex items-center gap-4 rounded-lg p-2.5">
        <div
            v-if="canMerge() && props.timestampBefore"
            :class="{
                'via-primary': props.timestamp.type === 'work',
                'via-pink-400': props.timestamp.type === 'break'
            }"
            class="group absolute inset-x-0 z-10 -mt-3.25 flex h-0 justify-center self-start bg-gradient-to-r from-transparent to-transparent transition-all duration-300 ease-out after:absolute after:inset-x-44 after:-mt-2 after:h-4 after:content-[''] hover:h-0.5 hover:opacity-100"
        >
            <div
                :class="{
                    'border-b-primary': props.timestamp.type === 'work',
                    'border-b-pink-400': props.timestamp.type === 'break'
                }"
                class="absolute -mt-0 w-5 border-b-2 border-dashed"
            ></div>
            <Link
                :href="route('timestamp.merge')"
                method="patch"
                :data="{
                    timestamp_before: props.timestampBefore.id,
                    timestamp: props.timestamp.id
                }"
                preserve-scroll
                preserve-state
                :class="{
                    'bg-primary': props.timestamp.type === 'work',
                    'bg-pink-400': props.timestamp.type === 'break',
                    'ring-primary': props.timestamp.type === 'work',
                    'ring-pink-400': props.timestamp.type === 'break'
                }"
                class="text-primary-foreground group/merge-button ring-offset-background z-10 -mt-2.5 flex h-5 scale-0 items-center justify-center rounded-full px-1.25 py-0.5 text-xs leading-none ring-offset-2 transition-all duration-300 ease-in-out group-hover:scale-100 group-hover:ease-[cubic-bezier(0.17,0.89,0.32,1.10)] hover:px-2 hover:ring-2"
            >
                <FoldVertical
                    class="size-3 transition-[height,width,margin] duration-500 ease-[cubic-bezier(0.17,0.89,0.32,1.10)] group-hover/merge-button:mr-1 group-hover/merge-button:size-3.5"
                />
                <span
                    class="max-w-0 overflow-clip transition-[max-width] duration-500 ease-[cubic-bezier(0.17,0.89,0.32,1.10)] group-hover/merge-button:max-w-28"
                >
                    {{ $t('app.merge') }}
                </span>
            </Link>
        </div>
        <div
            :class="{
                'bg-primary': props.timestamp.type === 'work',
                'bg-pink-400': props.timestamp.type === 'break'
            }"
            class="text-primary-foreground flex size-8 shrink-0 items-center justify-center rounded-md"
        >
            <BriefcaseBusiness class="size-5" v-if="props.timestamp.type === 'work'" />
            <Coffee class="size-5" v-if="props.timestamp.type === 'break'" />
        </div>
        <div class="flex w-24 shrink-0 items-center gap-1">
            <Timer class="text-muted-foreground size-4" />
            <span class="font-medium">
                {{ duration > 59 ? secToFormat(duration, false, true, true) : duration }}
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
                    {{ moment(props.timestamp.started_at.formatted, 'Hmm').format('LT') }}
                </span>
            </div>
            <MoveRight class="text-muted-foreground size-4" />
            <div class="flex min-w-16 flex-col items-center gap-1" v-if="props.timestamp.ended_at">
                <span class="text-muted-foreground text-xs leading-none">
                    {{ $t('app.end') }}
                </span>
                <span class="leading-none font-medium">
                    {{
                        moment((props.timestamp.ended_at ?? props.timestamp.last_ping_at)?.formatted, 'Hmm').format(
                            'LT'
                        )
                    }}
                </span>
            </div>
            <div class="bg-muted text-muted-foreground mx-1 flex items-center gap-2 rounded-lg px-3 py-1" v-else>
                <div class="size-3 shrink-0 animate-pulse rounded-full bg-red-500" />
                {{ $t('app.now') }}
            </div>
        </div>
        <div class="flex flex-1 grow flex-col gap-1" v-if="props.timestamp.project">
            <Link
                preserve-scroll
                preserve-state
                :href="route('project.show', { project: props.timestamp.project.id })"
                :style="'--project-color: ' + (props.timestamp.project.color ?? '#000000')"
                class="mx-2 flex h-9 items-center gap-2 rounded-md border-l-6 border-l-(--project-color) bg-(--project-color)/10 px-2 text-sm font-medium hover:bg-(--project-color)/20 dark:bg-(--project-color)/20 dark:hover:bg-(--project-color)/30"
            >
                <div class="flex h-9 shrink-0 items-center text-xl" v-if="props.timestamp.project.icon">
                    {{ props.timestamp.project.icon }}
                </div>
                <div class="line-clamp-1 flex-1">
                    {{ props.timestamp.project.name }}
                </div>
            </Link>
        </div>
        <div class="flex grow flex-col gap-1" v-if="props.timestamp.description">
            <span class="text-muted-foreground text-xs leading-none">
                {{ $t('app.notes') }}
            </span>
            <span class="line-clamp-1 leading-none font-medium">
                {{ props.timestamp.description }}
            </span>
        </div>
        <div class="ml-auto flex items-center justify-end">
            <TooltipProvider v-if="props.timestamp.source">
                <Tooltip>
                    <TooltipTrigger as-child>
                        <FolderInput class="text-muted-foreground mr-2 size-4" />
                    </TooltipTrigger>
                    <TooltipContent>
                        <p>{{ $t('app.imported from :name', { name: props.timestamp.source }) }}</p>
                    </TooltipContent>
                </Tooltip>
            </TooltipProvider>

            <Button
                :as="Link"
                :href="
                    route('timestamp.edit', {
                        timestamp: props.timestamp.id
                    })
                "
                class="text-muted-foreground size-8"
                preserve-scroll
                preserve-state
                size="icon"
                variant="ghost"
            >
                <Pencil />
            </Button>
            <Button
                @click="destroy"
                class="text-destructive hover:bg-destructive hover:text-destructive-foreground size-8"
                size="icon"
                v-if="props.timestamp.ended_at"
                variant="ghost"
            >
                <Trash />
            </Button>
        </div>
    </div>
</template>
