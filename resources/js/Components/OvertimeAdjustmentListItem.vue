<script lang="ts" setup>
import { Button } from '@/Components/ui/button'
import { secToFormat } from '@/lib/utils'
import { OvertimeAdjustment } from '@/types'
import { router } from '@inertiajs/vue3'
import { Minus, Pencil, Pin, Plus, Trash } from 'lucide-vue-next'
import moment from 'moment/min/moment-with-locales'

const props = defineProps<{
    overtimeAdjustment: OvertimeAdjustment
}>()

defineEmits<{
    (e: 'edit', overtimeAdjustment: OvertimeAdjustment): void
}>()

const destroy = () => {
    router.delete(
        route('overtime-adjustment.destroy', {
            date: props.overtimeAdjustment.effective_date.date,
            overtimeAdjustment: props.overtimeAdjustment.id
        }),
        {
            data: {
                confirm: false
            },
            preserveScroll: true,
            preserveState: 'errors'
        }
    )
}
</script>

<template>
    <div class="bg-sidebar overflow-clip rounded-lg">
        <div
            :style="
                '--linear-color: var(--color-' +
                (props.overtimeAdjustment.type === 'absolute'
                    ? 'lime-400'
                    : props.overtimeAdjustment.seconds < 0
                      ? 'green-500'
                      : 'amber-400') +
                ')'
            "
            class="h-1.5 [background-image:repeating-linear-gradient(45deg,transparent_0_.75rem,color-mix(in_srgb,var(--linear-color)_100%,transparent)_.75rem_1.5rem)]"
        ></div>
        <div class="flex items-center gap-4 p-2.5">
            <div
                :class="{
                    'text-primary-foreground bg-green-500':
                        props.overtimeAdjustment.type === 'relative' && props.overtimeAdjustment.seconds < 0,
                    'text-primary-foreground bg-amber-400':
                        props.overtimeAdjustment.type === 'relative' && props.overtimeAdjustment.seconds > 0,
                    'text-primary-foreground bg-lime-400': props.overtimeAdjustment.type === 'absolute'
                }"
                class="flex size-8 shrink-0 items-center justify-center rounded-md"
            >
                <Pin class="size-5" v-if="props.overtimeAdjustment.type === 'absolute'" />
                <template v-else>
                    <Plus class="size-5" v-if="props.overtimeAdjustment.seconds > 0" />
                    <Minus class="size-5" v-else />
                </template>
            </div>

            <div class="ml-2 flex min-w-16 flex-1 shrink-0 items-center gap-2 leading-none font-medium tabular-nums">
                {{ moment(props.overtimeAdjustment.effective_date.date).format('L') }}
            </div>
            <div class="flex flex-col items-end gap-1">
                <span class="text-muted-foreground text-xs leading-none">
                    {{ $t('app.hours') }}
                </span>
                <span class="leading-none font-medium tabular-nums">
                    {{
                        secToFormat(
                            props.overtimeAdjustment.seconds,
                            false,
                            true,
                            true,
                            props.overtimeAdjustment.seconds !== 0
                        )
                    }}
                </span>
            </div>
            <div class="ml-auto flex items-center justify-end">
                <Button
                    class="text-muted-foreground size-8"
                    size="icon"
                    variant="ghost"
                    @click="$emit('edit', props.overtimeAdjustment)"
                >
                    <Pencil />
                </Button>
                <Button
                    @click="destroy"
                    class="text-destructive hover:bg-destructive hover:text-destructive-foreground size-8"
                    size="icon"
                    variant="ghost"
                >
                    <Trash />
                </Button>
            </div>
        </div>
        <div
            v-if="props.overtimeAdjustment.note"
            class="text-muted-foreground mx-2 border-t py-2 text-sm whitespace-pre-wrap"
        >
            {{ props.overtimeAdjustment.note }}
        </div>
    </div>
</template>
