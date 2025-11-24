<script lang="ts" setup>
import { PageHeader } from '@/Components/ui-custom/page-header'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import { Switch } from '@/Components/ui/switch'
import { Head, router, useForm } from '@inertiajs/vue3'
import { useDebounceFn } from '@vueuse/core'
import { AlarmClockCheck, LockKeyhole, TimerReset } from 'lucide-vue-next'
import moment from 'moment/min/moment-with-locales'
import { ref, watch } from 'vue'

const props = defineProps<{
    stopBreakAutomatic?: string
    stopBreakAutomaticActivationTime?: string
    stopWorkTimeReset?: number
    stopBreakTimeReset?: number
}>()

const form = useForm({
    stopBreakAutomatic: props.stopBreakAutomatic ?? '',
    stopBreakAutomaticActivationTime: props.stopBreakAutomaticActivationTime ?? '',
    stopWorkTimeReset: props.stopWorkTimeReset?.toString() ?? '0',
    stopBreakTimeReset: props.stopBreakTimeReset?.toString() ?? '0'
})

const submit = () => {
    router.flushAll()
    form.patch(route('settings.start-stop.update'), {
        preserveScroll: true,
        preserveState: true
    })
}

const stopBreakAutomatikCheck = ref(props.stopBreakAutomatic !== null)
const stopBreakAutomatikActivationCheck = ref(props.stopBreakAutomaticActivationTime !== null)
const stopTimeResetCheck = ref(!!(props.stopWorkTimeReset || props.stopBreakTimeReset))

const debouncedSubmit = useDebounceFn(submit, 500)
watch(
    () => [
        form.stopBreakTimeReset,
        form.stopBreakAutomatic,
        form.stopBreakAutomaticActivationTime,
        form.stopWorkTimeReset
    ],
    debouncedSubmit,
    { deep: true }
)
watch(stopBreakAutomatikCheck, () => {
    if (stopBreakAutomatikCheck.value === false) {
        form.stopBreakAutomatic = ''
        stopBreakAutomatikActivationCheck.value = false
    }
})
watch(stopBreakAutomatikActivationCheck, () => {
    if (stopBreakAutomatikActivationCheck.value === false) {
        form.stopBreakAutomaticActivationTime = ''
    }
})
watch(stopTimeResetCheck, () => {
    if (stopTimeResetCheck.value === false) {
        form.stopWorkTimeReset = '0'
        form.stopBreakTimeReset = '0'
    }
})
</script>

