<script lang="ts" setup>
import WorkdayTimeInput from '@/Components/WorkdayTimeInput.vue'
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import { CalendarClock } from 'lucide-vue-next'
import { computed } from 'vue'

import { Button } from '@/Components/ui/button'
import { cn, weekdayTranslate } from '@/lib/utils'

import SheetDialog from '@/Components/dialogs/SheetDialog.vue'
import { Calendar } from '@/Components/ui/calendar'
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover'
import BasicLayout from '@/Layouts/BasicLayout.vue'
import { WorkSchedule } from '@/types'
import { DateFormatter, getLocalTimeZone, parseDate, type DateValue } from '@internationalized/date'
import { CalendarIcon } from 'lucide-vue-next'
import moment from 'moment/min/moment-with-locales'
import { ref } from 'vue'

defineOptions({
    layout: BasicLayout
})

const page = usePage()
const value = ref<DateValue>()

const df = new DateFormatter(page.props.js_locale, {
    dateStyle: 'long'
})

const props = defineProps<{
    workSchedule: WorkSchedule
    submit_route: string
    destroy_route: string
}>()

const submit = () => {
    router.flushAll()
    form.transform((data) => {
        if (value.value) {
            data.valid_from = value.value.toString() + ' 00:00:00'
        }
        return data
    }).patch(props.submit_route, {
        preserveScroll: true,
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
    <Head title="Work Schedule Edit" />

    <SheetDialog
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
            {{ weekWorkTime.toLocaleString($page.props.js_locale) }}
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
            <span class="text-sm leading-none font-medium">{{ $t('app.valid from') }}</span>
            <Popover>
                <PopoverTrigger as-child>
                    <Button
                        :class="cn('w-[250px] justify-start text-left font-normal', !value && 'text-muted-foreground')"
                        variant="outline"
                    >
                        <CalendarIcon class="mr-2 h-4 w-4" />
                        {{ value ? df.format(value.toDate(getLocalTimeZone())) : $t('app.pick a date') }}
                    </Button>
                </PopoverTrigger>
                <PopoverContent class="w-auto p-0">
                    <Calendar :locale="$page.props.js_locale" fixed-weeks v-model="value" />
                </PopoverContent>
            </Popover>
        </div>
        <label class="text-destructive text-xs">{{ form.errors.valid_from }}</label>
    </SheetDialog>
</template>
