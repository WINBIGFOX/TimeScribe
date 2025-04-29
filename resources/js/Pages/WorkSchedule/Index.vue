<script lang="ts" setup>
import { Button } from '@/Components/ui/button'
import { weekdayTranslate } from '@/lib/utils'
import { WorkSchedule } from '@/types'
import { Head, Link } from '@inertiajs/vue3'
import { CalendarPlus, Pen } from 'lucide-vue-next'
import moment from 'moment/min/moment-with-locales'

const props = defineProps<{
    workSchedules: WorkSchedule[]
}>()
</script>

<template>
    <Head title="Work Schedule" />

    <div class="mb-4 flex items-center justify-between gap-4">
        <div class="text-foreground/80 text-base font-medium">{{ $t('app.work schedule') }}</div>
        <div>
            <Button
                :as="Link"
                :href="route('work-schedule.create')"
                prefetch
                preserve-scroll
                preserve-state
                size="sm"
                variant="outline"
            >
                <CalendarPlus />
                {{ $t('app.create new work schedule') }}
            </Button>
        </div>
    </div>
    <div class="flex grow flex-col overflow-hidden">
        <div class="border-border text-foreground grid h-8 grid-cols-8 gap-2 border-b">
            <div :key="index" class="px-2" v-for="(weekday, index) in moment.weekdaysMin(true)">{{ weekday }}</div>
            <div></div>
        </div>
        <div class="grow overflow-y-auto pb-4" scroll-region>
            <div
                :class="{
                    'text-muted-foreground': !workSchedule.is_current
                }"
                :key="workSchedule.id"
                class="hover:bg-sidebar border-muted grid grid-cols-8 gap-2 border-b py-2"
                v-for="workSchedule in props.workSchedules"
            >
                <div
                    class="border-primary text-primary col-span-8 mx-2 mt-1 -mb-2 border-l-2 p-0 pl-1 text-left text-xs leading-none italic"
                    v-if="workSchedule.is_current"
                >
                    {{ $t('app.current work schedule') }}
                </div>
                <div
                    :key="index"
                    class="flex items-center gap-1 px-2"
                    v-for="(weekday, index) in moment.weekdays(true)"
                >
                    <template v-if="workSchedule[weekdayTranslate(weekday).toLowerCase()] > 0">
                        {{ workSchedule[weekdayTranslate(weekday).toLowerCase()].toLocaleString($page.props.js_locale) }}
                        <span class="text-muted-foreground text-xs">{{ $t('app.h') }}</span>
                    </template>
                    <span v-else>-</span>
                </div>
                <div class="px-2 text-right">
                    <Button
                        :as="Link"
                        :href="route('work-schedule.edit', { work_schedule: workSchedule.id })"
                        class="size-8"
                        prefetch
                        preserve-scroll
                        preserve-state
                        size="icon"
                        variant="outline"
                    >
                        <Pen />
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>
