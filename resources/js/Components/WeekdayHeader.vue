<script setup lang="ts">
const props = defineProps<{
    weekdayName: string;
    day: string;
    active?: boolean;
}>();
</script>

<template>
    <div
        class="mx-auto flex size-14 flex-col items-center justify-center rounded-lg bg-muted text-center text-muted-foreground"
        :class="{
            'ring-2 ring-inset ring-primary': props.active,
        }"
    >
        <span class="font-medium leading-none text-foreground">
            {{ props.weekdayName }}
        </span>
        <span class="mt-0.5 text-xs leading-none">{{ props.day }}.</span>
    </div>
</template>
