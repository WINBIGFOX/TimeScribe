<script lang="ts" setup>
import { Button } from '@/Components/ui/button'
import { HoverCard, HoverCardContent, HoverCardTrigger } from '@/Components/ui/hover-card'
import { Switch } from '@/Components/ui/switch'
import { formatMachineDate, getCurrencySymbol, secToFormat } from '@/lib/utils'
import { Project, Timestamp } from '@/types'
import { Link, useForm } from '@inertiajs/vue3'
import { BriefcaseBusiness, CircleCheckBig, ExternalLink, MoveRight, NotepadText, Timer } from '@lucide/vue'
import moment from 'moment/min/moment-with-locales'
import { watch } from 'vue'
import { Tooltip, TooltipTrigger, TooltipProvider, TooltipContent } from '@/Components/ui/tooltip'

const props = defineProps<{
    project: Project
    timestamp: Timestamp
}>()

const form = useForm({
    paid: props.timestamp.paid
})

const billableForm = useForm({
    billable: props.timestamp.billable
})

const updatePaid = (paid: boolean) => {
    form.paid = paid
    form.patch(route('timestamp.update.paid', { timestamp: props.timestamp.id }), {
        preserveState: true,
        preserveScroll: true,
        onError: () => {
            form.paid = props.timestamp.paid
        }
    })
}

const updateBillable = (billable: boolean) => {
    billableForm.billable = billable
    billableForm.patch(route('timestamp.update.billable', { timestamp: props.timestamp.id }), {
        preserveState: true,
        preserveScroll: true,
        onError: () => {
            billableForm.billable = props.timestamp.billable
        }
    })
}

watch(
    () => props.timestamp.paid,
    (paid) => {
        form.paid = paid
    }
)
watch(
    () => props.timestamp.billable,
    (billable) => {
        billableForm.billable = billable
    }
)
</script>

