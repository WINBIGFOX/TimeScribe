<script lang="ts" setup>
import {
    NumberField,
    NumberFieldContent,
    NumberFieldDecrement,
    NumberFieldIncrement,
    NumberFieldInput
} from '@/Components/ui/number-field'
import { Switch } from '@/Components/ui/switch'
import { Button } from '@/Components/ui/button'
import { router, useForm } from '@inertiajs/vue3'
import { useDebounceFn } from '@vueuse/core'
import { ArrowRight, CheckCircle2 } from 'lucide-vue-next'
import { computed, watch } from 'vue'

const props = defineProps<{
    vacationSettings: {
        default_entitlement_days: number
        auto_carryover: boolean
        minimum_day_hours: number
    }
}>()

defineEmits<{
    (e: 'prevStep'): void
    (e: 'nextStep'): void
}>()

const form = useForm({
    vacation: {
        default_entitlement_days: props.vacationSettings.default_entitlement_days,
        auto_carryover: props.vacationSettings.auto_carryover
    }
})

const submit = () => {
    router.flushAll()
    form.patch(route('welcome.update'), {
        preserveScroll: true,
        preserveState: true
    })
}

const debouncedSubmit = useDebounceFn(submit, 300)

watch(
    () => [form.vacation.default_entitlement_days, form.vacation.auto_carryover],
    () => {
        debouncedSubmit()
    },
    { deep: true }
)

const totalEntitlementPreview = computed(() => form.vacation.default_entitlement_days)
</script>

<template>
    <div class="flex flex-col space-y-6">
        <div class="flex flex-col text-center font-bold text-white">
            <span class="font-lobster-two text-4xl italic">
                {{ $t('app.vacation settings') }}
            </span>
            <span class="text-white/80 text-sm">
                {{ $t('app.default annual entitlement') }}
            </span>
        </div>

        <div class="bg-background text-foreground mx-auto flex w-96 flex-col gap-4 rounded-xl p-5 shadow-lg">
            <div class="flex items-center justify-between gap-3 rounded-lg bg-muted/10 p-3">
                <div class="space-y-1">
                    <p class="text-sm font-medium">
                        {{ $t('app.default annual entitlement') }}
                    </p>
                    <p class="text-muted-foreground text-xs">
                        {{ $t('app.used when no yearly override exists in the planner.') }}
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
                        :min="0"
                        :step="0.25"
                        class="w-24"
                        v-model.lazy="form.vacation.default_entitlement_days"
                        @update:model-value="debouncedSubmit"
                    >
                        <NumberFieldContent>
                            <NumberFieldDecrement />
                            <NumberFieldInput />
                            <NumberFieldIncrement />
                        </NumberFieldContent>
                    </NumberField>
                    {{ $t('app.days') }}
                </div>
            </div>

            <div class="flex items-center justify-between gap-3 rounded-lg bg-muted/10 p-3">
                <div class="space-y-1">
                    <p class="text-sm font-medium">
                        {{ $t('app.carry remaining days over automatically') }}
                    </p>
                    <p class="text-muted-foreground text-xs">
                        {{ $t('app.add the unused balance of the previous year if no custom carryover is defined.') }}
                    </p>
                </div>
                <Switch class="self-center" v-model="form.vacation.auto_carryover" @update:model-value="debouncedSubmit" />
            </div>

            <div class="rounded-lg bg-muted/15 p-3">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <CheckCircle2 class="text-emerald-500" />
                        <div class="text-sm font-medium">
                            {{ $t('app.total entitlement') }}
                        </div>
                    </div>
                    <div class="text-lg font-semibold tabular-nums">
                        {{ totalEntitlementPreview.toLocaleString($page.props.js_locale, { maximumFractionDigits: 2 }) }}
                        {{ $t('app.days') }}
                    </div>
                </div>
                <div class="text-muted-foreground mt-2 text-[11px] leading-tight">
                    {{
                        form.vacation.auto_carryover
                            ? $t('app.auto carryover enabled note')
                            : $t('app.auto carryover disabled note')
                    }}
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <Button @click="$emit('prevStep')" class="dark:text-foreground" size="lg" variant="ghost">
                {{ $t('app.back') }}
            </Button>
            <Button
                class="dark:hidden"
                size="lg"
                variant="secondary"
                @click="$emit('nextStep')"
            >
                {{ $t('app.next') }}
                <ArrowRight />
            </Button>
            <Button
                class="hidden dark:flex"
                size="lg"
                @click="$emit('nextStep')"
            >
                {{ $t('app.next') }}
                <ArrowRight />
            </Button>
        </div>
    </div>
</template>
