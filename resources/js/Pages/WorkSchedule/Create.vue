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
import { DateFormatter, getLocalTimeZone, parseDate, today, type DateValue } from '@internationalized/date'
import { CalendarIcon } from 'lucide-vue-next'
import moment from 'moment/min/moment-with-locales'
import { ref } from 'vue'

defineOptions({
    layout: BasicLayout
})

const value = ref<DateValue | undefined>()
const page = usePage()
const props = defineProps<{
    submit_route: string
}>()

const df = new DateFormatter(page.props.js_locale, {
    dateStyle: 'long'
})

const submit = () => {
    router.flushAll()
    form.transform((data) => {
        if (value.value) {
            data.valid_from = value.value.toString() + ' 00:00:00'
        }
        return data
    }).post(props.submit_route, {
        preserveScroll: true,
        preserveState: true
    })
}

const form = useForm({
    monday: 0,
    tuesday: 0,
    wednesday: 0,
    thursday: 0,
    friday: 0,
    saturday: 0,
    sunday: 0,
    valid_from: today(getLocalTimeZone()).toString()
})
value.value = parseDate(form.valid_from)

const weekWorkTime = computed(() => {
    return form.monday + form.tuesday + form.wednesday + form.thursday + form.friday + form.saturday + form.sunday
})
</script>

<template>
    <Head title="Work Schedule Create" />
    <SheetDialog
        :close="$t('app.cancel')"
        :submit="$t('app.save')"
        :title="$t('app.create work schedule')"
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
