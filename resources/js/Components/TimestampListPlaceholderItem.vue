<script lang="ts" setup>
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger
} from '@/Components/ui/dropdown-menu'
import { Timestamp } from '@/types'
import { Link } from '@inertiajs/vue3'
import { BetweenHorizontalEnd, BriefcaseBusiness, Coffee, Plus } from 'lucide-vue-next'

const props = defineProps<{
    duration?: number
    firstTimestamp?: Timestamp
    secondTimestamp?: Timestamp
}>()
</script>

<template>
    <div
        :class="{
            'py-1 pl-4': props.duration,
            'py-1 pl-2': !props.duration
        }"
        class="border-muted-foreground text-muted-foreground mx-10 flex items-center gap-2 border-l-3 border-dotted text-sm"
    >
        <div v-if="props.duration">
            {{ props.duration }}
            {{ $t('app.minutes') }}
        </div>
        <div
            class="hover:bg-muted-foreground/10 active:bg-muted-foreground/20 flex items-center gap-1 rounded px-2 py-1 transition-colors"
        >
            <Plus class="size-4" />
            {{ $t('app.add time') }}
        </div>

        <DropdownMenu v-if="props.duration && props.firstTimestamp && props.secondTimestamp">
            <DropdownMenuTrigger
                class="hover:bg-muted-foreground/10 active:bg-muted-foreground/20 flex items-center gap-1 rounded px-2 py-1 transition-colors"
            >
                <BetweenHorizontalEnd class="size-4" />
                {{ $t('app.fill the gap') }}
            </DropdownMenuTrigger>
            <DropdownMenuContent>
                <DropdownMenuLabel>{{ $t('app.fill with') }}</DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuItem
                    :as="Link"
                    :data="{
                        first_timestamp: props.firstTimestamp.id,
                        second_timestamp: props.secondTimestamp.id,
                        fill_with: 'work'
                    }"
                    :href="route('timestamp.fill')"
                    :preserve-state="false"
                    class="w-full"
                    method="post"
                    preserve-scroll
                >
                    <BriefcaseBusiness class="text-primary" />
                    {{ $t('app.work hours') }}
                </DropdownMenuItem>
                <DropdownMenuItem
                    :as="Link"
                    :data="{
                        first_timestamp: props.firstTimestamp.id,
                        second_timestamp: props.secondTimestamp.id,
                        fill_with: 'break'
                    }"
                    :href="route('timestamp.fill')"
                    :preserve-state="false"
                    class="w-full"
                    method="post"
                    preserve-scroll
                >
                    <Coffee class="text-pink-400" />
                    {{ $t('app.break time') }}
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    </div>
</template>
