<script setup lang="ts">
const props = defineProps<{
    weekdayName: string;
    day: string;
    active?: boolean;
}>();
</script>

<template>
    <div
        class="bg-muted text-muted-foreground flex size-14 flex-col items-center justify-center rounded-lg text-center"
        :class="{
            'ring-primary ring-2 ring-inset': props.active,
        }"
    >
        <span class="text-foreground leading-none font-medium">
            {{ props.weekdayName }}
        </span>
        <span class="mt-0.5 text-xs leading-none">{{ props.day }}.</span>
    </div>
</template>
