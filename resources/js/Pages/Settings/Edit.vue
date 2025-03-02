<script setup lang="ts">
import {
    Select,
    SelectContent,
    SelectItem,
    SelectSeparator,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import { Switch } from '@/Components/ui/switch';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import WorkdayTimeInput from '@/Components/WorkdayTimeInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { useColorMode, useDebounceFn } from '@vueuse/core';
import {
    AlarmClockCheck,
    CalendarClock,
    CalendarMinus,
    Eye,
    KeyRound,
    Languages,
    LockKeyhole,
    SunMoon,
    TimerReset,
} from 'lucide-vue-next';
import moment from 'moment/min/moment-with-locales';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    startOnLogin?: boolean;
    showTimerOnUnlock?: boolean;
    workdays: {
        monday?: number;
        tuesday?: number;
        wednesday?: number;
        thursday?: number;
        friday?: number;
        saturday?: number;
        sunday?: number;
    };
    holidayRegion?: string;
    stopBreakAutomatic?: string;
    stopBreakAutomaticActivationTime?: string;
    stopWorkTimeReset?: number;
    stopBreakTimeReset?: number;
    locale: string;
}>();

const form = useForm({
    startOnLogin: props.startOnLogin ?? false,
    showTimerOnUnlock: props.showTimerOnUnlock ?? false,
    workdays: {
        monday: props.workdays?.monday ?? 0,
        tuesday: props.workdays?.tuesday ?? 0,
        wednesday: props.workdays?.wednesday ?? 0,
        thursday: props.workdays?.thursday ?? 0,
        friday: props.workdays?.friday ?? 0,
        saturday: props.workdays?.saturday ?? 0,
        sunday: props.workdays?.sunday ?? 0,
    },
    holidayRegion: props.holidayRegion ?? '',
    stopBreakAutomatic: props.stopBreakAutomatic ?? '',
    stopBreakAutomaticActivationTime:
        props.stopBreakAutomaticActivationTime ?? '',
    stopWorkTimeReset: props.stopWorkTimeReset?.toString() ?? '0',
    stopBreakTimeReset: props.stopBreakTimeReset?.toString() ?? '0',
    locale: props.locale,
});

const weekWorkTime = computed(() => {
    return Object.values(form.workdays).reduce((acc, curr) => acc + curr, 0);
});

const submit = () => {
    form.patch(route('settings.update'), {
        preserveScroll: true,
        preserveState: true,
    });
};

const holidayCheck = ref(props.holidayRegion !== null);
const stopBreakAutomatikCheck = ref(props.stopBreakAutomatic !== null);
const stopBreakAutomatikActivationCheck = ref(
    props.stopBreakAutomaticActivationTime !== null,
);
const stopTimeResetCheck = ref(
    !!(props.stopWorkTimeReset || props.stopBreakTimeReset),
);

const debouncedSubmit = useDebounceFn(submit, 500);
watch(form, debouncedSubmit);
watch(holidayCheck, () => {
    if (holidayCheck.value === false) {
        form.holidayRegion = '';
    }
});
watch(stopBreakAutomatikCheck, () => {
    if (stopBreakAutomatikCheck.value === false) {
        form.stopBreakAutomatic = '';
        stopBreakAutomatikActivationCheck.value = false;
    }
});
watch(stopBreakAutomatikActivationCheck, () => {
    if (stopBreakAutomatikActivationCheck.value === false) {
        form.stopBreakAutomaticActivationTime = '';
    }
});
watch(stopTimeResetCheck, () => {
    if (stopTimeResetCheck.value === false) {
        form.stopWorkTimeReset = '0';
        form.stopBreakTimeReset = '0';
    }
});

const { store } = useColorMode();
</script>

