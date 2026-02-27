<script lang="ts" setup>
import { cn } from '@/lib/utils'

import { Button, buttonVariants } from '@/Components/ui/button'
import { Popover, PopoverAnchor, PopoverContent, PopoverTrigger } from '@/Components/ui/popover'

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
import { type DateValue, getLocalTimeZone, isEqualMonth, parseDate, today } from '@internationalized/date'
import { Calendar, ChevronLeft, ChevronRight, X } from 'lucide-vue-next'
import { type DateRange, RangeCalendarRoot, useDateFormatter } from 'reka-ui'
import { createMonth, type Grid, toDate } from 'reka-ui/date'
import { computed, type HTMLAttributes, type Ref, ref, watch } from 'vue'

type ExternalDateRange = {
    start: string
    end: string
}

const externalValue = defineModel<ExternalDateRange | undefined>()

const props = defineProps<{
    min?: string
    max?: string
    class?: HTMLAttributes['class']
    clearable?: boolean
    placeholder?: string
}>()

const minDate = computed(() => (props.min ? parseDate(props.min) : undefined))
const maxDate = computed(() => (props.max ? parseDate(props.max) : undefined))

const parseExternalDate = (date?: string) => {
    if (!date) {
        return undefined
    }

    try {
        return parseDate(date)
    } catch {
        return undefined
    }
}

const toDateString = (date?: DateValue) => {
    return date?.toString()
}

const open = ref(false)
const value = ref({
    start: parseExternalDate(externalValue.value?.start),
    end: parseExternalDate(externalValue.value?.end)
}) as Ref<DateRange>

const updateInternalValue = () => {
    const nextStart = parseExternalDate(externalValue.value?.start)
    const nextEnd = parseExternalDate(externalValue.value?.end)

    if (
        toDateString(value.value.start) === toDateString(nextStart) &&
        toDateString(value.value.end) === toDateString(nextEnd)
    ) {
        return
    }

    value.value = {
        start: nextStart,
        end: nextEnd
    }
}

watch(() => [externalValue.value?.start, externalValue.value?.end], updateInternalValue)

watch(value, () => {
    if (value.value.start !== undefined && value.value.end !== undefined) {
        const nextExternalValue = {
            start: value.value.start.toString(),
            end: value.value.end.toString()
        }

        if (
            externalValue.value?.start !== nextExternalValue.start ||
            externalValue.value?.end !== nextExternalValue.end
        ) {
            externalValue.value = nextExternalValue
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

const initialPlaceholder = (value.value.start ?? parseExternalDate(props.min) ?? today(getLocalTimeZone())) as DateValue
const calendarPlaceholder = ref(initialPlaceholder) as Ref<DateValue>
const secondMonthPlaceholder = ref(value.value.end ?? initialPlaceholder.add({ months: 1 })) as Ref<DateValue>

if (isEqualMonth(secondMonthPlaceholder.value, calendarPlaceholder.value)) {
    secondMonthPlaceholder.value = secondMonthPlaceholder.value.add({
        months: 1
    })
}

const firstMonth = ref(
    createMonth({
        dateObj: calendarPlaceholder.value,
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
        calendarPlaceholder.value = calendarPlaceholder.value.add({ months })
    } else {
        secondMonthPlaceholder.value = secondMonthPlaceholder.value.add({
            months
        })
    }
}

const clearDateRange = () => {
    value.value = {
        start: undefined,
        end: undefined
    }
    externalValue.value = undefined
    open.value = false
}

watch(calendarPlaceholder, (_placeholder) => {
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
    if (isEqualMonth(_secondMonthPlaceholder, calendarPlaceholder.value))
        calendarPlaceholder.value = calendarPlaceholder.value.subtract({ months: 1 })
})
</script>

<template>
    <Popover :open="open" @update:open="open = $event">
        <PopoverAnchor as-child>
            <div :class="cn('relative inline-block', props.class)">
                <PopoverTrigger as-child>
                    <Button
                        :class="
                            cn(
                                'w-full min-w-[250px] justify-start text-left font-normal',
                                !value.start && 'text-muted-foreground',
                                props.clearable && (value.start || value.end) && 'pr-10'
                            )
                        "
                        variant="outline"
                    >
                        <Calendar class="mr-2 h-4 w-4" />
                        <span class="min-w-0 flex-1 truncate">
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
                            <template v-else>{{ props.placeholder ?? 'Pick a date' }}</template>
                        </span>
                    </Button>
                </PopoverTrigger>
                <Button
                    v-if="props.clearable && (value.start || value.end)"
                    @click.stop.prevent="clearDateRange"
                    class="absolute top-1/2 right-1 z-10 h-7 w-7 -translate-y-1/2"
                    size="icon"
                    variant="ghost"
                >
                    <X class="size-4" />
                </Button>
            </div>
        </PopoverAnchor>
        <PopoverContent @pointerDownOutside="open = false" class="w-auto p-0">
            <RangeCalendarRoot
                :locale="$page.props.js_locale"
                :max-value="maxDate"
                :min-value="minDate"
                class="p-3"
                v-model="value"
                v-model:placeholder="calendarPlaceholder"
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
