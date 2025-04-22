<script lang="ts" setup>
import DeFlag from '@/Components/flags/DeFlag.vue'
import EnFlag from '@/Components/flags/EnFlag.vue'
import UsFlag from '@/Components/flags/UsFlag.vue'
import { Button } from '@/Components/ui/button'
import { Head, router } from '@inertiajs/vue3'
import { ArrowRight } from 'lucide-vue-next'

const updateLocale = (locale) => {
    router.patch(
        route('settings.general.updateLocale'),
        {
            locale
        },
        {
            preserveScroll: true,
            preserveState: true
        }
    )
}
</script>

<template>
    <div class="flex flex-col gap-6">
        <div class="font-lobster-two flex flex-col text-center text-4xl font-bold text-white italic">
            <span>{{ $t('app.welcome to') }}</span>
            <span class="text-6xl">TimeScribe</span>
        </div>

        <Button @click="$emit('nextStep')" class="dark:hidden" size="lg" variant="secondary">
            {{ $t('app.get started') }}
            <ArrowRight />
        </Button>
        <Button @click="$emit('nextStep')" class="hidden dark:flex" size="lg">
            {{ $t('app.get started') }}
            <ArrowRight />
        </Button>

        <div class="flex items-center justify-center gap-6">
            <div
                :class="{
                    '!border-white': $page.props.locale === 'de-DE'
                }"
                @click="updateLocale('de-DE')"
                class="rounded-lg border border-transparent p-1 transition-colors hover:bg-white"
            >
                <DeFlag class="!h-auto !w-10 rounded" />
            </div>
            <div
                :class="{
                    '!border-white': $page.props.locale === 'en-GB'
                }"
                @click="updateLocale('en-GB')"
                class="rounded-lg border border-transparent p-1 transition-colors hover:bg-white"
            >
                <EnFlag class="!h-auto !w-10 rounded" />
            </div>
            <div
                :class="{
                    '!border-white': $page.props.locale === 'en-US'
                }"
                @click="updateLocale('en-US')"
                class="rounded-lg border border-transparent p-1 transition-colors hover:bg-white"
            >
                <UsFlag class="!h-auto !w-10 rounded" />
            </div>
        </div>
    </div>
</template>
