<script setup lang="ts">
import moment from 'moment/min/moment-with-locales';

const props = defineProps<{
    date: string;
}>();

const momentDate = moment(props.date, 'YYYY-MM-DD');
</script>

<template>
    <div
        class="bg-muted text-muted-foreground flex size-14 flex-col items-center justify-center rounded-lg text-center"
        :class="{
            'ring-primary ring-2 ring-inset':
                props.date === moment().format('YYYY-MM-DD'),
        }"
    >
        <span class="text-foreground leading-none font-medium">
            {{ momentDate.format('dd') }}
        </span>
        <span class="mt-0.5 text-xs leading-none">
            {{ momentDate.format('D') }}.
        </span>
    </div>
</template>
