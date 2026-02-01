<script lang="ts" setup>
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/Components/ui/tooltip'
import { Timestamp } from '@/types'
import { router } from '@inertiajs/vue3'
import { BriefcaseBusiness, Coffee } from 'lucide-vue-next'
import moment from 'moment/min/moment-with-locales'
import { ref } from 'vue'

const props = withDefaults(
    defineProps<{
        date: string
        timestamps: Timestamp[]
        overtime?: number
        workTime?: number
    }>(),
    {
        overtime: 0,
        workTime: 0
    }
)

const timeline = ref<Record<string, Timestamp | undefined>>({})
const isToday = moment().isSame(moment(props.date, 'YYYY-MM-DD'), 'day')
const isFuture = moment().isBefore(moment(props.date, 'YYYY-MM-DD'), 'day')

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
            if (j === end && ended_at && ended_at.date.endsWith(':00')) {
                continue
            }
            timeline.value[j.toString()] = { ...timestamp }

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
            timeline.value[(j + i).toString()] = undefined
        }
    }
    parseTimestamps()
}

createTimeline()

const drag = ref(false)
const startDragIndex = ref<number | undefined>(undefined)
const currentDragIndex = ref<number | undefined>(undefined)

const ifSelected = (index: string) => {
    if (startDragIndex.value === undefined || currentDragIndex.value === undefined) {
        return false
    }
    const min = Math.min(startDragIndex.value, currentDragIndex.value)
    const max = Math.max(startDragIndex.value, currentDragIndex.value)
    return parseInt(index) >= min && parseInt(index) <= max
}

const dragStart = (index: string) => {
    dragReset()
    drag.value = true
    startDragIndex.value = parseInt(index)
}
const dragOver = (index: string) => {
    if (drag.value && startDragIndex.value !== undefined) {
        if (isToday && parseInt(moment().format('HHmm')) < parseInt(index.toString())) {
            dragStop()
            return
        }
        currentDragIndex.value = parseInt(index)
    }
}

const dragStop = () => {
    if (startDragIndex.value !== undefined && currentDragIndex.value !== undefined && drag.value === true) {
        let startDatetime = createDateTimeFromIndex(startDragIndex.value).format('YYYY-MM-DD HH:mm:ss')
        let endDatetime = createDateTimeFromIndex(currentDragIndex.value, 10).format('YYYY-MM-DD HH:mm:ss')
        if (startDragIndex.value > currentDragIndex.value) {
            startDatetime = createDateTimeFromIndex(currentDragIndex.value).format('YYYY-MM-DD HH:mm:ss')
            endDatetime = createDateTimeFromIndex(startDragIndex.value, 10).format('YYYY-MM-DD HH:mm:ss')
        }
        router.visit(
            route('timestamp.create', {
                datetime: startDatetime,
                endDatetime: endDatetime
            }),
            {
                preserveState: true,
                preserveScroll: true
            }
        )
    }
    drag.value = false
}

const createDateTimeFromIndex = (index: number, addMinutes?: number) => {
    const date = moment(props.date, 'YYYY-MM-DD')
    const hours = Math.floor(index / 100)
    const minutes = index % 100
    date.hour(hours)
    date.minute(minutes)
    date.second(0)
    if (addMinutes) {
        date.add(addMinutes, 'm')
    }
    return date
}

const dragReset = () => {
    drag.value = false
    startDragIndex.value = undefined
    currentDragIndex.value = undefined
}

const dragLeave = () => {
    if (drag.value) {
        if (currentDragIndex.value === 0 || currentDragIndex.value === 2350) {
            dragStop()
        } else {
            dragReset()
        }
    }
}

const indexToTimeFormat = (index: string, withoutMinutesBy12H?: boolean) => {
    const time = moment(
        (parseInt(index) > 100 ? index.toString().slice(0, -2) : '0') +
            ':' +
            (parseInt(index) > 10 ? index.toString().slice(-2) : '00'),
        'H:mm'
    )
        .format('LT')
        .replace(/^0([0-9])/g, '$1')

    return withoutMinutesBy12H ? time.replace(/:00 (PM|AM)/, ' $1') : time
}
</script>

<template>
    <div>
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
                                    'bg-gray-400! ring-gray-400! hover:bg-gray-500! hover:ring-gray-500! dark:ring-gray-500! dark:hover:bg-gray-500!':
                                        ifSelected(index)
                                }"
                                @mousedown="!time && !isFuture ? dragStart(index) : undefined"
                                @mouseover="!time && !isFuture ? dragOver(index) : dragStop()"
                                class="bg-muted ring-offset-background h-14 shrink-0 rounded-full ring-offset-1 transition-transform duration-100 group-hover:scale-110 group-hover:ring-2"
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
                    {{ index < 13 ? indexToTimeFormat(((index - 1) * 2 * 100).toString(), true) : '' }}
                </div>
            </div>
        </div>
    </div>
</template>
