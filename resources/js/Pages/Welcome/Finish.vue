<script setup lang="ts">
import { Button } from '@/Components/ui/button';
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Cog } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

const showSettingsHint = ref(false);
onMounted(() => {
    setTimeout(() => {
        showSettingsHint.value = true;
    }, 1000);
});
</script>

<template>
    <div class="flex flex-col space-y-6">
        <div class="flex flex-col text-center font-bold text-white">
            <span class="font-lobster-two text-4xl italic">
                {{ $t('app.you are ready!') }}
            </span>
        </div>
        <div class="flex flex-col items-center gap-4">
            <Button
                :href="route('welcome.finish')"
                :as="Link"
                variant="secondary"
                size="lg"
                @click="$emit('nextStep')"
            >
                {{ $t('app.start') }}
                <ArrowRight />
            </Button>
        </div>
        <div
            :class="{
                'opacity-100': showSettingsHint,
            }"
            class="absolute inset-x-0 bottom-10 flex items-center justify-center gap-4 opacity-0 transition-opacity duration-1000"
        >
            {{ $t('app.more settings can be found here') }}
            <Button
                variant="ghost"
                :href="route('welcome.finish', { openSettings: true })"
                :as="Link"
            >
                <Cog />
                {{ $t('app.settings') }}
            </Button>
        </div>
    </div>
</template>
