<script lang="ts" setup>
import { PageHeader } from '@/Components/ui-custom/page-header'
import { Select, SelectContent, SelectItem, SelectSeparator, SelectTrigger, SelectValue } from '@/Components/ui/select'
import { Switch } from '@/Components/ui/switch'
import { Enum } from '@/types'
import { Head, router, useForm } from '@inertiajs/vue3'
import { useDebounceFn } from '@vueuse/core'
import { AppWindowMac, CalendarMinus, Eye, Globe, KeyRound, Languages, PanelsTopLeft, SunMoon } from 'lucide-vue-next'
import { ref, watch } from 'vue'

const props = defineProps<{
    openAtLogin?: boolean
    theme?: string
    showTimerOnUnlock?: boolean
    holidayRegion?: string
    holidayRegions?: Enum
    locale: string
    appActivityTracking?: boolean
    timezones?: string[]
    timezone: string
    defaultOverview: string
}>()

const form = useForm({
    openAtLogin: props.openAtLogin ?? false,
    theme: props.theme ?? 'system',
    showTimerOnUnlock: props.showTimerOnUnlock ?? false,
    holidayRegion: props.holidayRegion ?? '',
    locale: props.locale,
    appActivityTracking: props.appActivityTracking ?? false,
    timezone: props.timezone,
    default_overview: props.defaultOverview ?? 'week'
})

const submit = () => {
    router.flushAll()
    form.patch(route('settings.general.update'), {
        preserveScroll: true,
        preserveState: true
    })
}
const holidayCheck = ref(props.holidayRegion !== null)

const debouncedSubmit = useDebounceFn(submit, 500)
watch(
    () => [
        form.theme,
        form.locale,
        form.openAtLogin,
        form.showTimerOnUnlock,
        form.holidayRegion,
        form.appActivityTracking,
        form.timezone,
        form.default_overview
    ],
    debouncedSubmit,
    { deep: true }
)
watch(holidayCheck, () => {
    if (holidayCheck.value === false) {
        form.holidayRegion = ''
    }
})
</script>

