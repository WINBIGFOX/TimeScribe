<script lang="ts" setup>
import { Button } from '@/Components/ui/button'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import BasicLayout from '@/Layouts/BasicLayout.vue'
import { secToFormat } from '@/lib/utils'
import { ActivityHistory } from '@/types'
import { Head, Link, router, usePoll } from '@inertiajs/vue3'
import { useColorMode } from '@vueuse/core'
import { ChartPie, Coffee, Cog, Play, Square } from 'lucide-vue-next'
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'

defineOptions({
    layout: BasicLayout
})

const props = defineProps<{
    currentType?: 'work' | 'break'
    workTime: number
    breakTime: number
    currentAppActivity?: ActivityHistory
    activeAppActivity: boolean
}>()

let timer: NodeJS.Timeout

const workSeconds = ref(props.workTime)
const breakSeconds = ref(props.breakTime)

const workTimeFormatted = computed(() => secToFormat(workSeconds.value))
const breakTimeFormatted = computed(() => secToFormat(breakSeconds.value, true))

const tick = () => {
    if (props.currentType === 'work') {
        workSeconds.value += 1
    } else if (props.currentType === 'break') {
        breakSeconds.value += 1
    }
}

window.Native.on('Native\\Laravel\\Events\\MenuBar\\MenuBarShown', () => {
    router.reload()
})

onMounted(() => {
    timer = setInterval(tick, 1000)
})

usePoll(
    5000,
    {
        only: ['currentAppActivity']
    },
    {
        autoStart: props.activeAppActivity,
        keepAlive: true
    }
)

