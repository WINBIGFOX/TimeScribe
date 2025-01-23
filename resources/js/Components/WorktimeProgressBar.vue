<script setup lang="ts">
const props = defineProps<{
    plan?: number | string;
    progress: number;
}>();
</script>

<template>
    <div class="mx-auto flex w-14 grow flex-col">
        <div
            v-if="props.plan"
            class="rounded-t-lg border border-muted bg-background text-center text-xs text-muted-foreground"
        >
            {{ props.plan }} Std.
        </div>
        <div
            v-if="props.plan"
            class="relative grow overflow-hidden rounded-b-lg bg-muted"
        >
            <div
                class="absolute inset-x-0 bottom-0 bg-primary transition-all duration-300"
                :style="{
                    height: `${props.progress}%`,
                }"
            />
        </div>
    </div>
</template>