<template>
    <Head title="Settings - General" />
    <PageHeader :title="$t('app.general settings')" />
    <div>
        <div class="flex items-center space-x-4 py-4">
            <KeyRound />
            <div class="flex-1">
                <p class="text-sm leading-none font-medium">
                    {{ $t('app.start at login') }}
                </p>
            </div>
            <Switch v-model="form.openAtLogin" />
        </div>
        <div class="flex items-center space-x-4 py-4">
            <Languages />
            <div class="flex flex-1 items-center gap-4">
                <p class="flex-1 text-sm leading-none font-medium">
                    {{ $t('app.language') }}
                </p>
                <Select size="5" v-model="form.locale">
                    <SelectTrigger class="w-1/2">
                        <SelectValue :placeholder="$t('app.language')" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="da_DK">
                            {{ $t('app.danish') }}
                        </SelectItem>
                        <SelectItem value="de_DE">
                            {{ $t('app.german') }}
                        </SelectItem>
                        <SelectItem value="en_GB">
                            {{ $t('app.english (UK)') }}
                        </SelectItem>
                        <SelectItem value="en_US">
                            {{ $t('app.english (US)') }}
                        </SelectItem>
                        <SelectItem value="fr_CA">
                            {{ $t('app.french (CA)') }}
                        </SelectItem>
                        <SelectItem value="fr_FR">
                            {{ $t('app.french (FR)') }}
                        </SelectItem>
                        <SelectItem value="it_IT">
                            {{ $t('app.italian') }}
                        </SelectItem>
                        <SelectItem value="pt_BR">
                            {{ $t('app.portuguese (BR)') }}
                        </SelectItem>
                        <SelectItem value="zh_CN">
                            {{ $t('app.chinese') }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>
        <div class="flex items-center space-x-4 py-4">
            <Globe />
            <div class="flex flex-1 items-center gap-4">
                <p class="flex-1 text-sm leading-none font-medium">
                    {{ $t('app.timezone') }}
                </p>
                <Select size="5" v-model="form.timezone">
                    <SelectTrigger class="w-1/2">
                        <SelectValue :placeholder="$t('app.timezone')" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem :key="timezone" :value="timezone" v-for="timezone in props.timezones">
                            {{ timezone }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>
        <div class="flex items-start space-x-4 py-4">
            <SunMoon />
            <div class="flex flex-1 gap-4">
                <div class="flex-1 space-y-1">
                    <p class="text-sm leading-none font-medium">
                        {{ $t('app.appearance') }}
                    </p>
                    <p class="text-muted-foreground text-sm text-balance">
                        {{ $t('app.choose the appearance of the application.') }}
                    </p>
                </div>

                <Select size="5" v-model="form.theme">
                    <SelectTrigger class="w-1/2">
                        <SelectValue :placeholder="$t('app.appearance')" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="system">
                            {{ $t('app.system') }}
                        </SelectItem>
                        <SelectItem value="light">
                            {{ $t('app.light') }}
                        </SelectItem>
                        <SelectItem value="dark">
                            {{ $t('app.dark') }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>
        <div class="flex items-center space-x-4 py-4">
            <PanelsTopLeft />
            <div class="flex flex-1 items-center gap-4">
                <p class="flex-1 text-sm leading-none font-medium">
                    {{ $t('app.default overview') }}
                </p>
                <Select size="5" v-model="form.default_overview">
                    <SelectTrigger class="w-1/2">
                        <SelectValue :placeholder="$t('app.default overview')" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="day">
                            {{ $t('app.daily overview') }}
                        </SelectItem>
                        <SelectItem value="week">
                            {{ $t('app.weekly overview') }}
                        </SelectItem>
                        <SelectItem value="month">
                            {{ $t('app.monthly overview') }}
                        </SelectItem>
                        <SelectItem value="year">
                            {{ $t('app.yearly overview') }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>
        <div class="flex items-start space-x-4 py-4">
            <Eye />
            <div class="flex-1 space-y-1">
                <p class="text-sm leading-none font-medium">
                    {{ $t('app.show timer automatically') }}
                </p>
                <p class="text-muted-foreground text-sm text-balance">
                    {{ $t('app.when the computer is unlocked, the timer can be displayed.') }}
                </p>
            </div>
            <Switch class="self-center" v-model="form.showTimerOnUnlock" />
        </div>

        <div class="flex items-start space-x-4 py-4">
            <AppWindowMac />
            <div class="flex-1 space-y-1">
                <p class="text-sm leading-none font-medium">
                    {{ $t('app.record app activities') }}
                </p>
                <p class="text-muted-foreground text-sm">
                    {{ $t('app.records your app activity and saves which app you were active in and for how long.') }}
                </p>
            </div>
            <Switch class="self-center" v-model="form.appActivityTracking" />
        </div>

        <div class="flex items-start space-x-4 py-4">
            <CalendarMinus />
            <div class="flex-1 space-y-1">
                <div class="flex items-center gap-10">
                    <div class="flex-1 space-y-1">
                        <p class="text-sm leading-none font-medium">
                            {{ $t('app.consider public holidays') }}
                        </p>
                        <p class="text-muted-foreground text-sm">
                            {{ $t('app.working hours on public holidays are fully credited.') }}
                        </p>
                    </div>
                    <Switch v-model="holidayCheck" />
                </div>
                <Select size="5" v-if="holidayCheck && props.holidayRegions" v-model="form.holidayRegion">
                    <SelectTrigger class="mt-2 ml-auto w-1/2">
                        <SelectValue placeholder="Region" />
                    </SelectTrigger>
                    <SelectContent>
                        <template :key="key" v-for="(name, key) in props.holidayRegions">
                            <SelectSeparator
                                v-if="
                                    key.toString().length === 2 &&
                                    key.toString() !== Object.keys(props.holidayRegions)[0]
                                "
                            />
                            <SelectItem :value="key">{{ name }}</SelectItem>
                        </template>
                    </SelectContent>
                </Select>
            </div>
        </div>
    </div>
</template>
