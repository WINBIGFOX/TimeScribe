<script setup lang="ts">
import { Plus } from 'lucide-vue-next';

const props = defineProps<{
    duration?: number;
}>();
</script>

<template>
    <div
        class="border-muted-foreground text-muted-foreground mx-10 flex items-center gap-2 border-l-3 border-dotted text-sm"
        :class="{
            'py-1 pl-4': props.duration,
            'py-1 pl-2': !props.duration,
        }"
    >
        <div v-if="props.duration">
            {{ props.duration }}
            Minuten
        </div>
        <div
            class="hover:bg-muted-foreground/10 active:bg-muted-foreground/20 flex items-center gap-1 rounded px-2 py-1 transition-colors"
        >
            <Plus class="size-4" />
            Zeit hinzufügen
        </div>
    </div>
</template>
