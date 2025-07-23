<script lang="ts" setup>
import { Button } from '@/Components/ui/button'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/Components/ui/tooltip'
import { secToFormat } from '@/lib/utils'
import { Timestamp } from '@/types'
import { Link, router } from '@inertiajs/vue3'
import { useIntervalFn } from '@vueuse/core'
import { BriefcaseBusiness, Coffee, FolderInput, MoveRight, Pencil, Timer, Trash } from 'lucide-vue-next'
import moment from 'moment/min/moment-with-locales'
import { ref } from 'vue'

const props = defineProps<{
    timestamp: Timestamp
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
</script>

<template>
    <div class="bg-sidebar flex items-center gap-4 rounded-lg p-2.5">
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
            <div
                :style="'--project-color: ' + (props.timestamp.project.color ?? '#000000')"
                class="mx-2 flex h-9 items-center gap-2 rounded-md border-l-6 border-l-[var(--project-color)] bg-[var(--project-color)]/10 px-2 text-sm font-medium dark:bg-[var(--project-color)]/20"
            >
                <div class="flex h-9 shrink-0 items-center text-xl" v-if="props.timestamp.project.icon">
                    {{ props.timestamp.project.icon }}
                </div>
                <div class="line-clamp-1">
                    {{ props.timestamp.project.name }}
                </div>
            </div>
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
