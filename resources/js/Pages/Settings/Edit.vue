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
    LockKeyhole,
    TimerReset,
} from 'lucide-vue-next';
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

useColorMode();
</script>

<template>
    <Head title="Stempeluhr" />
    <div
        class="sticky top-0 z-10 flex h-10 shrink-0 items-center justify-center font-medium backdrop-blur-sm"
        style="-webkit-app-region: drag"
    >
        Einstellungen
    </div>
    <div class="p-2 select-none">
        <Tabs default-value="general">
            <div class="text-center">
                <TabsList>
                    <TabsTrigger value="general">Allgemein</TabsTrigger>
                    <TabsTrigger value="workingplan">Arbeitsplan</TabsTrigger>
                    <TabsTrigger value="startStop">
                        Start/Pause Automatik
                    </TabsTrigger>
                </TabsList>
            </div>

            <TabsContent value="general" class="space-y-4 p-2">
                <div class="flex items-center space-x-4 rounded-md border p-4">
                    <KeyRound />
                    <div class="flex-1 space-y-1">
                        <p class="text-sm leading-none font-medium">
                            Bei Anmeldung starten
                        </p>
                    </div>
                    <Switch v-model:checked="form.startOnLogin" disabled />
                </div>
                <div class="flex items-center space-x-4 rounded-md border p-4">
                    <Eye />
                    <div class="flex-1 space-y-1">
                        <p class="text-sm leading-none font-medium">
                            Timer automatisch einblenden
                        </p>
                        <p class="text-muted-foreground text-sm">
                            Wenn der Rechner entsperrt wird, kann der Timer
                            eingeblendet werden.
                        </p>
                    </div>
                    <Switch v-model:checked="form.showTimerOnUnlock" />
                </div>

                <div class="flex items-start space-x-4 rounded-md border p-4">
                    <CalendarMinus />
                    <div class="flex-1 space-y-1">
                        <p class="text-sm leading-none font-medium">
                            Feiertage berücksichtigen
                        </p>
                        <p class="text-muted-foreground text-sm">
                            Die Arbeitszeit wird an Feiertagen voll angerechnet.
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
                                        Niedersachsen
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
                            Wochenarbeitszeit
                        </p>
                    </div>
                    {{ weekWorkTime }} Stunden
                </div>
                <div class="flex flex-col gap-2 rounded-md border p-4">
                    <WorkdayTimeInput
                        workday="Montag"
                        v-model="form.workdays.monday"
                    />
                    <WorkdayTimeInput
                        workday="Dienstag"
                        v-model="form.workdays.tuesday"
                    />
                    <WorkdayTimeInput
                        workday="Mittwoch"
                        v-model="form.workdays.wednesday"
                    />
                    <WorkdayTimeInput
                        workday="Donnerstag"
                        v-model="form.workdays.thursday"
                    />
                    <WorkdayTimeInput
                        workday="Freitag"
                        v-model="form.workdays.friday"
                    />
                    <WorkdayTimeInput
                        workday="Samstag"
                        v-model="form.workdays.saturday"
                    />
                    <WorkdayTimeInput
                        workday="Sonntag"
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
                                Stop/Pause-Automatik
                            </p>
                            <p class="text-muted-foreground text-sm">
                                Wenn der Rechner gesperrt wird, kann die
                                Arbeitszeit automatisch gestoppt oder die Pause
                                gestartet werden.
                            </p>
                            <div class="mt-4" v-if="stopBreakAutomatikCheck">
                                <Select v-model="form.stopBreakAutomatic">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Aktion" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="stop">
                                            Arbeitszeit stoppen
                                        </SelectItem>
                                        <SelectItem value="break">
                                            Pause starten
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
                                Zeitbedingt aktivieren
                            </p>
                            <p class="text-muted-foreground text-sm">
                                Die Start/Pause-Automatik wird erst ab einer
                                bestimmten Uhrzeit aktiv.
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
                                        <SelectValue placeholder="Uhrzeit" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            :key="hour"
                                            :value="`${hour + 12}`"
                                            v-for="hour in 11"
                                        >
                                            ab {{ hour + 12 }}:00 Uhr
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
                                Bis 05:00 Uhr des Folgetages ist die Automatik
                                aktiv.
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
                                Vergessener Stop
                            </p>
                            <p class="text-muted-foreground text-sm">
                                Wenn du vergisst, die Arbeits- oder Pausenzeit
                                zu stoppen, wird sie automatisch rückwirkend
                                gestoppt.
                            </p>
                            <p
                                class="text-muted-foreground my-2 text-xs italic"
                            >
                                Bei einer Abwesenheit von mehr als die
                                eingestellte Zeit wird die Arbeits- oder
                                Pausenzeit rückwirkend gestoppt.
                            </p>
                            <div class="mt-4" v-if="stopTimeResetCheck">
                                <p class="mb-2 text-sm leading-none">
                                    Arbeitszeit stoppen nach:
                                </p>
                                <Select v-model="form.stopWorkTimeReset">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Zeit" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="0">Nie</SelectItem>
                                        <SelectItem value="5">
                                            5 Minuten
                                        </SelectItem>
                                        <SelectItem value="10">
                                            10 Minuten
                                        </SelectItem>
                                        <SelectItem value="20">
                                            20 Minuten
                                        </SelectItem>
                                        <SelectItem value="30">
                                            30 Minuten
                                        </SelectItem>
                                        <SelectItem value="40">
                                            40 Minuten
                                        </SelectItem>
                                        <SelectItem value="50">
                                            50 Minuten
                                        </SelectItem>
                                        <SelectItem value="60">
                                            1:00 Stunde
                                        </SelectItem>
                                        <SelectItem value="90">
                                            1:30 Stunde
                                        </SelectItem>
                                        <SelectItem value="120">
                                            2:00 Stunden
                                        </SelectItem>
                                        <SelectItem value="150">
                                            2:30 Stunden
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="mt-4" v-if="stopTimeResetCheck">
                                <p class="mb-2 text-sm leading-none">
                                    Pausenzeit stoppen nach:
                                </p>
                                <Select v-model="form.stopBreakTimeReset">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Zeit" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="0">Nie</SelectItem>
                                        <SelectItem value="5">
                                            5 Minuten
                                        </SelectItem>
                                        <SelectItem value="10">
                                            10 Minuten
                                        </SelectItem>
                                        <SelectItem value="20">
                                            20 Minuten
                                        </SelectItem>
                                        <SelectItem value="30">
                                            30 Minuten
                                        </SelectItem>
                                        <SelectItem value="40">
                                            40 Minuten
                                        </SelectItem>
                                        <SelectItem value="50">
                                            50 Minuten
                                        </SelectItem>
                                        <SelectItem value="60">
                                            1:00 Stunde
                                        </SelectItem>
                                        <SelectItem value="90">
                                            1:30 Stunde
                                        </SelectItem>
                                        <SelectItem value="120">
                                            2:00 Stunden
                                        </SelectItem>
                                        <SelectItem value="150">
                                            2:30 Stunden
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
