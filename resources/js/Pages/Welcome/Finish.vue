<script lang="ts" setup>
import { Button } from '@/Components/ui/button'
import { Switch } from '@/Components/ui/switch'
import { Link, useForm } from '@inertiajs/vue3'
import { ArrowRight, Cog, KeyRound } from 'lucide-vue-next'
import { onMounted, ref, watch } from 'vue'

const props = defineProps<{
    openAtLogin: boolean
}>()

const form = useForm({
    openAtLogin: props.openAtLogin
})

const showSettingsHint = ref(false)

onMounted(() => {
    setTimeout(() => {
        showSettingsHint.value = true
    }, 1000)
})

const submit = (openAtLogin: boolean) => {
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
