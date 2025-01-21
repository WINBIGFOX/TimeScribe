<script setup lang="ts">
import {
    NumberField,
    NumberFieldContent,
    NumberFieldDecrement,
    NumberFieldIncrement,
    NumberFieldInput,
} from '@/Components/ui/number-field';
import { Switch } from '@/Components/ui/switch';
import { ref, watch } from 'vue';

const emit = defineEmits(['update:modelValue']);
const props = defineProps<{
    workday: string;
    modelValue: number;
}>();

const hours = ref(props.modelValue ?? 0);
const active = ref(props.modelValue > 0);

watch(hours, (newVal) => {
    emit('update:modelValue', newVal);
});

watch(active, (newVal) => {
    if (!newVal) {
        hours.value = 0;
    }
});
</script>

<template>
    <div class="h-10 flex items-center justify-between space-x-4">
        <p class="text-sm font-medium leading-none">{{ props.workday }}</p>
        <div class="flex items-center gap-4">
            <NumberField
                v-if="active"
                class="w-32"
                v-model.lazy="hours"
                :min="0"
                :max="15"
                :step="0.5"
                :format-options="{
                    style: 'decimal',
                    minimumFractionDigits: 1,
                }"
                locale="de-DE"
            >
                <NumberFieldContent>
                    <NumberFieldDecrement />
                    <NumberFieldInput />
                    <NumberFieldIncrement />
                </NumberFieldContent>
            </NumberField>
            <Switch v-model:checked="active" />
        </div>
    </div>
</template>
