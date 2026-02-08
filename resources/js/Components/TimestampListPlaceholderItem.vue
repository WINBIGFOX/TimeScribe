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
    startOfDay?: string
    timestampBefore?: Timestamp
    timestampAfter?: Timestamp
}>()
</script>

<template>
    <div
        v-if="(props.duration ?? 0) > 0 || !props.timestampAfter || !props.timestampBefore"
        :class="{
            'py-1 pl-4': props.duration,
            'py-1 pl-2': !props.duration
        }"
        class="border-muted-foreground text-muted-foreground mx-6 flex items-center gap-2 border-l-3 border-dotted text-sm"
    >
        <div v-if="props.duration">
            {{ props.duration }}
            {{ $t('app.minutes') }}
        </div>
        <Link
            :href="route('timestamp.create', { datetime: props.timestampBefore?.ended_at?.date ?? props.startOfDay })"
            class="hover:bg-muted-foreground/10 active:bg-muted-foreground/20 flex items-center gap-1 rounded px-2 py-1 transition-colors"
            prefetch
            preserve-scroll
            preserve-state
            v-if="props.timestampBefore?.ended_at?.date || props.startOfDay"
        >
            <Plus class="size-4" />
            {{ $t('app.add time') }}
        </Link>

        <DropdownMenu v-if="props.duration && props.timestampBefore && props.timestampAfter">
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
                        timestamp_before: props.timestampBefore.id,
                        timestamp_after: props.timestampAfter.id,
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
                        timestamp_before: props.timestampBefore.id,
                        timestamp_after: props.timestampAfter.id,
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
