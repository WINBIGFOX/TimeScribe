<script lang="ts" setup>
import BrFlag from '@/Components/flags/BrFlag.vue'
import CaFlag from '@/Components/flags/CaFlag.vue'
import CnFlag from '@/Components/flags/CnFlag.vue'
import DeFlag from '@/Components/flags/DeFlag.vue'
import DkFlag from '@/Components/flags/DkFlag.vue'
import EnFlag from '@/Components/flags/EnFlag.vue'
import FrFlag from '@/Components/flags/FrFlag.vue'
import ItFlag from '@/Components/flags/ItFlag.vue'
import UsFlag from '@/Components/flags/UsFlag.vue'
import { Button } from '@/Components/ui/button'
import { router } from '@inertiajs/vue3'
import { ArrowRight } from 'lucide-vue-next'

const locales = [
    { code: 'da_DK', component: DkFlag },
    { code: 'de_DE', component: DeFlag },
    { code: 'en_GB', component: EnFlag },
    { code: 'en_US', component: UsFlag },
    { code: 'fr_FR', component: FrFlag },
    { code: 'fr_CA', component: CaFlag },
    { code: 'it_IT', component: ItFlag },
    { code: 'pt_BR', component: BrFlag },
    { code: 'zh_CN', component: CnFlag }
]

const updateLocale = (locale) => {
    router.flushAll()
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

        <div class="grid grid-cols-3 items-center justify-center gap-6">
            <div :key="locale.code" class="flex items-center justify-center" v-for="locale in locales">
                <div
                    :class="{
                        'border-white!': $page.props.locale === locale.code
                    }"
                    @click="updateLocale(locale.code)"
                    class="rounded-lg border border-transparent p-1 transition-colors hover:bg-white"
                >
                    <component :is="locale.component" class="h-auto! w-10! rounded" />
                </div>
            </div>
        </div>
    </div>
</template>
