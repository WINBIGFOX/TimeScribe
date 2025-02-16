<script setup lang="ts">
import MainDialog from '@/Components/dialogs/MainDialog.vue';
import { Label } from '@/Components/ui/label';
import {
    NumberField,
    NumberFieldContent,
    NumberFieldDecrement,
    NumberFieldIncrement,
    NumberFieldInput,
} from '@/Components/ui/number-field';
import { secToFormat } from '@/lib/utils';
import { Timestamp } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { BriefcaseBusiness, Coffee } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    submit_route: string;
    timestamp: Timestamp;
}>();

const form = useForm({
    started_at: props.timestamp.started_at.date,
    ended_at: props.timestamp.ended_at?.date ?? '',
    type: props.timestamp.type,
});

const startTimeHour = ref(
    Math.floor(parseInt(props.timestamp.started_at.formatted) / 100),
);
const startTimeMinute = ref(
    Math.floor(parseInt(props.timestamp.started_at.formatted) % 100),
);

const endTimeHour = ref(
    Math.floor(parseInt(props.timestamp.ended_at?.formatted ?? '0') / 100),
);
const endTimeMinute = ref(
    Math.floor(parseInt(props.timestamp.ended_at?.formatted ?? '0') % 100),
);

const submit = () => {
    form.patch(props.submit_route, {
        preserveScroll: 'errors',
        preserveState: 'errors',
    });
};
</script>

<template>
    <MainDialog
        :loading="form.processing"
        @submit="submit"
        close="Abbrechen"
        submit="Speichern"
    >
        <template #title>
            <div class="flex items-center gap-2">
                <div
                    class="text-primary-foreground flex size-8 shrink-0 items-center justify-center rounded-md"
                    :class="{
                        'bg-primary': props.timestamp.type === 'work',
                        'bg-pink-400': props.timestamp.type === 'break',
                    }"
                >
                    <BriefcaseBusiness
                        class="size-5"
                        v-if="props.timestamp.type === 'work'"
                    />
                    <Coffee
                        class="size-5"
                        v-if="props.timestamp.type === 'break'"
                    />
                </div>
                {{ props.timestamp.type === 'work' ? 'Arbeitszeit' : 'Pause' }}
                bearbeiten
            </div>
        </template>
        <div class="divide divide-accent mb-4 space-y-4 divide-y">
            <div class="flex items-center pb-4">
                <div class="mt-4 flex flex-1 gap-2">
                    Start um:
                    <div class="w-20 shrink-0 text-right font-medium">
                        {{
                            secToFormat(
                                startTimeHour * 3600 + startTimeMinute * 60,
                                false,
                                true,
                                true,
                            )
                        }}
                        Uhr
                    </div>
                </div>
                <div class="flex flex-1 gap-2">
                    <NumberField
                        :disabled="!props.timestamp.can_start_edit"
                        id="startHour"
                        v-model="startTimeHour"
                        :min="0"
                        :max="23"
                        class="flex-1"
                    >
                        <Label for="startHour">Stunde</Label>
                        <NumberFieldContent>
                            <NumberFieldDecrement />
                            <NumberFieldInput />
                            <NumberFieldIncrement />
                        </NumberFieldContent>
                    </NumberField>
                    <NumberField
                        :disabled="!props.timestamp.can_start_edit"
                        id="startMin"
                        v-model="startTimeMinute"
                        :min="0"
                        :max="59"
                        class="flex-1"
                    >
                        <Label for="startMin">Minute</Label>
                        <NumberFieldContent>
                            <NumberFieldDecrement />
                            <NumberFieldInput />
                            <NumberFieldIncrement />
                        </NumberFieldContent>
                    </NumberField>
                </div>
            </div>
            <div class="flex items-center">
                <div class="mt-4 flex flex-1 gap-2">
                    Ende um:
                    <div class="w-20 shrink-0 text-right font-medium">
                        {{
                            secToFormat(
                                endTimeHour * 3600 + endTimeMinute * 60,
                                false,
                                true,
                                true,
                            )
                        }}
                        Uhr
                    </div>
                </div>
                <div class="flex flex-1 gap-2">
                    <NumberField
                        :disabled="!props.timestamp.can_end_edit"
                        id="endHour"
                        v-model="endTimeHour"
                        :min="0"
                        :max="23"
                        class="flex-1"
                    >
                        <Label for="endHour">Stunde</Label>
                        <NumberFieldContent>
                            <NumberFieldDecrement />
                            <NumberFieldInput />
                            <NumberFieldIncrement />
                        </NumberFieldContent>
                    </NumberField>
                    <NumberField
                        :disabled="!props.timestamp.can_end_edit"
                        id="endMinute"
                        v-model="endTimeMinute"
                        :min="0"
                        :max="59"
                        class="flex-1"
                    >
                        <Label for="endMinute">Minute</Label>
                        <NumberFieldContent>
                            <NumberFieldDecrement />
                            <NumberFieldInput />
                            <NumberFieldIncrement />
                        </NumberFieldContent>
                    </NumberField>
                </div>
            </div>
        </div>
    </MainDialog>
</template>
