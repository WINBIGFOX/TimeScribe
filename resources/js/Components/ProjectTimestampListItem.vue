<script lang="ts" setup>
import { Button } from '@/Components/ui/button'
import { Switch } from '@/Components/ui/switch'
import { getCurrencySymbol, secToFormat } from '@/lib/utils'
import { Project, Timestamp } from '@/types'
import { Link, useForm } from '@inertiajs/vue3'
import { BriefcaseBusiness, CircleCheckBig, ExternalLink, Timer } from 'lucide-vue-next'
import moment from 'moment/min/moment-with-locales'
import { watch } from 'vue'

const props = defineProps<{
    project: Project
    timestamp: Timestamp
}>()

const form = useForm({
    paid: props.timestamp.paid
})

const submit = () => {
    form.patch(route('timestamp.update.paid', { timestamp: props.timestamp.id }), {
        preserveState: true,
        preserveScroll: true
    })
}

watch(() => form.paid, submit)
</script>

<template>
    <div class="bg-sidebar flex items-center gap-4 rounded-lg p-2.5">
        <div class="text-primary-foreground bg-primary flex size-8 shrink-0 items-center justify-center rounded-md">
            <BriefcaseBusiness class="size-5" />
        </div>

        <div class="ml-2 flex min-w-16 flex-1 shrink-0 items-center gap-2 leading-none font-medium tabular-nums">
            {{ moment(props.timestamp.started_at.date, 'YYYY-MM-DD').format('L') }}
            <Button
                variant="ghost"
                size="sm"
                :as="Link"
                :href="
                    route('overview.day.show', {
                        date: moment(props.timestamp.started_at.date, 'YYYY-MM-DD').format('YYYY-MM-DD')
                    })
                "
            >
                <ExternalLink />
            </Button>
        </div>
        <div></div>
        <div
            class="flex w-24 shrink-0 items-center justify-end gap-1 tabular-nums"
            v-if="props.timestamp.billable_amount && props.project.currency"
        >
            <span
                class="*:text-muted-foreground flex items-center gap-1 leading-none font-medium *:text-xs"
                v-html="
                    props.timestamp.billable_amount
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
        </div>
        <div class="flex shrink-0 items-center gap-1 tabular-nums">
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
            <Switch
                :disabled="!props.timestamp.ended_at"
                v-model="form.paid"
                style="--primary: var(--color-emerald-500)"
                v-if="props.project.hourly_rate && props.project.currency"
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
    </div>
</template>