<template>
    <Head title="Settings - Start/Stop" />
    <PageHeader :title="$t('app.auto start/break')" />
    <div>
        <div class="flex items-start space-x-4 py-4">
            <LockKeyhole />
            <div class="flex-1 space-y-1">
                <div class="flex items-center gap-10">
                    <div class="flex-1 space-y-1">
                        <p class="text-sm leading-none font-medium">
                            {{ $t('app.auto start/break') }}
                        </p>
                        <p class="text-muted-foreground text-sm">
                            {{
                                $t(
                                    'app.when the computer is locked, the working time can be automatically stopped, or the break can be started.'
                                )
                            }}
                        </p>
                    </div>
                    <Switch v-model="stopBreakAutomatikCheck" />
                </div>

                <Select v-if="stopBreakAutomatikCheck" v-model="form.stopBreakAutomatic">
                    <SelectTrigger class="mt-4 ml-auto w-1/2">
                        <SelectValue :placeholder="$t('app.action')" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="stop">
                            {{ $t('app.stop working time') }}
                        </SelectItem>
                        <SelectItem value="break">
                            {{ $t('app.start break') }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>
        <div class="flex items-start space-x-4 py-4" v-if="stopBreakAutomatikCheck">
            <AlarmClockCheck />
            <div class="flex-1 space-y-1">
                <div class="flex items-center gap-10">
                    <div class="flex-1 space-y-1">
                        <p class="text-sm leading-none font-medium">
                            {{ $t('app.activate based on time') }}
                        </p>
                        <p class="text-muted-foreground text-sm">
                            {{ $t('app.the auto start/break feature will only be activated at a specified time.') }}
                        </p>
                    </div>
                    <Switch v-model="stopBreakAutomatikActivationCheck" />
                </div>

                <Select v-model="form.stopBreakAutomaticActivationTime">
                    <SelectTrigger class="mt-4 ml-auto w-1/2" v-if="stopBreakAutomatikActivationCheck">
                        <SelectValue :placeholder="$t('app.time')" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem :key="hour" :value="`${hour + 12}`" v-for="hour in 11">
                            {{
                                $t('app.from :time', {
                                    time: moment(hour + 12, 'HH').format('LT')
                                })
                            }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <div
                    class="text-muted-foreground ml-auto w-1/2 text-xs italic"
                    v-if="stopBreakAutomatikActivationCheck && form.stopBreakAutomaticActivationTime"
                >
                    {{
                        $t('app.the automatic system is active until :time on the following day.', {
                            time: moment(5, 'H').format('LT')
                        })
                    }}
                </div>
            </div>
        </div>
        <div class="mt-4 flex items-start space-x-4 border-t py-4 pt-8">
            <TimerReset />
            <div class="flex-1 space-y-1">
                <div class="flex items-center gap-10">
                    <div class="flex-1 space-y-1">
                        <p class="text-sm leading-none font-medium">
                            {{ $t('app.forgotten stop') }}
                        </p>
                        <p class="text-muted-foreground text-sm">
                            {{
                                $t(
                                    'app.if you forget to stop the working or break time, it will be automatically stopped retroactively.'
                                )
                            }}
                        </p>
                    </div>
                    <Switch v-model="stopTimeResetCheck" />
                </div>
                <p class="text-muted-foreground my-2 text-xs italic">
                    {{
                        $t(
                            'app.if an absence exceeds the configured time, the working or break time will be stopped retroactively.'
                        )
                    }}
                </p>
                <div class="mt-4 flex gap-4" v-if="stopTimeResetCheck">
                    <div class="flex-1">
                        <p class="mb-2 text-sm leading-none">
                            {{ $t('app.stop working time after:') }}
                        </p>
                        <Select v-model="form.stopWorkTimeReset">
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Zeit" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="0">
                                    {{ $t('app.never') }}
                                </SelectItem>
                                <SelectItem value="5">
                                    5
                                    {{ $t('app.minutes') }}
                                </SelectItem>
                                <SelectItem value="10">
                                    10
                                    {{ $t('app.minutes') }}
                                </SelectItem>
                                <SelectItem value="20">
                                    20
                                    {{ $t('app.minutes') }}
                                </SelectItem>
                                <SelectItem value="30">
                                    30
                                    {{ $t('app.minutes') }}
                                </SelectItem>
                                <SelectItem value="40">
                                    40
                                    {{ $t('app.minutes') }}
                                </SelectItem>
                                <SelectItem value="50">
                                    50
                                    {{ $t('app.minutes') }}
                                </SelectItem>
                                <SelectItem value="60">
                                    1:00
                                    {{ $t('app.hour') }}
                                </SelectItem>
                                <SelectItem value="90">
                                    1:30
                                    {{ $t('app.hour') }}
                                </SelectItem>
                                <SelectItem value="120">
                                    2:00
                                    {{ $t('app.hours') }}
                                </SelectItem>
                                <SelectItem value="150">
                                    2:30
                                    {{ $t('app.hours') }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="flex-1">
                        <p class="mb-2 text-sm leading-none">
                            {{ $t('app.stop break time after:') }}
                        </p>
                        <Select v-model="form.stopBreakTimeReset">
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Zeit" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="0">
                                    {{ $t('app.never') }}
                                </SelectItem>
                                <SelectItem value="5">
                                    5
                                    {{ $t('app.minutes') }}
                                </SelectItem>
                                <SelectItem value="10">
                                    10
                                    {{ $t('app.minutes') }}
                                </SelectItem>
                                <SelectItem value="20">
                                    20
                                    {{ $t('app.minutes') }}
                                </SelectItem>
                                <SelectItem value="30">
                                    30
                                    {{ $t('app.minutes') }}
                                </SelectItem>
                                <SelectItem value="40">
                                    40
                                    {{ $t('app.minutes') }}
                                </SelectItem>
                                <SelectItem value="50">
                                    50
                                    {{ $t('app.minutes') }}
                                </SelectItem>
                                <SelectItem value="60">
                                    1:00
                                    {{ $t('app.hour') }}
                                </SelectItem>
                                <SelectItem value="90">
                                    1:30
                                    {{ $t('app.hour') }}
                                </SelectItem>
                                <SelectItem value="120">
                                    2:00
                                    {{ $t('app.hours') }}
                                </SelectItem>
                                <SelectItem value="150">
                                    2:30
                                    {{ $t('app.hours') }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
