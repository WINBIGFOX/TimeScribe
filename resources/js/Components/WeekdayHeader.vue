<script lang="ts" setup>
import moment from 'moment/min/moment-with-locales'

const props = defineProps<{
    date: string
}>()

const momentDate = moment(props.date, 'YYYY-MM-DD')
</script>

<template>
    <div
        :class="{
            'ring-primary ring-2 ring-inset': props.date === moment().format('YYYY-MM-DD')
        }"
        class="bg-sidebar border-muted text-muted-foreground flex size-14 flex-col items-center justify-center rounded-lg border text-center"
    >
        <span class="text-foreground leading-none font-medium">
            {{ momentDate.format('dd') }}
        </span>
        <span class="mt-0.5 text-xs leading-none"> {{ momentDate.format('Do') }} </span>
    </div>
</template>
