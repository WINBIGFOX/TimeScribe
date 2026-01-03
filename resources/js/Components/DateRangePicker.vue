<script lang="ts" setup>
import { cn } from '@/lib/utils'

import { Button, buttonVariants } from '@/Components/ui/button'
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover'

import {
    RangeCalendarCell,
    RangeCalendarCellTrigger,
    RangeCalendarGrid,
    RangeCalendarGridBody,
    RangeCalendarGridHead,
    RangeCalendarGridRow,
    RangeCalendarHeadCell
} from '@/Components/ui/range-calendar'
import { usePage } from '@inertiajs/vue3'
import { type DateValue, isEqualMonth, parseDate } from '@internationalized/date'
import { Calendar, ChevronLeft, ChevronRight } from 'lucide-vue-next'
import { type DateRange, RangeCalendarRoot, useDateFormatter } from 'reka-ui'
import { createMonth, type Grid, toDate } from 'reka-ui/date'
import { computed, type Ref, ref, watch } from 'vue'

const externalValue = defineModel<{
    start: string
    end: string
}>()

const props = defineProps<{
    min?: string
    max?: string
}>()

const minDate = computed(() => (props.min ? parseDate(props.min) : undefined))
const maxDate = computed(() => (props.max ? parseDate(props.max) : undefined))

const open = ref()
const value = ref({
    start: parseDate(externalValue.value?.start || ''),
    end: parseDate(externalValue.value?.end || '')
}) as Ref<DateRange>

const updateInternalValue = () => {
    value.value.start = parseDate(externalValue.value?.start || '')
    value.value.end = parseDate(externalValue.value?.end || '')
}

watch(externalValue, () => {
    updateInternalValue()
})

watch(value, () => {
    if (value.value.start !== undefined && value.value.end !== undefined) {
        externalValue.value = {
            start: value.value.start?.toString() || '',
            end: value.value.end?.toString() || ''
        }
        open.value = false
    }
})

watch(open, () => {
    if (!open.value && (value.value.start === undefined || value.value.end === undefined)) {
        updateInternalValue()
    }
})

const page = usePage()
const locale = ref(page.props.js_locale)
const formatter = useDateFormatter(locale.value)

const placeholder = ref(value.value.start) as Ref<DateValue>
const secondMonthPlaceholder = ref(value.value.end) as Ref<DateValue>

if (isEqualMonth(secondMonthPlaceholder.value, placeholder.value)) {
    secondMonthPlaceholder.value = secondMonthPlaceholder.value.add({
        months: 1
    })
}

const firstMonth = ref(
    createMonth({
        dateObj: placeholder.value,
        locale: locale.value,
        fixedWeeks: true,
        weekStartsOn: 0
    })
) as Ref<Grid<DateValue>>
const secondMonth = ref(
    createMonth({
        dateObj: secondMonthPlaceholder.value,
        locale: locale.value,
        fixedWeeks: true,
        weekStartsOn: 0
    })
) as Ref<Grid<DateValue>>

function updateMonth(reference: 'first' | 'second', months: number) {
    if (reference === 'first') {
        placeholder.value = placeholder.value.add({ months })
    } else {
        secondMonthPlaceholder.value = secondMonthPlaceholder.value.add({
            months
        })
    }
}

watch(placeholder, (_placeholder) => {
    firstMonth.value = createMonth({
        dateObj: _placeholder,
        weekStartsOn: 0,
        fixedWeeks: false,
        locale: locale.value
    })
    if (isEqualMonth(secondMonthPlaceholder.value, _placeholder)) {
        secondMonthPlaceholder.value = secondMonthPlaceholder.value.add({
            months: 1
        })
    }
})

watch(secondMonthPlaceholder, (_secondMonthPlaceholder) => {
    secondMonth.value = createMonth({
        dateObj: _secondMonthPlaceholder,
        weekStartsOn: 0,
        fixedWeeks: false,
        locale: locale.value
    })
    if (isEqualMonth(_secondMonthPlaceholder, placeholder.value))
        placeholder.value = placeholder.value.subtract({ months: 1 })
})
</script>

