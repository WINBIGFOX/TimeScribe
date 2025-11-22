<script lang="ts" setup>
import { Badge } from '@/Components/ui/badge'
import { Button } from '@/Components/ui/button'
import { VacationEntry } from '@/types'
import { router } from '@inertiajs/vue3'
import { Check, Hourglass, Trash } from 'lucide-vue-next'
import moment from 'moment/min/moment-with-locales'

const props = defineProps<{
    vacationEntry: VacationEntry
}>()

const destroy = () => {
    router.delete(
        route('absence.vacation.destroy', {
            absence: props.vacationEntry.id
        }),
        {
            data: {
                confirm: false
            },
            preserveScroll: true,
            preserveState: 'errors'
        }
    )
}
</script>

<template>
    <div class="bg-sidebar flex items-center gap-4 rounded-lg p-2.5">
        <div
            :class="{
                'text-primary-foreground bg-primary': props.vacationEntry.status === 'taken',
                'bg-muted text-muted-foreground': props.vacationEntry.status === 'planned'
            }"
            class="flex size-8 shrink-0 items-center justify-center rounded-md"
        >
            <Check class="size-5" v-if="props.vacationEntry.status === 'taken'" />
            <Hourglass class="size-5" v-else />
        </div>

        <div class="ml-2 flex min-w-16 flex-1 shrink-0 items-center gap-2 leading-none font-medium tabular-nums">
            {{ moment(props.vacationEntry.date.formatted, 'DD.MM.YYYY').format('L') }}
        </div>
        <div>
            <Badge
                :variant="props.vacationEntry.status === 'taken' ? 'default' : 'outline'"
                :class="{
                    'text-background! bg-emerald-500!': props.vacationEntry.status === 'taken'
                }"
                class="w-fit"
            >
                {{ props.vacationEntry.status === 'taken' ? $t('app.taken') : $t('app.planned') }}
            </Badge>
        </div>
        <div class="flex flex-col gap-1">
            <span class="text-muted-foreground text-xs leading-none">
                {{ $t('app.hours') }}
            </span>
            <span class="line-clamp-1 leading-none font-medium">
                {{ props.vacationEntry.hours }}
            </span>
        </div>
        <div class="flex flex-col gap-1">
            <span class="text-muted-foreground text-xs leading-none">
                {{ $t('app.day equivalent') }}
            </span>
            <span class="line-clamp-1 leading-none font-medium">
                {{ props.vacationEntry.day_equivalent }}
            </span>
        </div>
        <div class="ml-auto flex items-center justify-end">
            <!--
            <Button
                :as="Link"
                href=""
                class="text-muted-foreground size-8"
                preserve-scroll
                preserve-state
                size="icon"
                variant="ghost"
            >
                <Pencil />
            </Button>-->
            <Button
                @click="destroy"
                class="text-destructive hover:bg-destructive hover:text-destructive-foreground size-8"
                size="icon"
                variant="ghost"
            >
                <Trash />
            </Button>
        </div>
    </div>
</template>
