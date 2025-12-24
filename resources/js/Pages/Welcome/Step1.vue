<script lang="ts" setup>
import { Button } from '@/Components/ui/button'
import WorkdayTimeInput from '@/Components/WorkdayTimeInput.vue'
import { weekdayTranslate } from '@/lib/utils'
import { router, useForm } from '@inertiajs/vue3'
import { useDebounceFn } from '@vueuse/core'
import { ArrowRight, CalendarClock } from 'lucide-vue-next'
import moment from 'moment/min/moment-with-locales'
import { computed, watch } from 'vue'

const props = defineProps<{
    workSchedule?: {
        sunday?: number
        monday?: number
        tuesday?: number
        wednesday?: number
        thursday?: number
        friday?: number
        saturday?: number
    }
    vacationSettings: {
        minimum_day_hours: number
    }
    mode?: 'fixed' | 'flexible'
    trackVacation?: boolean
}>()

const form = useForm({
    workSchedule: {
        sunday: props.workSchedule?.sunday ?? 0,
        monday: props.workSchedule?.monday ?? 0,
        tuesday: props.workSchedule?.tuesday ?? 0,
        wednesday: props.workSchedule?.wednesday ?? 0,
        thursday: props.workSchedule?.thursday ?? 0,
        friday: props.workSchedule?.friday ?? 0,
        saturday: props.workSchedule?.saturday ?? 0
    }
})

const submit = () => {
    router.flushAll()
    form.patch(route('welcome.update'), {
        preserveScroll: true,
        preserveState: true
    })
}
const debouncedSubmit = useDebounceFn(submit, 500)
watch(() => form.workSchedule, debouncedSubmit, { deep: true })

const weekWorkTime = computed(() => {
    return (
        form.workSchedule.sunday +
        form.workSchedule.monday +
        form.workSchedule.tuesday +
        form.workSchedule.wednesday +
        form.workSchedule.thursday +
        form.workSchedule.friday +
        form.workSchedule.saturday
    )
})
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
            class="bg-background text-foreground mx-auto mb-0 flex w-96 items-center space-x-4 rounded-xl rounded-b-none border-b p-4 py-2"
        >
            <CalendarClock />
            <div class="flex-1 space-y-1">
                <p class="text-sm leading-none font-medium">
                    {{ $t('app.weekly work hours') }}
                </p>
            </div>
            {{ weekWorkTime.toLocaleString($page.props.js_locale) }}
            {{ $t('app.hours') }}
        </div>
        <div class="bg-background text-foreground mx-auto flex w-96 flex-col gap-1 rounded-xl rounded-t-none px-4 py-3">
            <WorkdayTimeInput
                :key="index"
                :workday="weekday"
                v-for="(weekday, index) in moment.weekdays(true)"
                v-model="form.workSchedule[weekdayTranslate(weekday).toLowerCase()]"
            />
        </div>
        <div class="flex justify-between">
            <Button @click="$emit('prevStep')" class="dark:text-foreground" size="lg" variant="ghost">
                {{ $t('app.back') }}
            </Button>
            <Button
                @click="$emit('nextStep')"
                class="dark:hidden"
                size="lg"
                v-if="weekWorkTime > 0"
                variant="secondary"
            >
                {{ $t('app.next') }}
                <ArrowRight />
            </Button>
            <Button @click="$emit('nextStep')" class="hidden dark:flex" size="lg" v-if="weekWorkTime > 0">
                {{ $t('app.next') }}
                <ArrowRight />
            </Button>
        </div>
    </div>
</template>