<template>
    <div :data-timestamp-id="props.timestamp.id" class="bg-sidebar flex flex-wrap items-center gap-4 rounded-lg p-2.5">
        <div class="text-primary-foreground bg-primary flex size-8 shrink-0 items-center justify-center rounded-md">
            <BriefcaseBusiness class="size-5" />
        </div>

        <div class="flex min-w-16 shrink-0 items-center gap-2 leading-none font-medium tabular-nums">
            <bdi>
                {{ moment(props.timestamp.started_at.date, 'YYYY-MM-DD').format('L') }}
            </bdi>
            <Button
                :as="Link"
                :href="
                    route('overview.day.show', {
                        date: formatMachineDate(moment(props.timestamp.started_at.date, 'YYYY-MM-DD'))
                    })
                "
                size="sm"
                variant="ghost"
            >
                <ExternalLink />
            </Button>
        </div>
        <div class="flex shrink-0 items-center gap-2">
            <div class="flex min-w-18 flex-col items-center gap-1">
                <span class="text-muted-foreground text-xs leading-none">
                    {{ $t('app.start') }}
                </span>
                <span class="leading-none font-medium">
                    <bdi>
                        {{ moment(props.timestamp.started_at.formatted, 'Hmm').format('LT') }}
                    </bdi>
                </span>
            </div>
            <MoveRight class="text-muted-foreground size-4 rtl:-scale-x-100" />
            <div class="flex min-w-16 flex-col items-center gap-1" v-if="props.timestamp.ended_at">
                <span class="text-muted-foreground text-xs leading-none">
                    {{ $t('app.end') }}
                </span>
                <span class="leading-none font-medium">
                    <bdi>
                        {{
                            moment((props.timestamp.ended_at ?? props.timestamp.last_ping_at)?.formatted, 'Hmm').format(
                                'LT'
                            )
                        }}
                    </bdi>
                </span>
            </div>
            <div class="bg-muted text-muted-foreground mx-1 flex items-center gap-2 rounded-lg px-3 py-1" v-else>
                <div class="size-3 shrink-0 animate-pulse rounded-full bg-red-500" />
                {{ $t('app.now') }}
            </div>
        </div>
        <div v-if="props.timestamp.description">
            <HoverCard :close-delay="0" :open-delay="0">
                <HoverCardTrigger as-child>
                    <Button
                        :as="Link"
                        :href="route('project.show', { project: props.project.id })"
                        size="sm"
                        variant="outline"
                    >
                        <NotepadText class="size-4" />
                    </Button>
                </HoverCardTrigger>
                <HoverCardContent class="max-h-60 w-auto max-w-md min-w-64 overflow-y-auto">
                    <div class="flex grow flex-col gap-1" v-if="props.timestamp.description">
                        <span class="text-muted-foreground text-xs leading-none">
                            {{ $t('app.notes') }}
                        </span>
                        <span class="whitespace-pre-wrap">
                            {{ props.timestamp.description }}
                        </span>
                    </div>
                </HoverCardContent>
            </HoverCard>
        </div>
        <div class="flex-1"></div>
        <div
            class="flex w-24 shrink-0 items-center justify-end gap-1 tabular-nums"
            v-if="Number(props.project.hourly_rate) > 0 && props.project.currency"
        >
            <TooltipProvider>
                <Tooltip>
                    <TooltipTrigger as-child>
                        <span
                            :class="{
                                'text-muted-foreground/30 decoration-foreground line-through': !props.timestamp.billable
                            }"
                            class="*:text-muted-foreground hover:bg-muted ring-muted flex items-center gap-1 rounded-xs leading-none font-medium *:text-xs hover:ring-6"
                            dir="ltr"
                            v-html="
                                (props.timestamp.billable_amount ?? 0)
                                    .toLocaleString($page.props.js_locale, {
                                        style: 'currency',
                                        currency: props.project.currency,
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    })
                                    .replace(
                                        getCurrencySymbol($page.props.js_locale, props.project.currency),
                                        '<span>$&amp;</span>'
                                    )
                                    .replace('&nbsp;', '')
                            "
                        >
                        </span>
                    </TooltipTrigger>
                    <TooltipContent
                        class="bg-muted [&_.fill-primary]:fill-muted [&_.fill-primary]:bg-muted text-foreground"
                    >
                        <div class="flex items-center gap-2">
                            <label :for="`timestamp-billable-${props.timestamp.id}`" class="text-xs">
                                {{ $t('app.billable') }}
                            </label>
                            <Switch
                                :aria-describedby="
                                    props.timestamp.paid ? `timestamp-billable-hint-${props.timestamp.id}` : undefined
                                "
                                :disabled="props.timestamp.paid || form.processing || billableForm.processing"
                                :id="`timestamp-billable-${props.timestamp.id}`"
                                :model-value="billableForm.billable"
                                @update:model-value="updateBillable"
                            />
                        </div>
                        <p
                            :id="`timestamp-billable-hint-${props.timestamp.id}`"
                            class="text-muted-foreground mt-2 basis-full text-xs"
                            v-if="props.timestamp.paid"
                        >
                            {{ $t('app.remove the paid mark first.') }}
                        </p>
                    </TooltipContent>
                </Tooltip>
            </TooltipProvider>
        </div>
        <div class="flex shrink-0 items-center gap-1 tabular-nums" dir="ltr">
            <Timer class="text-muted-foreground size-4" />
            <span class="font-medium">
                {{
                    props.timestamp.duration > 59
                        ? secToFormat(props.timestamp.duration, false, true, true)
                        : props.timestamp.duration.toFixed(0)
                }}
            </span>
            <span class="text-muted-foreground text-xs">
                {{ props.timestamp.duration > 59 ? $t('app.h') : $t('app.s') }}
            </span>
        </div>
        <div
            class="ms-auto flex items-center justify-end gap-4"
            v-if="props.timestamp.type === 'work' && Number(props.project.hourly_rate) > 0 && props.project.currency"
        >
            <Switch
                :aria-label="$t('app.paid')"
                :disabled="
                    !props.timestamp.ended_at || !props.timestamp.billable || form.processing || billableForm.processing
                "
                :model-value="form.paid"
                @update:model-value="updatePaid"
                style="--primary: var(--color-emerald-500)"
            >
                <template #thumb>
                    <CircleCheckBig
                        :class="{
                            'text-emerald-500': form.paid,
                            'text-muted-foreground': !form.paid
                        }"
                        class="m-0.5 size-3"
                    />
                </template>
            </Switch>
        </div>

        <p
            class="text-destructive basis-full text-sm"
            role="alert"
            v-if="billableForm.errors.billable || form.errors.paid"
        >
            {{ billableForm.errors.billable || form.errors.paid }}
        </p>
    </div>
</template>
