<script lang="ts" setup>
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/Components/ui/tooltip'
import { Timestamp } from '@/types'
import { BriefcaseBusiness, Coffee } from 'lucide-vue-next'
import moment from 'moment/min/moment-with-locales'
import { ref } from 'vue'

const props = withDefaults(
    defineProps<{
        timestamps: Timestamp[]
        overtime?: number
        workTime?: number
    }>(),
    {
        overtime: 0,
        workTime: 0
    }
)

const timeline = ref<Record<number, Timestamp | undefined>>({})

const parseTimestamps = () => {
    props.timestamps.forEach((timestamp) => {
        const start = Math.floor(parseInt(timestamp.started_at.formatted) / 10) * 10
        const ended_at = timestamp.ended_at ?? timestamp.last_ping_at
        let end = Math.floor(parseInt(ended_at?.formatted ?? '') / 10) * 10
        if (end.toString().endsWith('60')) {
            end = end - 60 + 100
        }
        if (end === 2400) {
            end = end - 50
        }

        for (let j = start; j <= end; j += 10) {
            timeline.value[j] = { ...timestamp }

            if (j.toString().endsWith('50')) {
                j += 40
            }
        }
    })
    markOvertime()
}

const markOvertime = () => {
    let overtimeCount = Math.ceil(props.overtime / 600)
    for (const [key, value] of Object.entries(timeline.value).reverse()) {
        if (value?.type === 'work' && (overtimeCount > 0 || props.overtime === props.workTime)) {
            timeline.value[key] = { ...value, type: 'overtime' }
            overtimeCount--
        } else if (overtimeCount <= 0) {
            break
        }
    }
}

const createTimeline = () => {
    for (let i = 0; i < 2400; i += 100) {
        for (let j = 0; j < 60; j += 10) {
            timeline.value[j + i] = undefined
        }
    }
    parseTimestamps()
}

createTimeline()

const drag = ref(false)
const startDragIndex = ref<number | undefined>(undefined)
const currentDragIndex = ref<number | undefined>(undefined)

const ifSelected = (index: number) => {
    if (startDragIndex.value === undefined || currentDragIndex.value === undefined) {
        return false
    }
    const min = Math.min(startDragIndex.value, currentDragIndex.value)
    const max = Math.max(startDragIndex.value, currentDragIndex.value)
    return index >= min && index <= max
}

const dragStart = (index: number) => {
    dragReset()
    drag.value = true
    startDragIndex.value = index
}
const dragOver = (index: number) => {
    if (drag.value && startDragIndex.value !== undefined) {
        currentDragIndex.value = index
    }
}

const dragStop = () => {
    drag.value = false
}

const dragReset = () => {
    drag.value = false
    startDragIndex.value = undefined
    currentDragIndex.value = undefined
}

const dragLeave = () => {
    if (drag.value) {
        dragReset()
    }
}

const indexToTimeFormat = (index: number, withoutMinutesBy12H?: boolean) => {
    const time = moment(
        (index > 100 ? index.toString().slice(0, -2) : '0') + ':' + (index > 10 ? index.toString().slice(-2) : '00'),
        'H:mm'
    )
        .format('LT')
        .replace(/^0([0-9])/g, '$1')

    return withoutMinutesBy12H ? time.replace(/:00 (PM|AM)/, ' $1') : time
}
</script>

<template>
    <div @mouseleave="dragLeave" @mouseup="dragStop" class="relative h-24">
        <div class="absolute inset-x-0 top-3 z-10 mx-0.5 flex justify-between gap-0.5">
            <TooltipProvider :delayDuration="0" :key="index" v-for="(time, index) in timeline">
                <Tooltip>
                    <TooltipTrigger class="group flex-1">
                        <div
                            :class="{
                                'bg-primary ring-primary': time?.type === 'work' || time?.type === 'overtime',
                                'bg-pink-400 ring-pink-400': time?.type === 'break',
                                'ring-gray-300 hover:bg-gray-300 dark:ring-gray-600 dark:hover:bg-gray-600': !time,
                                'bg-gray-400 ring-gray-400 hover:bg-gray-500 hover:ring-gray-500 dark:ring-gray-500 dark:hover:bg-gray-500':
                                    ifSelected(index)
                            }"
                            @mousedown="dragStart(index)"
                            @mouseover="dragOver(index)"
                            class="bg-accent ring-offset-background h-14 shrink-0 rounded-full ring-offset-1 transition-transform duration-100 group-hover:scale-110 group-hover:ring-2"
                        />
                        <div
                            :class="{
                                'bg-amber-400 ring-amber-400': time?.type === 'overtime'
                            }"
                            class="mt-0.5 aspect-square shrink-0 rounded-full"
                        />
                    </TooltipTrigger>
                    <TooltipContent
                        :class="{
                            'bg-muted [&_.fill-primary]:fill-muted [&_.fill-primary]:bg-muted': time === undefined,
                            'bg-primary': time?.type === 'work',
                            'bg-amber-400 [&_.fill-primary]:bg-amber-400 [&_.fill-primary]:fill-amber-400':
                                time?.type === 'overtime',
                            'bg-pink-400 [&_.fill-primary]:bg-pink-400 [&_.fill-primary]:fill-pink-400':
                                time?.type === 'break'
                        }"
                        side="bottom"
                    >
                        <div
                            :class="{
                                'text-muted-foreground': time === undefined
                            }"
                        >
                            {{ indexToTimeFormat(index) }}
                        </div>
                        <div class="flex justify-center">
                            <BriefcaseBusiness
                                class="my-1 size-5 shrink-0"
                                v-if="time?.type === 'work' || time?.type === 'overtime'"
                            />
                            <Coffee class="my-1 size-5 shrink-0" v-if="time?.type === 'break'" />
                        </div>
                    </TooltipContent>
                </Tooltip>
            </TooltipProvider>
        </div>
        <div class="pointer-events-none absolute inset-x-0 top-0 mx-0.5 flex justify-between gap-0.5">
            <div
                :class="{
                    'flex-none': index === 49,
                    'border-gray-300 dark:border-gray-600': index % 4 === 1,
                    'border-gray-100 dark:border-gray-800': index % 4 !== 1 && index % 2 !== 1,
                    'border-gray-200 dark:border-gray-700': index % 2 === 1 && index % 4 !== 1
                }"
                :key="index"
                class="h-20 flex-1 border-l"
                v-for="index in 49"
            ></div>
        </div>
        <div class="pointer-events-none absolute inset-x-0 top-20 mx-0.5 flex justify-between gap-0.5">
            <div
                :class="{
                    'flex-none': index === 13,
                    'pl-1': index !== 13
                }"
                :key="index"
                class="text-muted-foreground flex h-4 flex-1 items-end border-l border-gray-300 text-xs leading-none dark:border-gray-600"
                v-for="index in 13"
            >
                {{ index < 13 ? indexToTimeFormat((index - 1) * 2 * 100, true) : '' }}
            </div>
        </div>
    </div>
</template>