onBeforeUnmount(() => {
    clearInterval(timer)
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

const { state } = useColorMode()

const loading = ref(false)

router.on('start', () => {
    loading.value = true
})
router.on('finish', () => {
    loading.value = false
})

const handleResize = (height?: number) => {
    router.post(
        route('menubar.resize'),
        {
            height: height
        },
        { preserveScroll: true, preserveState: true }
    )
}

const handleSelectOpenState = (open: boolean) => {
    if (open) {
        handleResize(500)
    } else {
        handleResize()
    }
}
</script>

<template>
    <Head title="Menubar" />

    <div class="bg-background flex h-dvh flex-col select-none">
        <div class="fixed inset-x-0 top-0 flex justify-end">
            <Button
                :as="Link"
                :href="
                    route('window.settings.open', {
                        darkMode: state === 'dark' ? 1 : 0
                    })
                "
                preserve-scroll
                preserve-state
                size="icon"
                variant="ghost"
            >
                <Cog />
            </Button>
        </div>
        <div
            :class="{
                'pt-8': props.currentType !== 'work' || !props.currentAppActivity,
                'pt-0': props.currentType === 'work' && props.currentAppActivity
            }"
            class="flex h-36.5 flex-col items-center justify-center transition-all duration-1000"
        >
            <div
                :class="{
                    'opacity-50': props.currentType === 'break'
                }"
                class="text-center transition-opacity duration-1000"
            >
                <div
                    :class="{
                        'mb-2 h-10 scale-100 opacity-100': props.currentType === 'work' && props.currentAppActivity,
                        'mb-0 h-0 scale-0 opacity-0': props.currentType !== 'work' || !props.currentAppActivity
                    }"
                    class="flex items-center justify-center gap-2 overflow-hidden transition-all duration-1000"
                >
                    <img
                        :src="props.currentAppActivity.app_icon"
                        alt="App-Icon"
                        class="pointer-events-none size-10"
                        v-if="props.currentAppActivity?.app_icon"
                    />
                    <span>
                        {{ props.currentAppActivity?.app_name }}
                    </span>
                </div>

                <div
                    :class="{
                        'text-4xl': props.currentType !== 'break',
                        'text-2xl': props.currentType === 'break'
                    }"
                    class="font-bold tracking-tighter tabular-nums transition-all duration-1000"
                >
                    {{ workTimeFormatted }}
                </div>
                <div
                    :class="{
                        'text-[0.70rem]': props.currentType !== 'break',
                        'text-[0.50rem]': props.currentType === 'break'
                    }"
                    class="text-muted-foreground uppercase transition-all duration-1000"
                >
                    {{ $t('app.work hours') }}
                </div>
            </div>
            <transition
                class="transform-gpu transition-all duration-1000"
                enter-from-class="opacity-0 scale-0 h-0"
                enter-to-class="opacity-100 scale-100 h-14"
                leave-from-class="opacity-100 scale-100 h-14"
                leave-to-class="opacity-0 scale-0 h-0"
            >
                <div class="text-center" v-if="props.currentType === 'break'">
                    <div class="text-4xl font-bold tracking-tighter tabular-nums transition-all duration-1000">
                        {{ breakTimeFormatted }}
                    </div>
                    <div class="text-muted-foreground text-[0.70rem] uppercase transition-all duration-1000">
                        {{ $t('app.break') }}
                    </div>
                </div>
            </transition>
        </div>
        <div class="flex grow flex-col px-2">
            <Select @update:open="handleSelectOpenState">
                <SelectTrigger class="w-full">
                    <SelectValue placeholder="00" />
                </SelectTrigger>
                <SelectContent class="h-36">
                    <SelectItem value="1">Test</SelectItem>
                    <SelectItem value="2">Test</SelectItem>
                    <SelectItem value="3">Test</SelectItem>
                </SelectContent>
            </Select>
        </div>
        <div>
            <div class="flex p-2">
                <Button
                    :as="Link"
                    :href="
                        route('window.overview.open', {
                            darkMode: state === 'dark' ? 1 : 0
                        })
                    "
                    class="flex-1 shrink-0"
                    preserve-scroll
                    preserve-state
                    size="sm"
                    variant="outline"
                >
                    <ChartPie />
                    {{ $t('app.overview') }}
                </Button>
            </div>
            <div class="bg-muted dark:bg-muted/60 flex gap-2 p-2">
                <Button
                    :as="Link"
                    :disabled="loading"
                    :href="route('menubar.storeWork')"
                    @click="router.flushAll()"
                    class="flex-1 shrink-0 px-0 disabled:opacity-100"
                    method="POST"
                    preserve-scroll
                    preserve-state
                    size="lg"
                    v-if="props.currentType === null"
                >
                    <Play />
                    {{ $t('app.start') }}
                </Button>
                <Button
                    :as="Link"
                    :disabled="loading"
                    :href="route('menubar.storeStop')"
                    @click="router.flushAll()"
                    class="flex-1 shrink-0 px-0 disabled:opacity-100"
                    method="POST"
                    preserve-scroll
                    preserve-state
                    size="lg"
                    v-if="props.currentType !== null"
                    variant="destructive"
                >
                    <Square />
                    {{ $t('app.stop') }}
                </Button>
                <Button
                    :as="Link"
                    :disabled="loading"
                    :href="route('menubar.storeBreak')"
                    @click="router.flushAll()"
                    class="flex-1 shrink-0 px-0 disabled:opacity-100"
                    method="POST"
                    preserve-scroll
                    preserve-state
                    size="lg"
                    v-if="props.currentType === 'work'"
                    variant="outline"
                >
                    <Coffee />
                    {{ $t('app.break') }}
                </Button>
                <Button
                    :as="Link"
                    :disabled="loading"
                    :href="route('menubar.storeWork')"
                    @click="router.flushAll()"
                    class="flex-1 shrink-0 px-0 disabled:opacity-100"
                    method="POST"
                    preserve-scroll
                    preserve-state
                    size="lg"
                    v-if="props.currentType === 'break'"
                >
                    <Play />
                    {{ $t('app.continue') }}
                </Button>
            </div>
        </div>
    </div>
</template>
