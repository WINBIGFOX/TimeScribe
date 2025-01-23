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
    CalendarClock,
    CalendarMinus,
    KeyRound,
    LockKeyhole,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    startOnLogin?: boolean;
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
}>();

const form = useForm({
    startOnLogin: props.startOnLogin ?? false,
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

const debouncedSubmit = useDebounceFn(submit, 500);
watch(form, debouncedSubmit);
watch(holidayCheck, () => {
    if (holidayCheck.value === false) {
        form.holidayRegion = '';
    }
});

useColorMode();
</script>

<template>
    <Head title="Stempeluhr" />
    <div
        class="sticky top-0 flex h-10 items-center justify-center font-medium backdrop-blur"
        style="-webkit-app-region: drag"
    >
        Einstellungen
    </div>
    <div class="select-none p-2">
        <Tabs default-value="general">
            <div class="text-center">
                <TabsList>
                    <TabsTrigger value="general">Allgemein</TabsTrigger>
                    <TabsTrigger value="workingplan">Arbeitsplan</TabsTrigger>
                </TabsList>
            </div>

            <TabsContent value="general" class="space-y-4 p-2">
                <div class="flex items-center space-x-4 rounded-md border p-4">
                    <KeyRound />
                    <div class="flex-1 space-y-1">
                        <p class="text-sm font-medium leading-none">
                            Bei Anmeldung starten
                        </p>
                    </div>
                    <Switch v-model:checked="form.startOnLogin" />
                </div>
                <div class="flex items-center space-x-4 rounded-md border p-4">
                    <LockKeyhole />
                    <div class="flex-1 space-y-2">
                        <p class="text-sm font-medium leading-none">
                            Stempeluhr wenn Computer gesperrt wird
                        </p>
                        <div class="mt-4">
                            <Select>
                                <SelectTrigger>
                                    <SelectValue placeholder="Aktion" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="apple">
                                        Nichts
                                    </SelectItem>
                                    <SelectItem value="banana">
                                        Stoppen
                                    </SelectItem>
                                    <SelectItem value="blueberry">
                                        Pause starten
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </div>

                <div class="flex items-center space-x-4 rounded-md border p-4">
                    <CalendarMinus />
                    <div class="flex-1 space-y-2">
                        <p class="text-sm font-medium leading-none">
                            Feiertage berücksichtigen
                        </p>
                        <div class="mt-4" v-if="holidayCheck">
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
                        <p class="text-sm font-medium leading-none">
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
        </Tabs>
    </div>
</template>