<template>
    <Popover :open="open" @update:open="open = $event">
        <PopoverTrigger as-child>
            <Button
                :class="cn('min-w-[250px] justify-start text-left font-normal', !value && 'text-muted-foreground')"
                @click="open = !open"
                variant="outline"
            >
                <Calendar class="mr-2 h-4 w-4" />
                <template v-if="value.start">
                    <template v-if="value.end">
                        {{
                            formatter.custom(toDate(value.start), {
                                dateStyle: 'medium'
                            })
                        }}
                        -
                        {{
                            formatter.custom(toDate(value.end), {
                                dateStyle: 'medium'
                            })
                        }}
                    </template>

                    <template v-else>
                        {{
                            formatter.custom(toDate(value.start), {
                                dateStyle: 'medium'
                            })
                        }}
                    </template>
                </template>
                <template v-else>Pick a date</template>
            </Button>
        </PopoverTrigger>
        <PopoverContent @pointerDownOutside="open = false" class="w-auto p-0">
            <RangeCalendarRoot
                :locale="$page.props.js_locale"
                :max-value="maxDate"
                :min-value="minDate"
                class="p-3"
                v-model="value"
                v-model:placeholder="placeholder"
                v-slot="{ weekDays }"
            >
                <div class="mt-4 flex flex-col gap-y-4 sm:flex-row sm:gap-x-4 sm:gap-y-0">
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <button
                                :class="
                                    cn(
                                        buttonVariants({ variant: 'outline' }),
                                        'h-7 w-7 bg-transparent p-0 opacity-50 hover:opacity-100'
                                    )
                                "
                                @click="updateMonth('first', -1)"
                            >
                                <ChevronLeft class="h-4 w-4" />
                            </button>
                            <div :class="cn('text-sm font-medium')">
                                {{ formatter.fullMonthAndYear(toDate(firstMonth.value)) }}
                            </div>
                            <button
                                :class="
                                    cn(
                                        buttonVariants({ variant: 'outline' }),
                                        'h-7 w-7 bg-transparent p-0 opacity-50 hover:opacity-100'
                                    )
                                "
                                @click="updateMonth('first', 1)"
                            >
                                <ChevronRight class="h-4 w-4" />
                            </button>
                        </div>
                        <RangeCalendarGrid>
                            <RangeCalendarGridHead>
                                <RangeCalendarGridRow>
                                    <RangeCalendarHeadCell :key="day" class="w-full" v-for="day in weekDays">
                                        {{ day }}
                                    </RangeCalendarHeadCell>
                                </RangeCalendarGridRow>
                            </RangeCalendarGridHead>
                            <RangeCalendarGridBody>
                                <RangeCalendarGridRow
                                    :key="`weekDate-${index}`"
                                    class="mt-2 w-full"
                                    v-for="(weekDates, index) in firstMonth.rows"
                                >
                                    <RangeCalendarCell
                                        :date="weekDate"
                                        :key="weekDate.toString()"
                                        v-for="weekDate in weekDates"
                                    >
                                        <RangeCalendarCellTrigger :day="weekDate" :month="firstMonth.value" />
                                    </RangeCalendarCell>
                                </RangeCalendarGridRow>
                            </RangeCalendarGridBody>
                        </RangeCalendarGrid>
                    </div>
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <button
                                :class="
                                    cn(
                                        buttonVariants({ variant: 'outline' }),
                                        'h-7 w-7 bg-transparent p-0 opacity-50 hover:opacity-100'
                                    )
                                "
                                @click="updateMonth('second', -1)"
                            >
                                <ChevronLeft class="h-4 w-4" />
                            </button>
                            <div :class="cn('text-sm font-medium')">
                                {{ formatter.fullMonthAndYear(toDate(secondMonth.value)) }}
                            </div>

                            <button
                                :class="
                                    cn(
                                        buttonVariants({ variant: 'outline' }),
                                        'h-7 w-7 bg-transparent p-0 opacity-50 hover:opacity-100'
                                    )
                                "
                                @click="updateMonth('second', 1)"
                            >
                                <ChevronRight class="h-4 w-4" />
                            </button>
                        </div>
                        <RangeCalendarGrid>
                            <RangeCalendarGridHead>
                                <RangeCalendarGridRow>
                                    <RangeCalendarHeadCell :key="day" class="w-full" v-for="day in weekDays">
                                        {{ day }}
                                    </RangeCalendarHeadCell>
                                </RangeCalendarGridRow>
                            </RangeCalendarGridHead>
                            <RangeCalendarGridBody>
                                <RangeCalendarGridRow
                                    :key="`weekDate-${index}`"
                                    class="mt-2 w-full"
                                    v-for="(weekDates, index) in secondMonth.rows"
                                >
                                    <RangeCalendarCell
                                        :date="weekDate"
                                        :key="weekDate.toString()"
                                        v-for="weekDate in weekDates"
                                    >
                                        <RangeCalendarCellTrigger :day="weekDate" :month="secondMonth.value" />
                                    </RangeCalendarCell>
                                </RangeCalendarGridRow>
                            </RangeCalendarGridBody>
                        </RangeCalendarGrid>
                    </div>
                </div>
            </RangeCalendarRoot>
        </PopoverContent>
    </Popover>
</template>
