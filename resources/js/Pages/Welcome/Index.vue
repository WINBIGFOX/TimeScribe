<script lang="ts" setup>
import Finish from '@/Pages/Welcome/Finish.vue'
import Start from '@/Pages/Welcome/Start.vue'
import Step1 from '@/Pages/Welcome/Step1.vue'
import { Head } from '@inertiajs/vue3'
import { useColorMode } from '@vueuse/core'
import { ref } from 'vue'

const currentStep = ref(0)
const steps = [Start, Step1, Finish]

const fadeAnimation = ref('fade-forward')
const nextStep = () => {
    fadeAnimation.value = 'fade-forward'
    if (currentStep.value === steps.length - 1) {
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
            <component :is="steps[currentStep]" @nextStep="nextStep" @prevStep="prevStep" v-bind="$page.props" />
        </Transition>
    </div>
</template>
