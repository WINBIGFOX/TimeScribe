<script lang="ts" setup>
import { Button } from '@/Components/ui/button'
import { secToFormat } from '@/lib/utils'
import { Project } from '@/types'
import { Head, Link, router, usePoll } from '@inertiajs/vue3'
import { refThrottled, useColorMode } from '@vueuse/core'
import { Coffee, Play, Square, Tally2, X } from 'lucide-vue-next'
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'

const props = defineProps<{
    workTime: number
    breakTime: number
    currentType?: 'work' | 'break'
    currentProject?: Project
}>()

const workSeconds = ref(props.workTime)
const breakSeconds = ref(props.breakTime)
let timer: NodeJS.Timeout | undefined

const workTimeFormatted = computed(() => secToFormat(workSeconds.value))
const breakTimeFormatted = computed(() => secToFormat(breakSeconds.value, true))

const tick = () => {
    if (props.currentType === 'work') {
        workSeconds.value += 1
    } else if (props.currentType === 'break') {
        breakSeconds.value += 1
    }
}

const reload = () => {
    router.flushAll()
    router.reload({
        only: ['workTime', 'breakTime', 'currentType', 'currentProject'],
        showProgress: false
    })
}

if (window.Native) {
    window.Native.on('App\\Events\\TimerStarted', reload)
    window.Native.on('App\\Events\\TimerStopped', reload)
}

onMounted(() => {
    timer = setInterval(tick, 1000)
})

onBeforeUnmount(() => {
    if (timer) {
        clearInterval(timer)
    }
})

watch(
    () => props.workTime,
    (newVal) => {
        workSeconds.value = newVal
    }
)

watch(
    () => props.breakTime,
    (newVal) => {
        breakSeconds.value = newVal
    }
)

usePoll(5000, {
    only: ['workTime', 'breakTime', 'currentType', 'currentProject'],
    showProgress: false
})

useColorMode()

const showMenu = ref(false)
const useShowMenu = refThrottled(showMenu, 5000)
</script>

<template>
    <Head title="Timer" />
    <div
        :style="'--project-color: ' + (props.currentProject?.color ?? '#000000')"
        :class="{
            'opacity-10': !props.currentType && !useShowMenu,
            'opacity-50': props.currentType === 'break' && !useShowMenu
        }"
        class="bg-background ring-offset-background group relative flex h-full items-center justify-center overflow-clip rounded-full border ring-3 ring-(--project-color) ring-offset-1 transition-all duration-500 ring-inset hover:opacity-100! hover:ring-1"
    >
        <transition
            class="transform-gpu transition-all duration-1000"
            enter-from-class="opacity-0 scale-0 max-w-0"
            enter-to-class="opacity-100 scale-100 max-w-7"
            leave-from-class="opacity-100 scale-100 max-w-7"
            leave-to-class="opacity-0 scale-0 max-w-0"
        >
            <div v-if="props.currentProject?.icon" class="text-2xl">
                {{ props.currentProject.icon }}
            </div>
        </transition>

        <div class="relative h-full w-32">
            <transition
                class="absolute inset-0 flex transform-gpu flex-col items-center justify-center transition-all duration-1000"
                enter-from-class="opacity-0 scale-0"
                enter-to-class="opacity-100 scale-100"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-0"
            >
                <div class="text-center" v-if="props.currentType !== 'break'">
                    <div class="text-2xl leading-none font-bold tracking-tighter tabular-nums">
                        {{ workTimeFormatted }}
                    </div>
                    <div class="text-muted-foreground clear-none text-[0.70rem] uppercase">
                        {{ $t('app.work hours') }}
                    </div>
                </div>
            </transition>
            <transition
                class="absolute inset-0 flex transform-gpu flex-col items-center justify-center transition-all duration-1000"
                enter-from-class="opacity-0 scale-0"
                enter-to-class="opacity-100 scale-100"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-0"
            >
                <div class="text-center" v-if="props.currentType === 'break'">
                    <div class="text-2xl leading-none font-bold tracking-tighter tabular-nums">
                        {{ breakTimeFormatted }}
                    </div>
                    <div class="text-muted-foreground clear-none text-[0.70rem] uppercase">
                        {{ $t('app.break') }}
                    </div>
                </div>
            </transition>
        </div>

        <div
            :class="{
                'scale-100! opacity-100!': showMenu
            }"
            class="bg-background absolute inset-2 left-6 flex scale-105 transform-gpu items-stretch rounded-full opacity-0 transition-all duration-500 group-hover:scale-100 group-hover:opacity-100"
        >
            <Button
                v-if="props.currentType"
                :as="Link"
                method="post"
                :href="route('fly-timer.storeStop')"
                preserve-state
                size="lg"
                variant="destructive"
                class="h-full flex-1 rounded-l-full rounded-r-none pl-5! transition-colors"
            >
                <Square />
            </Button>
            <Button
                v-if="props.currentType !== 'work'"
                :as="Link"
                method="post"
                :href="route('fly-timer.storeWork')"
                preserve-state
                size="lg"
                :class="{
                    'rounded-full': !props.currentType,
                    'rounded-l-none rounded-r-full pr-5!': props.currentType
                }"
                class="h-full flex-1 border border-transparent transition-colors"
            >
                <Play />
            </Button>
            <Button
                v-if="props.currentType === 'work'"
                :as="Link"
                method="post"
                :href="route('fly-timer.storeBreak')"
                preserve-state
                size="lg"
                variant="outline"
                class="h-full flex-1 rounded-l-none rounded-r-full pr-5!"
            >
                <Coffee />
            </Button>
        </div>
        <Link
            :href="route('window.fly-timer.close')"
            class="bg-background fixed top-0 right-0 flex size-4 items-center justify-center overflow-clip rounded-full opacity-0 transition-opacity delay-1000 duration-500 group-hover:opacity-100"
        >
            <X
                class="bg-destructive hover:bg-destructive/90 dark:bg-destructive/60 text-destructive-foreground size-4 p-0.5"
            />
        </Link>
        <div
            @mouseover="
                () => {
                    showMenu = true
                    useShowMenu = true
                }
            "
            @mouseleave="showMenu = false"
            :class="{
                'translate-x-0 opacity-100 [app-region:drag]': useShowMenu || showMenu
            }"
            class="text-muted-foreground fixed left-0 flex h-full w-6 -translate-x-6 transform-gpu items-center justify-end overflow-clip rounded-l-full rounded-r-none opacity-0 transition-all duration-500 group-hover:translate-x-0 group-hover:opacity-100 focus-visible:[app-region:drag]"
        >
            <Tally2 class="translate-x-2" />
        </div>
    </div>
</template>

<style>
html,
body {
    background: transparent !important;
}
</style>
