<script setup lang="ts">
import { Button } from '@/Components/ui/button';
import WorkdayTimeInput from '@/Components/WorkdayTimeInput.vue';
import { useForm } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { ArrowRight, CalendarClock } from 'lucide-vue-next';
import { computed, watch } from 'vue';

const props = defineProps<{
    workdays: {
        monday?: number;
        tuesday?: number;
        wednesday?: number;
        thursday?: number;
        friday?: number;
        saturday?: number;
        sunday?: number;
    };
}>();

const form = useForm({
    workdays: {
        monday: props.workdays?.monday ?? 0,
        tuesday: props.workdays?.tuesday ?? 0,
        wednesday: props.workdays?.wednesday ?? 0,
        thursday: props.workdays?.thursday ?? 0,
        friday: props.workdays?.friday ?? 0,
        saturday: props.workdays?.saturday ?? 0,
        sunday: props.workdays?.sunday ?? 0,
    },
});

const submit = () => {
    form.patch(route('welcome.update'), {
        preserveScroll: true,
        preserveState: true,
    });
};
const debouncedSubmit = useDebounceFn(submit, 500);
watch(form, debouncedSubmit);

const weekWorkTime = computed(() => {
    return Object.values(form.workdays).reduce(
        (acc, curr) => (isNaN(curr) ? 0 : curr) + acc,
        0,
    );
});
</script>

<template>
    <div class="flex flex-col space-y-6">
        <div class="flex flex-col text-center font-bold text-white">
            <span class="font-lobster-two text-4xl italic">
                {{ $t('app.set up your weekly schedule') }}
            </span>
            <span>
                {{ $t('app.enter your target working hours for each weekday') }}
            </span>
        </div>
        <div
            class="mb-0 flex w-96 items-center space-x-4 rounded-xl rounded-b-none border p-4 py-2"
        >
            <CalendarClock />
            <div class="flex-1 space-y-1">
                <p class="text-sm leading-none font-medium">
                    {{ $t('app.weekly work hours') }}
                </p>
            </div>
            {{ weekWorkTime.toLocaleString($page.props.locale) }}
            {{ $t('app.hours') }}
        </div>
        <div
            class="bg-background text-foreground flex w-96 flex-col gap-1 rounded-xl rounded-t-none px-4 py-3"
        >
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
        <div class="flex justify-between">
            <Button variant="ghost" size="lg" @click="$emit('prevStep')">
                {{ $t('app.back') }}
            </Button>
            <Button
                v-if="weekWorkTime > 0"
                variant="secondary"
                size="lg"
                @click="$emit('nextStep')"
            >
                {{ $t('app.next') }}
                <ArrowRight />
            </Button>
        </div>
    </div>
</template>
