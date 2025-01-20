<script setup lang="ts">
import { Switch } from '@/Components/ui/switch';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import WorkdayTimeInput from '@/Components/WorkdayTimeInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { useColorMode } from '@vueuse/core';
import { CalendarClock, KeyRound } from 'lucide-vue-next';
import { computed } from 'vue';

const form = useForm({
    startOnLogin: false,
    workdays: {
        monday: 0,
        tuesday: 0,
        wednesday: 0,
        thursday: 0,
        friday: 0,
        saturday: 0,
        sunday: 0,
    },
});

const weekWorkTime = computed(() => {
    return Object.values(form.workdays).reduce((acc, curr) => acc + curr, 0);
});

useColorMode();
</script>

<template>
    <Head title="Stempeluhr" />
    <div
        class="flex h-10 sticky top-0 backdrop-blur items-center justify-center font-medium"
        style="-webkit-app-region: drag"
    >
        Einstellungen
    </div>
    <div class="p-2">
        <Tabs default-value="workingplan">
            <div class="text-center">
                <TabsList>
                    <TabsTrigger value="general">Allgemein</TabsTrigger>
                    <TabsTrigger value="workingplan">Arbeitsplan</TabsTrigger>
                </TabsList>
            </div>

            <TabsContent value="general" class="p-2">
                <div class="flex items-center space-x-4 rounded-md border p-4">
                    <KeyRound />
                    <div class="flex-1 space-y-1">
                        <p class="text-sm font-medium leading-none">
                            Bei Anmeldung starten
                        </p>
                    </div>
                    <Switch />
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
