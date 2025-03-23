<script setup lang="ts">
import { Button } from '@/Components/ui/button';
import { secToFormat } from '@/lib/utils';
import { ActivityHistory } from '@/types';
import { Head, Link, usePoll } from '@inertiajs/vue3';
import { useColorMode } from '@vueuse/core';
import {
    CalendarDays,
    ChartPie,
    Coffee,
    Cog,
    Play,
    Square,
} from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps<{
    currentType?: 'work' | 'break';
    workTime: number;
    breakTime: number;
    currentAppActivity?: ActivityHistory;
}>();

let timer: NodeJS.Timeout;

const workSeconds = ref(props.workTime);
const breakSeconds = ref(props.breakTime);

const workTimeFormatted = computed(() => secToFormat(workSeconds.value));
const breakTimeFormatted = computed(() =>
    secToFormat(breakSeconds.value, true),
);

const tick = () => {
    if (props.currentType === 'work') {
        workSeconds.value += 1;
    } else if (props.currentType === 'break') {
        breakSeconds.value += 1;
    }
};

window.Native.on('Native\\Laravel\\Events\\MenuBar\\MenuBarShown', () => {
    window.location.reload();
});

onMounted(() => {
    timer = setInterval(tick, 1000);
});

usePoll(
    5000,
    {
        only: ['currentAppActivity'],
    },
    {
        autoStart: true,
        keepAlive: true,
    },
);

onBeforeUnmount(() => {
    clearInterval(timer);
});

watch(
    () => props.workTime,
    (newVal) => {
        workSeconds.value = newVal;
    },
);

watch(
    () => props.breakTime,
    (newVal) => {
        breakSeconds.value = newVal;
    },
);

const { state } = useColorMode();
</script>

<template>
    <Head title="Menubar" />
    <div class="flex h-dvh flex-col select-none">
        <div class="fixed inset-x-0 top-0 flex justify-end">
            <!--
            <Button size="icon" variant="ghost">
                <Power />
            </Button>
            -->
            <Button
                :as="Link"
                :href="
                    route('menubar.openSetting', {
                        darkMode: state === 'dark' ? 1 : 0,
                    })
                "
                size="icon"
                preserve-scroll
                preserve-state
                variant="ghost"
            >
                <Cog />
            </Button>
        </div>
        <div
            class="flex grow flex-col items-center justify-center transition-all duration-1000"
            :class="{
                'pt-8':
                    props.currentType !== 'work' || !props.currentAppActivity,
                'pt-0':
                    props.currentType === 'work' && props.currentAppActivity,
            }"
        >
            <div
                class="text-center transition-opacity duration-1000"
                :class="{
                    'opacity-50': props.currentType === 'break',
                }"
            >
                <div
                    class="flex items-center justify-center gap-2 overflow-hidden transition-all duration-1000"
                    :class="{
                        'mb-2 h-10 scale-100 opacity-100':
                            props.currentType === 'work' &&
                            props.currentAppActivity,
                        'mb-0 h-0 scale-0 opacity-0':
                            props.currentType !== 'work' ||
                            !props.currentAppActivity,
                    }"
                >
                    <img
                        v-if="props.currentAppActivity?.app_icon"
                        alt="App-Icon"
                        class="pointer-events-none size-10"
                        :src="props.currentAppActivity.app_icon"
                    />
                    <span>
                        {{ props.currentAppActivity?.app_name }}
                    </span>
                </div>

                <div
                    class="font-bold tracking-tighter tabular-nums transition-all duration-1000"
                    :class="{
                        'text-4xl': props.currentType !== 'break',
                        'text-2xl': props.currentType === 'break',
                    }"
                >
                    {{ workTimeFormatted }}
                </div>
                <div
                    class="text-muted-foreground uppercase transition-all duration-1000"
                    :class="{
                        'text-[0.70rem]': props.currentType !== 'break',
                        'text-[0.50rem]': props.currentType === 'break',
                    }"
                >
                    {{ $t('app.work hours') }}
                </div>
            </div>
            <transition
                enter-from-class="opacity-0 scale-0 h-0"
                enter-to-class="opacity-100 scale-100 h-14"
                leave-from-class="opacity-100 scale-100 h-14"
                leave-to-class="opacity-0 scale-0 h-0"
                class="transition-all duration-1000"
            >
                <div class="text-center" v-if="props.currentType === 'break'">
                    <div
                        class="text-4xl font-bold tracking-tighter tabular-nums transition-all duration-1000"
                    >
                        {{ breakTimeFormatted }}
                    </div>
                    <div
                        class="text-muted-foreground text-[0.70rem] uppercase transition-all duration-1000"
                    >
                        {{ $t('app.break') }}
                    </div>
                </div>
            </transition>
        </div>
        <div>
            <div class="flex gap-2 p-2">
                <Button
                    :as="Link"
                    :href="
                        route('menubar.openOverview', {
                            darkMode: state === 'dark' ? 1 : 0,
                        })
                    "
                    preserve-scroll
                    preserve-state
                    class="flex-1 shrink-0"
                    variant="outline"
                    size="sm"
                >
                    <ChartPie />
                    {{ $t('app.overview') }}
                </Button>
                <Button
                    :as="Link"
                    :href="
                        route('menubar.openAbsence', {
                            darkMode: state === 'dark' ? 1 : 0,
                        })
                    "
                    preserve-scroll
                    preserve-state
                    class="flex-1 shrink-0"
                    variant="outline"
                    size="sm"
                >
                    <CalendarDays />
                    {{ $t('app.absences') }}
                </Button>
            </div>
            <div class="bg-muted flex gap-2 p-2">
                <Button
                    :as="Link"
                    :href="route('menubar.storeWork')"
                    method="POST"
                    preserve-scroll
                    preserve-state
                    v-if="props.currentType === null"
                    class="flex-1 shrink-0 px-0"
                    size="lg"
                >
                    <Play />
                    {{ $t('app.start') }}
                </Button>
                <Button
                    :as="Link"
                    :href="route('menubar.storeStop')"
                    method="POST"
                    preserve-scroll
                    preserve-state
                    v-if="props.currentType !== null"
                    class="flex-1 shrink-0 px-0"
                    size="lg"
                    variant="destructive"
                >
                    <Square />
                    {{ $t('app.stop') }}
                </Button>
                <Button
                    :as="Link"
                    :href="route('menubar.storeBreak')"
                    method="POST"
                    preserve-scroll
                    preserve-state
                    v-if="props.currentType === 'work'"
                    class="flex-1 shrink-0 px-0"
                    size="lg"
                    variant="outline"
                >
                    <Coffee />
                    {{ $t('app.break') }}
                </Button>
                <Button
                    :as="Link"
                    :href="route('menubar.storeWork')"
                    method="POST"
                    preserve-scroll
                    preserve-state
                    v-if="props.currentType === 'break'"
                    class="flex-1 shrink-0 px-0"
                    size="lg"
                >
                    <Play />
                    {{ $t('app.continue') }}
                </Button>
            </div>
        </div>
    </div>
</template>
