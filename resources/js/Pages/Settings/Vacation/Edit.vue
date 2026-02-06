<script lang="ts" setup>
import { PageHeader } from '@/Components/ui-custom/page-header'
import {
    NumberField,
    NumberFieldContent,
    NumberFieldDecrement,
    NumberFieldIncrement,
    NumberFieldInput
} from '@/Components/ui/number-field'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import { Switch } from '@/Components/ui/switch'
import { Head, router, useForm } from '@inertiajs/vue3'
import { useDebounceFn } from '@vueuse/core'
import { CalendarSync, CircleDivide, RedoDot, Timer } from 'lucide-vue-next'
import { ref, watch } from 'vue'

const props = defineProps<{
    defaultEntitlementDays: number
    prorationStep?: number
    autoCarryover: boolean
    minimumDayHours: number
}>()

const form = useForm({
    default_entitlement_days: props.defaultEntitlementDays,
    proration_step: props.prorationStep,
    auto_carryover: props.autoCarryover,
    prorate_consumption: props.prorationStep !== null,
    minimum_day_hours: props.minimumDayHours
})

const prorateConsumption = ref(props.prorationStep !== null)

const submit = () => {
    router.flushAll()
    form.patch(route('settings.vacation.update'), {
        preserveState: true,
        preserveScroll: true
    })
}

const debouncedSubmit = useDebounceFn(submit, 500)

watch(prorateConsumption, () => {
    if (prorateConsumption.value === false) {
        form.prorate_consumption = false
        form.proration_step = undefined
    } else {
        form.prorate_consumption = true
        form.proration_step = props.prorationStep ?? 0.5
    }
})

watch(
    () => [
        form.default_entitlement_days,
        form.proration_step,
        form.auto_carryover,
        form.prorate_consumption,
        form.minimum_day_hours
    ],
    () => {
        debouncedSubmit()
    },
    { deep: true }
)
</script>

<template>
    <Head title="Settings - Vacation" />
    <PageHeader :title="$t('app.vacation settings')" />
    <div>
        <div class="flex items-start space-x-4 py-4">
            <CalendarSync />
            <div class="flex-1 space-y-1">
                <p class="text-sm leading-none font-medium">
                    {{ $t('app.default annual entitlement') }}
                </p>
                <p class="text-muted-foreground text-sm">
                    {{ $t('app.used when no yearly override exists in the planner.') }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <NumberField
                    :format-options="{
                        style: 'decimal',
                        minimumFractionDigits: 0
                    }"
                    :locale="$page.props.js_locale"
                    :min="0"
                    :step="0.25"
                    class="w-24"
                    v-model.lazy="form.default_entitlement_days"
                >
                    <NumberFieldContent>
                        <NumberFieldDecrement />
                        <NumberFieldInput />
                        <NumberFieldIncrement />
                    </NumberFieldContent>
                </NumberField>
                {{ form.default_entitlement_days !== 1 ? $t('app.days') : $t('app.day') }}
            </div>
        </div>

        <div class="flex items-start space-x-4 border-t py-4">
            <Timer />
            <div class="flex-1 space-y-1">
                <p class="text-sm leading-none font-medium">
                    {{ $t('app.minimum hours per vacation day') }}
                </p>
                <p class="text-muted-foreground text-sm">
                    {{ $t('app.uses this baseline when calculating fractional consumption.') }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <NumberField
                    :format-options="{
                        style: 'decimal',
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 2
                    }"
                    :locale="$page.props.js_locale"
                    :min="0.25"
                    :step="0.25"
                    class="w-24"
                    v-model.lazy="form.minimum_day_hours"
                >
                    <NumberFieldContent>
                        <NumberFieldDecrement />
                        <NumberFieldInput />
                        <NumberFieldIncrement />
                    </NumberFieldContent>
                </NumberField>
                {{ $t('app.hours') }}
            </div>
        </div>

        <div class="flex items-start space-x-4 py-4">
            <RedoDot />
            <div class="flex-1 space-y-1">
                <p class="text-sm leading-none font-medium">
                    {{ $t('app.carry remaining days over automatically') }}
                </p>
                <p class="text-muted-foreground text-sm">
                    {{ $t('app.add the unused balance of the previous year if no custom carryover is defined.') }}
                </p>
            </div>
            <Switch class="self-center" v-model="form.auto_carryover" />
        </div>

        <div class="mt-4 flex items-start space-x-4 border-t py-4 pt-8">
            <CircleDivide />
            <div class="flex-1 space-y-1">
                <div class="flex items-center gap-10">
                    <div class="flex-1 space-y-1">
                        <p class="text-sm leading-none font-medium">
                            {{ $t('app.prorate vacation consumption') }}
                        </p>
                        <p class="text-muted-foreground text-sm">
                            {{ $t('app.count vacation time in fractions of a day based on scheduled hours.') }}
                        </p>
                    </div>
                    <Switch v-model="prorateConsumption" />
                </div>

                <div
                    class="border-border/60 mt-4 flex flex-col gap-4 rounded-lg border border-dashed p-4 flex-row items-center justify-between"
                    v-if="prorateConsumption"
                >
                    <div class="flex-1 space-y-1">
                        <p class="text-sm leading-none font-medium">
                            {{ $t('app.proration step') }}
                        </p>
                        <p class="text-muted-foreground text-sm">
                            {{ $t('app.choose how fractional days are rounded.') }}
                        </p>
                    </div>
                    <Select v-model="form.proration_step">
                        <SelectTrigger class="w-60">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem :value="0.25">{{ $t('app.quarter day (0.25)') }}</SelectItem>
                            <SelectItem :value="0.5">{{ $t('app.half day (0.5)') }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <div class="text-destructive text-xs" v-if="form.errors.proration_step">
                        {{ form.errors.proration_step }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
