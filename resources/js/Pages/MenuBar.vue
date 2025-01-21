<script setup lang="ts">
import { Button } from '@/Components/ui/button';
import { Head, Link, router } from '@inertiajs/vue3';
import { useColorMode } from '@vueuse/core';
import {
    CalendarDays,
    ChartPie,
    Coffee,
    Cog,
    Play,
    Square,
} from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';

const props = defineProps<{
    currentType?: 'work' | 'break';
    workTime: number;
    breakTime: number;
}>();

let timer: undefined | number = undefined;

const workSeconds = ref(props.workTime);
const breakSeconds = ref(props.breakTime);

const secToFormat = (seconds: number, withoutHours?: boolean) => {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = Math.floor(seconds % 60);
    if (withoutHours) {
        return `${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
    }
    return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
};

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
    router.reload();
});

onMounted(() => {
    timer = setInterval(tick, 1000);
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

useColorMode();
</script>

<template>
    <Head title="Stempeluhr" />
    <div class="flex h-dvh select-none flex-col">
        <div class="flex justify-end">
            <!--
            <Button size="icon" variant="ghost">
                <Power />
            </Button>
            -->
            <Button
                :as="Link"
                :href="route('menubar.openSetting')"
                size="icon"
                preserve-scroll
                preserve-state
                variant="ghost"
            >
                <Cog />
            </Button>
        </div>
        <div class="flex grow flex-col items-center justify-center">
            <div
                class="text-center transition-opacity duration-1000"
                :class="{
                    'opacity-50': props.currentType === 'break',
                }"
            >
                <div
                    class="font-bold tracking-tighter transition-all duration-1000"
                    :class="{
                        'text-4xl': props.currentType !== 'break',
                        'text-2xl': props.currentType === 'break',
                    }"
                >
                    {{ workTimeFormatted }}
                </div>
                <div
                    class="uppercase text-muted-foreground transition-all duration-1000"
                    :class="{
                        'text-[0.70rem]': props.currentType !== 'break',
                        'text-[0.50rem]': props.currentType === 'break',
                    }"
                >
                    Arbeitszeit
                </div>
            </div>
            <transition
                appear
                enter-from-class="opacity-0 scale-0 h-0"
                enter-to-class="opacity-100 scale-100 h-14"
                leave-from-class="opacity-100 scale-100 h-14"
                leave-to-class="opacity-0 scale-0 h-0"
                class="h-0 opacity-0 transition-all duration-1000"
            >
                <div
                    class="h-0 text-center"
                    v-if="props.currentType === 'break'"
                >
                    <div
                        class="text-4xl font-bold tracking-tighter transition-all duration-1000"
                    >
                        {{ breakTimeFormatted }}
                    </div>
                    <div
                        class="text-[0.70rem] uppercase text-muted-foreground transition-all duration-1000"
                    >
                        Pause
                    </div>
                </div>
            </transition>
        </div>
        <div class="">
            <div class="flex gap-2 p-2">
                <Button
                    :as="Link"
                    :href="route('menubar.openOverview')"
                    preserve-scroll
                    preserve-state
                    class="flex-1"
                    variant="outline"
                    size="sm"
                >
                    <ChartPie />
                    Übersicht
                </Button>
                <Button class="flex-1" variant="outline" size="sm">
                    <CalendarDays />
                    Abwesenheiten
                </Button>
            </div>
            <div class="flex gap-2 bg-muted p-2">
                <Button
                    :as="Link"
                    :href="route('menubar.storeWork')"
                    method="POST"
                    preserve-scroll
                    preserve-state
                    v-if="props.currentType === null"
                    class="flex-1 shrink-0"
                    size="lg"
                >
                    <Play />
                    Starten
                </Button>
                <Button
                    :as="Link"
                    :href="route('menubar.storeStop')"
                    method="POST"
                    preserve-scroll
                    preserve-state
                    v-if="props.currentType !== null"
                    class="flex-1 shrink-0"
                    size="lg"
                    variant="destructive"
                >
                    <Square />
                    Stoppen
                </Button>
                <Button
                    :as="Link"
                    :href="route('menubar.storeWork')"
                    method="POST"
                    preserve-scroll
                    preserve-state
                    v-if="props.currentType === 'break'"
                    class="flex-1 shrink-0"
                    size="lg"
                >
                    <Play />
                    Weiter
                </Button>
                <Button
                    :as="Link"
                    :href="route('menubar.storeBreak')"
                    method="POST"
                    preserve-scroll
                    preserve-state
                    v-if="props.currentType === 'work'"
                    class="flex-1 shrink-0"
                    size="lg"
                    variant="outline"
                >
                    <Coffee />
                    Pause
                </Button>
            </div>
        </div>
    </div>
</template>