<template>
    <Head title="Settings" />
    <div
        class="sticky top-0 z-10 flex h-10 shrink-0 items-center justify-center font-medium backdrop-blur-sm"
        style="-webkit-app-region: drag"
    >
        {{ $t('app.settings') }}
    </div>
    <div class="p-2 select-none">
        <Tabs default-value="general">
            <div class="text-center">
                <TabsList>
                    <TabsTrigger value="general">
                        {{ $t('app.general') }}
                    </TabsTrigger>
                    <TabsTrigger value="workingplan">
                        {{ $t('app.work schedule') }}
                    </TabsTrigger>
                    <TabsTrigger value="startStop">
                        {{ $t('app.auto start/break') }}
                    </TabsTrigger>
                </TabsList>
            </div>

            <TabsContent value="general" class="space-y-4 p-2">
                <div class="flex items-center space-x-4 rounded-md border p-4">
                    <KeyRound />
                    <div class="flex-1 space-y-1">
                        <p class="text-sm leading-none font-medium">
                            {{ $t('app.start at login') }}
                        </p>
                    </div>
                    <Switch v-model:checked="form.startOnLogin" disabled />
                </div>
                <div class="flex items-start space-x-4 rounded-md border p-4">
                    <Languages />
                    <div class="flex-1 space-y-1">
                        <p class="text-sm leading-none font-medium">
                            {{ $t('app.language') }}
                        </p>
                        <div class="mt-2">
                            <Select size="5" v-model="form.locale">
                                <SelectTrigger>
                                    <SelectValue
                                        :placeholder="$t('app.language')"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="de-DE">
                                        {{ $t('app.german') }}
                                    </SelectItem>
                                    <SelectItem value="en-GB">
                                        {{ $t('app.english (UK)') }}
                                    </SelectItem>
                                    <SelectItem value="en-US">
                                        {{ $t('app.english (US)') }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </div>
                <div class="flex items-start space-x-4 rounded-md border p-4">
                    <SunMoon />
                    <div class="flex-1 space-y-1">
                        <p class="text-sm leading-none font-medium">
                            {{ $t('app.appearance') }}
                        </p>
                        <p class="text-muted-foreground text-sm text-balance">
                            {{
                                $t(
                                    'app.choose the appearance of the application.',
                                )
                            }}
                        </p>
                        <div class="mt-2">
                            <Select size="5" v-model="store">
                                <SelectTrigger>
                                    <SelectValue
                                        :placeholder="$t('app.appearance')"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="auto">
                                        {{ $t('app.system') }}
                                    </SelectItem>
                                    <SelectItem value="light">
                                        {{ $t('app.light') }}
                                    </SelectItem>
                                    <SelectItem value="dark">
                                        {{ $t('app.dark') }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-4 rounded-md border p-4">
                    <Eye />
                    <div class="flex-1 space-y-1">
                        <p class="text-sm leading-none font-medium">
                            {{ $t('app.show timer automatically') }}
                        </p>
                        <p class="text-muted-foreground text-sm text-balance">
                            {{
                                $t(
                                    'app.when the computer is unlocked, the timer can be displayed.',
                                )
                            }}
                        </p>
                    </div>
                    <Switch v-model:checked="form.showTimerOnUnlock" />
                </div>

                <div class="flex items-start space-x-4 rounded-md border p-4">
                    <CalendarMinus />
                    <div class="flex-1 space-y-1">
                        <p class="text-sm leading-none font-medium">
                            {{ $t('app.consider public holidays') }}
                        </p>
                        <p class="text-muted-foreground text-sm">
                            {{
                                $t(
                                    'app.working hours on public holidays are fully credited.',
                                )
                            }}
                        </p>
                        <div class="mt-2" v-if="holidayCheck">
                            <Select size="5" v-model="form.holidayRegion">
                                <SelectTrigger>
                                    <SelectValue placeholder="Region" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="DE">
                                        Deutschland
                                    </SelectItem>
                                    <SelectSeparator />
                                    <SelectItem value="DE-BB">
                                        Brandenburg
                                    </SelectItem>
                                    <SelectItem value="DE-BE">
                                        Berlin
                                    </SelectItem>
                                    <SelectItem value="DE-BW">
                                        Baden-Württemberg
                                    </SelectItem>
                                    <SelectItem value="DE-BY">
                                        Bayern
                                    </SelectItem>
                                    <SelectItem value="DE-HB">
                                        Bremen
                                    </SelectItem>
                                    <SelectItem value="DE-HE">
                                        Hessen
                                    </SelectItem>
                                    <SelectItem value="DE-HH">
                                        Hamburg
                                    </SelectItem>
                                    <SelectItem value="DE-MV">
                                        Mecklenburg-Vorpommern
                                    </SelectItem>
                                    <SelectItem value="DE-NI">
                                        {{ $t('app.never') }}dersachsen
                                    </SelectItem>
                                    <SelectItem value="DE-NW">
                                        Nordrhein-Westfalen
                                    </SelectItem>
                                    <SelectItem value="DE-RP">
                                        Rheinland-Pfalz
                                    </SelectItem>
                                    <SelectItem value="DE-SH">
                                        Schleswig-Holstein
                                    </SelectItem>
                                    <SelectItem value="DE-SL">
                                        Saarland
                                    </SelectItem>
                                    <SelectItem value="DE-SN">
                                        Sachsen
                                    </SelectItem>
                                    <SelectItem value="DE-ST">
                                        Sachsen-Anhalt
                                    </SelectItem>
                                    <SelectItem value="DE-TH">
                                        Thüringen
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                    <Switch v-model:checked="holidayCheck" />
                </div>
            </TabsContent>
            <TabsContent value="workingplan" class="space-y-4 p-2">
                <div class="flex items-center space-x-4 rounded-md border p-4">
                    <CalendarClock />
                    <div class="flex-1 space-y-1">
                        <p class="text-sm leading-none font-medium">
                            {{ $t('app.weekly work hours') }}
                        </p>
                    </div>
                    {{ weekWorkTime.toLocaleString($page.props.locale) }}
                    {{ $t('app.hours') }}
                </div>
                <div class="flex flex-col gap-2 rounded-md border p-4">
                    <WorkdayTimeInput
                        :workday="$t('app.monday')"
                        v-model="form.workdays.monday"
                    />
                    <WorkdayTimeInput
                        :workday="$t('app.tuesday')"
                        v-model="form.workdays.tuesday"
                    />
                    <WorkdayTimeInput
                        :workday="$t('app.wednesday')"
                        v-model="form.workdays.wednesday"
                    />
                    <WorkdayTimeInput
                        :workday="$t('app.thursday')"
                        v-model="form.workdays.thursday"
                    />
                    <WorkdayTimeInput
                        :workday="$t('app.friday')"
                        v-model="form.workdays.friday"
                    />
                    <WorkdayTimeInput
                        :workday="$t('app.saturday')"
                        v-model="form.workdays.saturday"
                    />
                    <WorkdayTimeInput
                        :workday="$t('app.sunday')"
                        v-model="form.workdays.sunday"
                    />
                </div>
            </TabsContent>

            <TabsContent value="startStop" class="space-y-4 p-2">
                <div class="rounded-md border">
                    <div class="flex items-start space-x-4 p-4">
                        <LockKeyhole />
                        <div class="flex-1 space-y-1">
                            <p class="text-sm leading-none font-medium">
                                {{ $t('app.auto start/break') }}
                            </p>
                            <p class="text-muted-foreground text-sm">
                                {{
                                    $t(
                                        'app.when the computer is locked, the working time can be automatically stopped, or the break can be started.',
                                    )
                                }}
                            </p>
                            <div class="mt-4" v-if="stopBreakAutomatikCheck">
                                <Select v-model="form.stopBreakAutomatic">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Aktion" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="stop">
                                            {{ $t('app.stop working time') }}
                                        </SelectItem>
                                        <SelectItem value="break">
                                            {{ $t('app.start break') }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                        <Switch v-model:checked="stopBreakAutomatikCheck" />
                    </div>
                    <div
                        class="flex items-start space-x-4 border-t p-4"
                        v-if="stopBreakAutomatikCheck"
                    >
                        <AlarmClockCheck />
                        <div class="flex-1 space-y-1">
                            <p class="text-sm leading-none font-medium">
                                {{ $t('app.activate based on time') }}
                            </p>
                            <p class="text-muted-foreground text-sm">
                                {{
                                    $t(
                                        'app.the auto start/break feature will only be activated at a specified time.',
                                    )
                                }}
                            </p>
                            <div
                                class="mt-4"
                                v-if="stopBreakAutomatikActivationCheck"
                            >
                                <Select
                                    v-model="
                                        form.stopBreakAutomaticActivationTime
                                    "
                                >
                                    <SelectTrigger>
                                        <SelectValue
                                            :placeholder="$t('app.time')"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            :key="hour"
                                            :value="`${hour + 12}`"
                                            v-for="hour in 11"
                                        >
                                            {{
                                                $t('app.from :time', {
                                                    time: moment(
                                                        hour + 12,
                                                        'HH',
                                                    ).format('LT'),
                                                })
                                            }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <p
                                class="text-muted-foreground text-xs italic"
                                v-if="
                                    stopBreakAutomatikActivationCheck &&
                                    form.stopBreakAutomaticActivationTime
                                "
                            >
                                {{
                                    $t(
                                        'app.the automatic system is active until :time on the following day.',
                                        {
                                            time: moment(5, 'H').format('LT'),
                                        },
                                    )
                                }}
                            </p>
                        </div>
                        <Switch
                            v-model:checked="stopBreakAutomatikActivationCheck"
                        />
                    </div>
                </div>
                <div class="rounded-md border">
                    <div class="flex items-start space-x-4 p-4">
                        <TimerReset />
                        <div class="flex-1 space-y-1">
                            <p class="text-sm leading-none font-medium">
                                {{ $t('app.forgotten stop') }}
                            </p>
                            <p class="text-muted-foreground text-sm">
                                {{
                                    $t(
                                        'app.if you forget to stop the working or break time, it will be automatically stopped retroactively.',
                                    )
                                }}
                            </p>
                            <p
                                class="text-muted-foreground my-2 text-xs italic"
                            >
                                {{
                                    $t(
                                        'app.if an absence exceeds the configured time, the working or break time will be stopped retroactively.',
                                    )
                                }}
                            </p>
                            <div class="mt-4" v-if="stopTimeResetCheck">
                                <p class="mb-2 text-sm leading-none">
                                    {{ $t('app.stop working time after:') }}
                                </p>
                                <Select v-model="form.stopWorkTimeReset">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Zeit" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="0">
                                            {{ $t('app.never') }}
                                        </SelectItem>
                                        <SelectItem value="5">
                                            5 {{ $t('app.minutes') }}
                                        </SelectItem>
                                        <SelectItem value="10">
                                            10 {{ $t('app.minutes') }}
                                        </SelectItem>
                                        <SelectItem value="20">
                                            20 {{ $t('app.minutes') }}
                                        </SelectItem>
                                        <SelectItem value="30">
                                            30 {{ $t('app.minutes') }}
                                        </SelectItem>
                                        <SelectItem value="40">
                                            40 {{ $t('app.minutes') }}
                                        </SelectItem>
                                        <SelectItem value="50">
                                            50 {{ $t('app.minutes') }}
                                        </SelectItem>
                                        <SelectItem value="60">
                                            1:00 {{ $t('app.hour') }}
                                        </SelectItem>
                                        <SelectItem value="90">
                                            1:30 {{ $t('app.hour') }}
                                        </SelectItem>
                                        <SelectItem value="120">
                                            2:00 {{ $t('app.hours') }}
                                        </SelectItem>
                                        <SelectItem value="150">
                                            2:30 {{ $t('app.hours') }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="mt-4" v-if="stopTimeResetCheck">
                                <p class="mb-2 text-sm leading-none">
                                    {{ $t('app.stop break time after:') }}
                                </p>
                                <Select v-model="form.stopBreakTimeReset">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Zeit" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="0">
                                            {{ $t('app.never') }}
                                        </SelectItem>
                                        <SelectItem value="5">
                                            5 {{ $t('app.minutes') }}
                                        </SelectItem>
                                        <SelectItem value="10">
                                            10 {{ $t('app.minutes') }}
                                        </SelectItem>
                                        <SelectItem value="20">
                                            20 {{ $t('app.minutes') }}
                                        </SelectItem>
                                        <SelectItem value="30">
                                            30 {{ $t('app.minutes') }}
                                        </SelectItem>
                                        <SelectItem value="40">
                                            40 {{ $t('app.minutes') }}
                                        </SelectItem>
                                        <SelectItem value="50">
                                            50 {{ $t('app.minutes') }}
                                        </SelectItem>
                                        <SelectItem value="60">
                                            1:00 {{ $t('app.hour') }}
                                        </SelectItem>
                                        <SelectItem value="90">
                                            1:30 {{ $t('app.hour') }}
                                        </SelectItem>
                                        <SelectItem value="120">
                                            2:00 {{ $t('app.hours') }}
                                        </SelectItem>
                                        <SelectItem value="150">
                                            2:30 {{ $t('app.hours') }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                        <Switch v-model:checked="stopTimeResetCheck" />
                    </div>
                </div>
            </TabsContent>
        </Tabs>
    </div>
</template>
