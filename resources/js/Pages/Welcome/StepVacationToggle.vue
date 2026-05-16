<script lang="ts" setup>
import { Button } from '@/Components/ui/button'
import { CheckCircle2, Circle } from '@lucide/vue'

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
            <span class="text-sm text-white/80">
                {{ $t('app.choose whether to show vacation steps') }}
            </span>
        </div>

        <div class="mx-auto flex w-96 flex-col gap-3">
            <button
                :aria-pressed="props.trackVacation"
                :class="[
                    'border-border/60 flex items-start gap-3 rounded-lg border p-4 text-left transition',
                    props.trackVacation
                        ? 'bg-background text-foreground border-primary/50 shadow-md'
                        : 'bg-background/80 text-foreground/80 hover:bg-background'
                ]"
                @click="select(true)"
                type="button"
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
                    'border-border/60 flex items-start gap-3 rounded-lg border p-4 text-left transition',
                    !props.trackVacation
                        ? 'bg-background text-foreground border-primary/50 shadow-md'
                        : 'bg-background/80 text-foreground/80 hover:bg-background'
                ]"
                @click="select(false)"
                type="button"
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
            <Button @click="$emit('nextStep')" class="dark:hidden" size="lg" variant="secondary">
                {{ $t('app.next') }}
            </Button>
            <Button @click="$emit('nextStep')" class="hidden dark:flex" size="lg">
                {{ $t('app.next') }}
            </Button>
        </div>
    </div>
</template>
