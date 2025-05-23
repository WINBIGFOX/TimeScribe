<script lang="ts" setup>
import { Button } from '@/Components/ui/button'
import { Head, Link } from '@inertiajs/vue3'
import { CalendarPlus } from 'lucide-vue-next'

// const props = defineProps<{}>()
</script>

<template>
    <Head title="Projects and Tags" />

    <div class="mb-4 flex items-center justify-between gap-4">
        <div class="text-foreground/80 text-base font-medium">{{ $t('app.projects and tags') }}</div>
        <div>
            <Button
                :as="Link"
                :href="route('tag.create')"
                prefetch
                preserve-scroll
                preserve-state
                size="sm"
                variant="outline"
            >
                <CalendarPlus />
                {{ $t('app.create new project/tag') }}
            </Button>
        </div>
    </div>
    <div class="flex grow flex-col overflow-hidden"></div>
</template>
