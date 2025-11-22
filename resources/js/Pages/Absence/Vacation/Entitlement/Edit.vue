<script setup lang="ts">
import SheetDialog from '@/Components/dialogs/SheetDialog.vue'
import {
    NumberField,
    NumberFieldContent,
    NumberFieldDecrement,
    NumberFieldIncrement,
    NumberFieldInput
} from '@/Components/ui/number-field'
import { Switch } from '@/Components/ui/switch'
import type { VacationEntitlement } from '@/types'
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'

const props = defineProps<{
    submit_route: string
    entitlement: VacationEntitlement
}>()

const page = usePage()
const locale = computed(() => page.props.js_locale)

const formatValue = (value: number | null | undefined): string | null => {
    if (value === null || value === undefined) {
        return null
    }

    const fractionDigits = Number.isInteger(value) ? 0 : 2

    return value.toLocaleString(locale.value, {
        minimumFractionDigits: fractionDigits,
        maximumFractionDigits: fractionDigits
    })
}

const formatValueWithUnit = (value: number | null | undefined): string | null => {
    const formatted = formatValue(value)

    if (!formatted || value === null || value === undefined) {
        return null
    }

    return `${formatted}`
}

const defaultEntitlementWithUnit = computed(() => formatValueWithUnit(props.entitlement.defaultDays))
const calculatedCarryoverWithUnit = computed(() => formatValueWithUnit(props.entitlement.calculatedCarryover))

const form = useForm({
    year: props.entitlement.year,
    days: props.entitlement.days ?? null,
    carryover: props.entitlement.carryover ?? null
})

form.defaults({
    year: props.entitlement.year,
    days: props.entitlement.days ?? null,
    carryover: props.entitlement.carryover ?? null
})

const overrideDays = ref(form.days !== null && form.days !== undefined)
const overrideCarryover = ref(
    props.entitlement.autoCarryover && form.carryover !== null && form.carryover !== undefined
)

watch(overrideDays, (enabled) => {
    if (enabled) {
        if (form.days === null) {
            form.days = props.entitlement.defaultDays ?? 0
        }

        return
    }

    form.days = null
    form.clearErrors('days')
})

watch(overrideCarryover, (enabled) => {
    if (enabled) {
        if (form.carryover === null) {
            form.carryover = props.entitlement.calculatedCarryover ?? 0
        }

        return
    }

    form.carryover = null
    form.clearErrors('carryover')
})

watch(
    () => props.entitlement,
    (entitlement) => {
        form.defaults({
            year: entitlement.year,
            days: entitlement.days ?? null,
            carryover: entitlement.carryover ?? null
        })

        form.year = entitlement.year
        form.days = entitlement.days ?? null
        form.carryover = entitlement.carryover ?? null

        overrideDays.value = entitlement.days !== null && entitlement.days !== undefined
        overrideCarryover.value =
            entitlement.autoCarryover && entitlement.carryover !== null && entitlement.carryover !== undefined

        if (!entitlement.autoCarryover) {
            form.carryover = null
            form.clearErrors('carryover')
        }
    },
    { deep: true }
)

const submit = () => {
    router.flushAll()
    form.post(props.submit_route, {
        preserveScroll: true,
        preserveState: true
    })
}
</script>

<template>
    <Head title="Vacation Entitlement edit" />
    <SheetDialog
        :close="$t('app.cancel')"
        :loading="form.processing"
        :submit="$t('app.save')"
        :title="$t('app.edit :item', { item: $t('app.vacation allowance') + ' ' + props.entitlement.year })"
        @submit="submit"
    >
        <div class="space-y-6 py-4">
            <div class="flex flex-col gap-1">
                <span class="text-muted-foreground text-xs tracking-wide uppercase">{{ $t('app.year') }}</span>
                <span class="text-sm font-medium">{{ props.entitlement.year }}</span>
            </div>

            <div class="space-y-4">
                <div class="flex items-start justify-between gap-4">
                    <div class="space-y-1">
                        <span class="text-sm leading-none font-medium">
                            {{
                                $t('app.override standard allowance (:value) for :year', {
                                    year: props.entitlement.year.toString(),
                                    value:
                                        (defaultEntitlementWithUnit ?? '—') +
                                        ' ' +
                                        (props.entitlement.defaultDays !== 1 ? $t('app.days') : $t('app.day'))
                                })
                            }}
                        </span>
                        <p class="text-muted-foreground text-xs">
                            {{ $t('app.used when no yearly override exists in the planner.') }}
                        </p>
                    </div>
                    <Switch v-model="overrideDays" />
                </div>

                <div class="flex flex-col gap-2" v-if="overrideDays">
                    <div class="flex items-center gap-2">
                        <NumberField
                            :format-options="{
                                style: 'decimal',
                                minimumFractionDigits: 0
                            }"
                            :locale="locale"
                            :min="0"
                            :step="0.25"
                            class="w-24"
                            v-model.lazy="form.days"
                        >
                            <NumberFieldContent>
                                <NumberFieldDecrement />
                                <NumberFieldInput />
                                <NumberFieldIncrement />
                            </NumberFieldContent>
                        </NumberField>
                        {{ (form.days ?? props.entitlement.defaultDays ?? 0) !== 1 ? $t('app.days') : $t('app.day') }}
                    </div>
                    <div class="text-destructive text-sm" v-if="form.errors.days">
                        {{ form.errors.days }}
                    </div>
                </div>
            </div>

            <div v-if="props.entitlement.autoCarryover" class="space-y-4">
                <div class="flex items-start justify-between gap-4">
                    <div class="space-y-1">
                        <span class="text-sm leading-none font-medium">
                            {{
                                $t('app.override carryover (:value) for :year', {
                                    year: props.entitlement.year.toString(),
                                    value:
                                        (calculatedCarryoverWithUnit ?? '—') +
                                        ' ' +
                                        (props.entitlement.calculatedCarryover !== 1 ? $t('app.days') : $t('app.day'))
                                })
                            }}
                        </span>
                        <p class="text-muted-foreground text-xs">
                            {{ $t('app.carryover is calculated automatically from the previous year.') }}
                        </p>
                        <p class="text-muted-foreground text-xs" v-if="calculatedCarryoverWithUnit">
                            {{
                                $t('app.calculated carryover: :value', {
                                    value:
                                        calculatedCarryoverWithUnit +
                                        ' ' +
                                        (props.entitlement.calculatedCarryover !== 1 ? $t('app.days') : $t('app.day'))
                                })
                            }}
                        </p>
                    </div>
                    <Switch v-model="overrideCarryover" />
                </div>

                <div class="flex flex-col gap-2" v-if="overrideCarryover">
                    <div class="flex items-center gap-2">
                        <NumberField
                            :format-options="{
                                style: 'decimal',
                                minimumFractionDigits: 0
                            }"
                            :locale="locale"
                            :min="0"
                            :step="0.25"
                            class="w-24"
                            v-model.lazy="form.carryover"
                        >
                            <NumberFieldContent>
                                <NumberFieldDecrement />
                                <NumberFieldInput />
                                <NumberFieldIncrement />
                            </NumberFieldContent>
                        </NumberField>
                        {{ (form.carryover ?? 0) !== 1 ? $t('app.days') : $t('app.day') }}
                    </div>
                    <div class="text-destructive text-sm" v-if="form.errors.carryover">
                        {{ form.errors.carryover }}
                    </div>
                </div>
            </div>
        </div>
    </SheetDialog>
</template>
