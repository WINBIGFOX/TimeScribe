<script lang="ts" setup>
import { cn } from '@/lib/utils'
import { CalendarRoot, type CalendarRootEmits, type CalendarRootProps, useForwardPropsEmits } from 'reka-ui'
import { computed, type HTMLAttributes } from 'vue'
import {
    CalendarCell,
    CalendarCellTrigger,
    CalendarGrid,
    CalendarGridBody,
    CalendarGridHead,
    CalendarGridRow,
    CalendarHeadCell,
    CalendarHeader,
    CalendarHeading,
    CalendarNextButton,
    CalendarPrevButton
} from '.'

const props = defineProps<CalendarRootProps & { class?: HTMLAttributes['class']; highlighted?: string[] }>()
const emits = defineEmits<CalendarRootEmits>()

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props

    return delegated
})

const forwarded = useForwardPropsEmits(delegatedProps, emits)
</script>

<template>
    <CalendarRoot :class="cn('p-3', props.class)" v-bind="forwarded" v-slot="{ grid, weekDays }" data-slot="calendar">
        <CalendarHeader>
            <CalendarHeading />

            <div class="flex items-center gap-1">
                <CalendarPrevButton />
                <CalendarNextButton />
            </div>
        </CalendarHeader>

        <div class="mt-4 flex flex-col gap-y-4 sm:flex-row sm:gap-x-4 sm:gap-y-0">
            <CalendarGrid :key="month.value.toString()" v-for="month in grid">
                <CalendarGridHead>
                    <CalendarGridRow>
                        <CalendarHeadCell :key="day" v-for="day in weekDays">
                            {{ day }}
                        </CalendarHeadCell>
                    </CalendarGridRow>
                </CalendarGridHead>
                <CalendarGridBody>
                    <CalendarGridRow
                        :key="`weekDate-${index}`"
                        class="mt-2 w-full"
                        v-for="(weekDates, index) in month.rows"
                    >
                        <CalendarCell :date="weekDate" :key="weekDate.toString()" v-for="weekDate in weekDates">
                            <CalendarCellTrigger :day="weekDate" :month="month.value" class="group">
                                <div
                                    class="bg-primary group-data-selected:bg-foreground/50 absolute bottom-1 h-1 w-1/3 rounded-sm"
                                    v-if="props.highlighted?.includes(weekDate.toString())"
                                />
                                {{ weekDate.day }}
                            </CalendarCellTrigger>
                        </CalendarCell>
                    </CalendarGridRow>
                </CalendarGridBody>
            </CalendarGrid>
        </div>
    </CalendarRoot>
</template>
