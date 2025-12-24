<script lang="ts" setup>
import { Button } from '@/Components/ui/button'
import { CheckCircle2, Circle } from 'lucide-vue-next'
import { computed } from 'vue'

const props = defineProps<{
    mode: 'fixed' | 'flexible'
}>()

const emit = defineEmits<{
    (e: 'update:mode', value: 'fixed' | 'flexible'): void
    (e: 'nextStep'): void
    (e: 'prevStep'): void
}>()

const options = computed(() => [
    {
        value: 'fixed' as const,
        title: 'app.fixed weekly schedule title',
        description: 'app.fixed weekly schedule description'
    },
    {
        value: 'flexible' as const,
        title: 'app.flexible project hours title',
        description: 'app.flexible project hours description'
    }
])

const select = (value: 'fixed' | 'flexible') => {
    emit('update:mode', value)
}
</script>

<template>
    <div class="flex flex-col space-y-6">
        <div class="flex flex-col text-center font-bold text-white">
            <span class="font-lobster-two text-4xl italic">
                {{ $t('app.how do you want to use timescribe') }}
            </span>
            <span class="text-sm text-white/80">
                {{ $t('app.choose your mode') }}
            </span>
        </div>

        <div class="mx-auto flex w-96 flex-col gap-3">
            <button
                :aria-pressed="props.mode === option.value"
                :class="[
                    'border-border/60 flex items-start gap-3 rounded-lg border p-4 text-left transition',
                    props.mode === option.value
                        ? 'bg-background text-foreground border-primary/50 shadow-md'
                        : 'bg-background/80 text-foreground/80 hover:bg-background'
                ]"
                :key="option.value"
                type="button"
                v-for="option in options"
                @click="select(option.value)"
            >
                <div class="mt-0.5">
                    <component
                        :is="props.mode === option.value ? CheckCircle2 : Circle"
                        :class="props.mode === option.value ? 'text-emerald-500' : 'text-muted-foreground'"
                        class="h-5 w-5"
                    />
                </div>
                <div class="space-y-1">
                    <div class="text-sm font-semibold">
                        {{ $t(option.title) }}
                    </div>
                    <div class="text-muted-foreground text-xs">
                        {{ $t(option.description) }}
                    </div>
                </div>
            </button>
        </div>

        <div class="flex items-center justify-between">
            <Button @click="$emit('prevStep')" class="dark:text-foreground" size="lg" variant="ghost">
                {{ $t('app.back') }}
            </Button>
            <Button class="dark:hidden" size="lg" variant="secondary" @click="$emit('nextStep')">
                {{ $t('app.next') }}
            </Button>
            <Button class="hidden dark:flex" size="lg" @click="$emit('nextStep')">
                {{ $t('app.next') }}
            </Button>
        </div>
    </div>
</template>
