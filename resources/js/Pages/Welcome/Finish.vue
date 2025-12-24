<script lang="ts" setup>
import { Button } from '@/Components/ui/button'
import { Switch } from '@/Components/ui/switch'
import { Link, router, useForm } from '@inertiajs/vue3'
import { ArrowRight, CheckCircle2, Circle, Cog, KeyRound } from 'lucide-vue-next'
import { computed, onMounted, ref, watch } from 'vue'

const props = defineProps<{
    openAtLogin: boolean
    workSchedule?: {
        sunday?: number
        monday?: number
        tuesday?: number
        wednesday?: number
        thursday?: number
        friday?: number
        saturday?: number
    } | null
    vacationSettings: {
        default_entitlement_days: number
        auto_carryover: boolean
        minimum_day_hours: number
    }
    locale: string
    mode?: 'fixed' | 'flexible'
    trackVacation?: boolean
}>()

const form = useForm({
    openAtLogin: props.openAtLogin
})

const showSettingsHint = ref(false)

onMounted(() => {
    setTimeout(() => {
        showSettingsHint.value = true
    }, 1000)

    router.reload({
        only: ['workSchedule', 'vacationSettings', 'locale']
    })
})

const submit = (openAtLogin: boolean) => {
    router.flushAll()
    form.openAtLogin = openAtLogin
    form.patch(route('welcome.update'), {
        preserveScroll: true,
        preserveState: true
    })
}

watch(
    () => form.openAtLogin,
    (newValue) => {
        submit(newValue)
    }
)

const workScheduleHours = computed(() => {
    if (!props.workSchedule) {
        return 0
    }

    return (
        (props.workSchedule.sunday ?? 0) +
        (props.workSchedule.monday ?? 0) +
        (props.workSchedule.tuesday ?? 0) +
        (props.workSchedule.wednesday ?? 0) +
        (props.workSchedule.thursday ?? 0) +
        (props.workSchedule.friday ?? 0) +
        (props.workSchedule.saturday ?? 0)
    )
})

const workScheduleComplete = computed(() => {
    if (props.mode === 'flexible') {
        return false
    }

    return workScheduleHours.value > 0
})
const vacationSetupComplete = computed(() => {
    if (props.trackVacation === false) {
        return false
    }

    return props.vacationSettings.default_entitlement_days > 0 && props.vacationSettings.minimum_day_hours > 0
})

const vacationLabel = computed(() =>
    props.trackVacation === false ? 'app.vacation tracking skipped' : 'app.vacation settings saved'
)
const localeChosen = computed(() => !!props.locale)

const checklist = computed(() => [
    {
        labelKey: 'app.language selected',
        done: localeChosen.value
    },
    {
        labelKey: props.mode === 'flexible' ? 'app.work schedule skipped' : 'app.work schedule set',
        done: workScheduleComplete.value
    },
    {
        labelKey: vacationLabel.value,
        done: vacationSetupComplete.value
    }
])

const indicatorIcon = (done: boolean) => (done ? CheckCircle2 : Circle)
</script>

<template>
    <div class="flex flex-col space-y-6">
        <div class="flex flex-col text-center font-bold text-white">
            <span class="font-lobster-two text-4xl italic">
                {{ $t('app.almost finished') }}
            </span>
        </div>

        <div class="bg-background text-foreground flex w-96 items-center space-x-4 rounded-xl border p-4">
            <KeyRound />
            <div class="flex-1 space-y-1">
                <p class="text-sm leading-none font-medium">
                    {{ $t('app.start at login') }}
                </p>
            </div>
            <Switch v-model="form.openAtLogin" />
        </div>
        <div class="bg-background text-foreground flex w-96 flex-col gap-3 rounded-xl border p-4 shadow-sm">
            <div class="text-sm font-medium">
                {{ $t('app.quick check') }}
            </div>
            <div class="space-y-2">
                <div :key="item.labelKey" class="flex items-center gap-3" v-for="item in checklist">
                    <component
                        :is="indicatorIcon(item.done)"
                        :class="item.done ? 'text-emerald-500' : 'text-muted-foreground'"
                        class="h-5 w-5"
                    />
                    <span :class="item.done ? 'text-foreground' : 'text-muted-foreground'">
                        {{ $t(item.labelKey) }}
                    </span>
                </div>
            </div>
        </div>
        <div
            :class="{
                'opacity-100': showSettingsHint
            }"
            class="flex flex-col items-center gap-4 opacity-0 transition-opacity duration-1000"
        >
            <Button
                :as="Link"
                :href="route('welcome.finish')"
                @click="$emit('nextStep')"
                class="dark:hidden"
                size="lg"
                variant="secondary"
            >
                {{ $t('app.start') }}
                <ArrowRight />
            </Button>
            <Button
                :as="Link"
                :href="route('welcome.finish')"
                @click="$emit('nextStep')"
                class="hidden dark:flex"
                size="lg"
            >
                {{ $t('app.start') }}
                <ArrowRight />
            </Button>
        </div>
        <div
            :class="{
                'opacity-100': showSettingsHint
            }"
            class="dark:text-foreground absolute inset-x-0 bottom-10 flex items-center justify-center gap-4 opacity-0 transition-opacity duration-1000"
        >
            {{ $t('app.more settings can be found here') }}
            <Button :as="Link" :href="route('welcome.finish', { openSettings: true })" variant="ghost">
                <Cog />
                {{ $t('app.settings') }}
            </Button>
        </div>
    </div>
</template>
