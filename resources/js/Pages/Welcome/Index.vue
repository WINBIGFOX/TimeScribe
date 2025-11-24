<script lang="ts" setup>
import Finish from '@/Pages/Welcome/Finish.vue'
import Start from '@/Pages/Welcome/Start.vue'
import Step1 from '@/Pages/Welcome/Step1.vue'
import Step2 from '@/Pages/Welcome/Step2.vue'
import StepIntent from '@/Pages/Welcome/StepIntent.vue'
import StepVacationToggle from '@/Pages/Welcome/StepVacationToggle.vue'
import { Head } from '@inertiajs/vue3'
import { useColorMode } from '@vueuse/core'
import { type Component, computed, ref, watch } from 'vue'

const currentStep = ref(0)
const mode = ref<'fixed' | 'flexible'>('fixed')
const trackVacation = ref(true)

const steps = computed(() => {
    const items = [Start, StepIntent] as Component[]

    if (mode.value === 'fixed') {
        items.push(StepVacationToggle, Step1)

        if (trackVacation.value) {
            items.push(Step2)
        }
    }

    items.push(Finish)

    return items
})

const fadeAnimation = ref('fade-forward')
const nextStep = () => {
    fadeAnimation.value = 'fade-forward'
    if (currentStep.value === steps.value.length - 1) {
        return
    }
    currentStep.value++
}

const prevStep = () => {
    fadeAnimation.value = 'fade-backward'
    if (currentStep.value === 0) {
        return
    }
    currentStep.value--
}

watch(
    steps,
    (list) => {
        if (currentStep.value > list.length - 1) {
            currentStep.value = list.length - 1
        }
    },
    { immediate: true }
)

watch(
    () => mode.value,
    (value) => {
        if (value === 'flexible') {
            trackVacation.value = false
        }
    }
)
useColorMode()
</script>

<template>
    <Head title="Welcome to TimeScribe" />
    <div
        class="sticky top-0 flex h-10 shrink-0 items-center justify-center font-medium"
        style="-webkit-app-region: drag"
    />
    <div
        class="bg-primary dark:bg-sidebar text-primary-foreground absolute inset-0 flex items-center justify-center duration-1000 select-none"
    >
        <Transition :name="fadeAnimation" mode="out-in">
            <component
                :is="steps[currentStep]"
                @nextStep="nextStep"
                @prevStep="prevStep"
                @update:mode="mode = $event"
                @update:trackVacation="trackVacation = $event"
                v-bind="{ ...$page.props, mode, trackVacation }"
            />
        </Transition>
    </div>
</template>
