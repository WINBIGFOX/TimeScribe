<script lang="ts" setup>
import MainDialog from '@/Components/dialogs/MainDialog.vue'
import WorkdayTimeInput from '@/Components/WorkdayTimeInput.vue'
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import { CalendarClock } from 'lucide-vue-next'
import { computed } from 'vue'

import { Button } from '@/Components/ui/button'
import { cn, weekdayTranslate } from '@/lib/utils'

import { Calendar } from '@/Components/ui/calendar'
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover'
import { WorkSchedule } from '@/types'
import { DateFormatter, getLocalTimeZone, parseDate, type DateValue } from '@internationalized/date'
import { CalendarIcon } from 'lucide-vue-next'
import moment from 'moment/min/moment-with-locales'
import { ref } from 'vue'

const page = usePage()
const value = ref<DateValue>()

const df = new DateFormatter(page.props.locale, {
    dateStyle: 'long'
})

const props = defineProps<{
    workSchedule: WorkSchedule
    submit_route: string
    destroy_route: string
}>()

const submit = () => {
    form.transform((data) => {
        if (value.value) {
            data.valid_from = value.value.toString() + ' 00:00:00'
        }
        return data
    }).patch(props.submit_route, {
        preserveScroll: 'errors',
        preserveState: 'errors'
    })
}

const form = useForm({
    monday: props.workSchedule.monday,
    tuesday: props.workSchedule.tuesday,
    wednesday: props.workSchedule.wednesday,
    thursday: props.workSchedule.thursday,
    friday: props.workSchedule.friday,
    saturday: props.workSchedule.saturday,
    sunday: props.workSchedule.sunday,
    valid_from: props.workSchedule.valid_from.date
})

value.value = parseDate(props.workSchedule.valid_from.date)
const weekWorkTime = computed(() => {
    return form.monday + form.tuesday + form.wednesday + form.thursday + form.friday + form.saturday + form.sunday
})

const destroy = () => {
    router.delete(props.destroy_route, {
        data: {
            confirm: false
        },
        preserveScroll: true,
        preserveState: true
    })
}
</script>

<template>
    <Head title="Timestamp" />
    <MainDialog
        :close="$t('app.cancel')"
        :destroy="$t('app.remove')"
        :submit="$t('app.save')"
        :title="$t('app.edit work schedule')"
        @destroy="destroy"
        @submit="submit"
    >
        <div class="flex items-center space-x-4 rounded-t-md border border-b-0 p-4">
            <CalendarClock />
            <div class="flex-1 space-y-1">
                <p class="text-sm leading-none font-medium">
                    {{ $t('app.weekly work hours') }}
                </p>
            </div>
            {{ weekWorkTime.toLocaleString($page.props.locale) }}
            {{ $t('app.hours') }}
        </div>
        <div class="flex flex-col gap-0 rounded-b-md border p-4">
            <WorkdayTimeInput
                :key="index"
                :workday="weekday"
                v-for="(weekday, index) in moment.weekdays(true)"
                v-model="form[weekdayTranslate(weekday).toLowerCase()]"
            />
        </div>
        <div class="mt-4 flex items-center justify-between">
            {{ $t('app.valid from') }}
            <Popover>
                <PopoverTrigger as-child>
                    <Button
                        :class="cn('w-[280px] justify-start text-left font-normal', !value && 'text-muted-foreground')"
                        variant="outline"
                    >
                        <CalendarIcon class="mr-2 h-4 w-4" />
                        {{ value ? df.format(value.toDate(getLocalTimeZone())) : $t('app.pick a date') }}
                    </Button>
                </PopoverTrigger>
                <PopoverContent class="w-auto p-0">
                    <Calendar :locale="$page.props.locale" fixed-weeks v-model="value" />
                </PopoverContent>
            </Popover>
        </div>
        <label class="text-destructive text-xs">{{ form.errors.valid_from }}</label>
    </MainDialog>
</template>
