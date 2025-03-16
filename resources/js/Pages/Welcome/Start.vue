<script setup lang="ts">
import DeFlag from '@/Components/flags/DeFlag.vue';
import EnFlag from '@/Components/flags/EnFlag.vue';
import UsFlag from '@/Components/flags/UsFlag.vue';
import { Button } from '@/Components/ui/button';
import { router } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';

const updateLocale = (locale) => {
    router.patch(
        route('settings.updateLocale'),
        {
            locale,
        },
        {
            preserveScroll: true,
            preserveState: true,
        },
    );
};
</script>

<template>
    <div class="flex flex-col gap-6">
        <div
            class="font-lobster-two flex flex-col text-center text-4xl font-bold text-white italic"
        >
            <span>{{ $t('app.welcome to') }}</span>
            <span class="text-6xl">TimeScribe</span>
        </div>

        <Button @click="$emit('nextStep')" size="lg" variant="secondary">
            {{ $t('app.get started') }}
            <ArrowRight />
        </Button>

        <div class="flex items-center justify-center gap-6">
            <div
                @click="updateLocale('de-DE')"
                class="rounded-lg border border-transparent p-1 transition-colors hover:bg-white"
                :class="{
                    '!border-white': $page.props.locale === 'de-DE',
                }"
            >
                <DeFlag class="!h-auto !w-10 rounded" />
            </div>
            <div
                @click="updateLocale('en-GB')"
                class="rounded-lg border border-transparent p-1 transition-colors hover:bg-white"
                :class="{
                    '!border-white': $page.props.locale === 'en-GB',
                }"
            >
                <EnFlag class="!h-auto !w-10 rounded" />
            </div>
            <div
                @click="updateLocale('en-US')"
                class="rounded-lg border border-transparent p-1 transition-colors hover:bg-white"
                :class="{
                    '!border-white': $page.props.locale === 'en-US',
                }"
            >
                <UsFlag class="!h-auto !w-10 rounded" />
            </div>
        </div>
    </div>
</template>
