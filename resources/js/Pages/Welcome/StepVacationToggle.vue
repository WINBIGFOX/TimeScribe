<script lang="ts" setup>
import { Button } from '@/Components/ui/button'
import { CheckCircle2, Circle } from 'lucide-vue-next'

const props = defineProps<{
    trackVacation: boolean
}>()

const emit = defineEmits<{
    (e: 'update:trackVacation', value: boolean): void
    (e: 'nextStep'): void
    (e: 'prevStep'): void
}>()

const select = (value: boolean) => emit('update:trackVacation', value)
</script>

<template>
    <div class="flex flex-col space-y-6">
        <div class="flex flex-col text-center font-bold text-white">
            <span class="font-lobster-two text-4xl italic">
                {{ $t('app.track vacation and absences') }}
            </span>
            <span class="text-white/80 text-sm">
                {{ $t('app.choose whether to show vacation steps') }}
            </span>
        </div>

        <div class="mx-auto flex w-96 flex-col gap-3">
            <button
                :aria-pressed="props.trackVacation"
                :class="[
                    'border-border/60 text-left rounded-lg border p-4 transition flex items-start gap-3',
                    props.trackVacation
                        ? 'bg-background text-foreground shadow-md border-primary/50'
                        : 'bg-background/80 text-foreground/80 hover:bg-background'
                ]"
                type="button"
                @click="select(true)"
            >
                <div class="mt-0.5">
                    <component :is="props.trackVacation ? CheckCircle2 : Circle" class="h-5 w-5 text-emerald-500" />
                </div>
                <div class="space-y-1">
                    <div class="text-sm font-semibold">
                        {{ $t('app.yes track vacation') }}
                    </div>
                    <div class="text-muted-foreground text-xs">
                        {{ $t('app.setup entitlement carryover minimum hours') }}
                    </div>
                </div>
            </button>
            <button
                :aria-pressed="!props.trackVacation"
                :class="[
                    'border-border/60 text-left rounded-lg border p-4 transition flex items-start gap-3',
                    !props.trackVacation
                        ? 'bg-background text-foreground shadow-md border-primary/50'
                        : 'bg-background/80 text-foreground/80 hover:bg-background'
                ]"
                type="button"
                @click="select(false)"
            >
                <div class="mt-0.5">
                    <component :is="!props.trackVacation ? CheckCircle2 : Circle" class="h-5 w-5 text-emerald-500" />
                </div>
                <div class="space-y-1">
                    <div class="text-sm font-semibold">
                        {{ $t('app.no only track time') }}
                    </div>
                    <div class="text-muted-foreground text-xs">
                        {{ $t('app.skip vacation steps') }}
                    </div>
                </div>
            </button>
        </div>

        <div class="flex items-center justify-between">
            <Button @click="$emit('prevStep')" class="dark:text-foreground" size="lg" variant="ghost">
                {{ $t('app.back') }}
            </Button>
            <Button
                class="dark:hidden"
                size="lg"
                variant="secondary"
                @click="$emit('nextStep')"
            >
                {{ $t('app.next') }}
            </Button>
            <Button
                class="hidden dark:flex"
                size="lg"
                @click="$emit('nextStep')"
            >
                {{ $t('app.next') }}
            </Button>
        </div>
    </div>
</template>
