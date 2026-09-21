<script lang="ts" setup>
import { Button } from '@/Components/ui/button'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/Components/ui/tooltip'
import { secToFormat } from '@/lib/utils'
import { Timestamp } from '@/types'
import { Link, router } from '@inertiajs/vue3'
import { BriefcaseBusiness, Coffee, FolderInput, FoldVertical, NotepadText, Pencil, Timer, Trash } from '@lucide/vue'
import { useIntervalFn } from '@vueuse/core'
import moment from 'moment/min/moment-with-locales'
import { computed, ref, watch } from 'vue'

const props = defineProps<{
    timestamp: Timestamp
    timestampBefore?: Timestamp
    timestampAfter?: Timestamp
}>()

const calcDuration = () =>
    Math.ceil(
        moment(props.timestamp.ended_at?.date ?? moment())
            .diff(props.timestamp.started_at.date)
            .valueOf() / 1000
    )

const shouldShowEndTime = computed(() => {
    if (!props.timestamp.ended_at) {
        return false
    }

    return props.timestamp.ended_at.formatted !== props.timestampAfter?.started_at.formatted
})

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
    <div class="relative">
        <div
            :class="{
                'via-primary': props.timestamp.type === 'work',
                'via-pink-400': props.timestamp.type === 'break'
            }"
            class="group absolute inset-x-0 z-10 -mt-3.75 ml-6 flex h-0 justify-center self-start bg-linear-to-r from-transparent to-transparent transition-all duration-300 ease-out after:absolute after:inset-x-44 after:-mt-2 after:h-4 after:content-[''] hover:h-0.5 hover:opacity-100"
            v-if="canMerge() && props.timestampBefore"
        >
            <div
                :class="{
                    'border-b-primary': props.timestamp.type === 'work',
                    'border-b-pink-400': props.timestamp.type === 'break'
                }"
                class="absolute mt-0 w-5 border-b-2 border-dashed"
            ></div>
            <Link
                :class="{
                    'bg-primary': props.timestamp.type === 'work',
                    'bg-pink-400': props.timestamp.type === 'break',
                    'ring-primary': props.timestamp.type === 'work',
                    'ring-pink-400': props.timestamp.type === 'break'
                }"
                :data="{
                    timestamp_before: props.timestampBefore.id,
                    timestamp: props.timestamp.id
                }"
                :href="route('timestamp.merge')"
                class="text-primary-foreground group/merge-button ring-offset-background z-10 -mt-2.5 flex h-5 scale-0 items-center justify-center rounded-full px-1.25 py-0.5 text-xs leading-none ring-offset-2 transition-all duration-300 ease-in-out group-hover:scale-100 group-hover:ease-[cubic-bezier(0.17,0.89,0.32,1.10)] hover:px-2 hover:ring-2"
                method="patch"
                preserve-scroll
                preserve-state
            >
                <FoldVertical
                    class="size-3 transition-[height,width,margin] duration-500 ease-[cubic-bezier(0.17,0.89,0.32,1.10)] group-hover/merge-button:size-3.5 not-rtl:group-hover/merge-button:mr-1 rtl:group-hover/merge-button:ml-1"
                />
                <span
                    class="max-w-0 overflow-clip transition-[max-width] duration-500 ease-[cubic-bezier(0.17,0.89,0.32,1.10)] group-hover/merge-button:max-w-28"
                >
                    {{ $t('app.merge') }}
                </span>
            </Link>
        </div>
    </div>
    <div
        :class="{
            '-mt-3': canMerge() && props.timestampBefore
        }"
        class="flex gap-2"
    >
        <div class="flex flex-col items-center gap-1">
            <div
                :class="{
                    'text-primary': props.timestamp.type === 'work',
                    'text-pink-400': props.timestamp.type === 'break'
                }"
                class="bg-sidebar ring-background relative z-10 flex size-7 shrink-0 items-center justify-center rounded-full border ring-4"
            >
                <BriefcaseBusiness class="size-4" v-if="props.timestamp.type === 'work'" />
                <Coffee class="size-4" v-if="props.timestamp.type === 'break'" />
            </div>
            <div
                :class="{
                    'bg-primary': props.timestamp.type === 'work',
                    'bg-pink-400': props.timestamp.type === 'break'
                }"
                class="z-0 h-full w-0.75 rounded-full"
            ></div>
        </div>
        <div
            :class="{
                'mb-3': props.timestamp.ended_at
            }"
            class="group/timestamp-timeline flex flex-col justify-between gap-4"
        >
            <div class="flex min-w-16 flex-col gap-1">
                <span class="text-muted-foreground text-xs leading-none">
                    {{ $t('app.start') }}
                </span>
                <span class="leading-none font-medium">
                    <bdi>
                        {{ moment(props.timestamp.started_at.formatted, 'Hmm').format('LT') }}
                    </bdi>
                </span>
            </div>

            <div
                :class="{
                    'opacity-0': !shouldShowEndTime && props.timestamp.ended_at,
                    'group-hover/timestamp-timeline:opacity-50': !shouldShowEndTime && props.timestamp.ended_at
                }"
                class="transition-opacity duration-300 ease-in-out"
            >
                <div class="flex min-w-16 flex-col gap-1" v-if="props.timestamp.ended_at">
                    <span class="text-muted-foreground text-xs leading-none">
                        {{ $t('app.end') }}
                    </span>
                    <span class="leading-none font-medium">
                        <bdi>
                            {{
                                moment(
                                    (props.timestamp.ended_at ?? props.timestamp.last_ping_at)?.formatted,
                                    'Hmm'
                                ).format('LT')
                            }}
                        </bdi>
                    </span>
                </div>
                <div
                    class="bg-muted text-muted-foreground -ml-3 flex items-center gap-2 rounded-lg px-3 py-1 text-sm"
                    v-else
                >
                    <div class="size-3 shrink-0 animate-pulse rounded-full bg-red-500" />
                    {{ $t('app.now') }}
                </div>
            </div>
        </div>
        <div
            :class="{
                'mb-3': props.timestamp.ended_at,
                'bg-(--project-color)/10 dark:bg-(--project-color)/20': props.timestamp.project,
                'border-t-6 border-t-(--project-color)':
                    props.timestamp.project && !(canMerge() && props.timestampBefore),
                'bg-sidebar': !props.timestamp.project
            }"
            :style="'--project-color: ' + (props.timestamp.project?.color ?? 'var(--color-sidebar)')"
            class="relative flex flex-1 flex-col overflow-clip rounded-md"
        >
            <div class="absolute top-0.5 right-0 flex items-center justify-end not-rtl:ml-auto rtl:mr-auto">
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
            <Link
                :href="route('project.show', { project: props.timestamp.project.id })"
                class="flex h-9 items-center gap-2 rounded-b-md px-2 pr-20 text-sm font-medium hover:bg-(--project-color)/20 dark:hover:bg-(--project-color)/30"
                preserve-scroll
                preserve-state
                v-if="props.timestamp.project && !(canMerge() && props.timestampBefore)"
            >
                <div class="flex h-9 shrink-0 items-center text-xl" v-if="props.timestamp.project.icon">
                    {{ props.timestamp.project.icon }}
                </div>
                <div class="line-clamp-1 flex-1">
                    {{ props.timestamp.project.name }}
                </div>
            </Link>
            <div class="flex flex-1 items-start gap-4 px-2 py-1">
                <div class="flex w-24 shrink-0 items-center gap-1 rtl:justify-end" dir="ltr">
                    <Timer class="text-muted-foreground size-4" />
                    <span class="font-medium">
                        <bdi>
                            {{ duration > 59 ? secToFormat(duration, false, true, true) : duration }}
                        </bdi>
                    </span>
                    <span class="text-muted-foreground text-xs">
                        <bdi>
                            {{ duration > 59 ? $t('app.h') : $t('app.s') }}
                        </bdi>
                    </span>
                </div>
                <div class="flex grow gap-1" v-if="props.timestamp.description">
                    <NotepadText class="text-muted-foreground mt-1 size-4 shrink-0" />
                    <span class="line-clamp-2 font-medium">
                        {{ props.timestamp.description }}
                    </span>
                </div>
            </div>

            <div
                class="m-1 flex flex-col gap-1 pt-4"
                v-if="props.timestamp.app_usage?.length && props.timestamp.type !== 'break'"
            >
                <span class="text-muted-foreground ml-1 text-xs leading-none">{{ $t('app.app activities') }}</span>
                <div class="bg-background dark:bg-background/70 flex flex-wrap gap-1 rounded-md border p-1">
                    <TooltipProvider :key="app_usage.app_identifier" v-for="app_usage in props.timestamp.app_usage">
                        <Tooltip>
                            <TooltipTrigger as-child>
                                <img :alt="app_usage.app_name" :src="app_usage.app_icon" class="size-6" />
                            </TooltipTrigger>
                            <TooltipContent
                                class="bg-muted [&_.fill-primary]:fill-muted [&_.fill-primary]:bg-muted text-foreground flex items-center gap-1.5 pl-1.5"
                            >
                                <img :alt="app_usage.app_name" :src="app_usage.app_icon" class="size-10" />
                                <div>
                                    <p class="font-bold">{{ app_usage.app_name }}</p>
                                    <div>
                                        <div class="flex w-24 shrink-0 items-center gap-1 rtl:justify-end" dir="ltr">
                                            <Timer class="text-muted-foreground size-4" />
                                            <span class="font-medium">
                                                <bdi>
                                                    {{
                                                        app_usage.duration > 59
                                                            ? secToFormat(app_usage.duration, false, true, true)
                                                            : app_usage.duration
                                                    }}
                                                </bdi>
                                            </span>
                                            <span class="text-muted-foreground text-xs">
                                                <bdi>
                                                    {{ app_usage.duration > 59 ? $t('app.h') : $t('app.s') }}
                                                </bdi>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </TooltipContent>
                        </Tooltip>
                    </TooltipProvider>
                </div>
            </div>
        </div>
    </div>
</template>
