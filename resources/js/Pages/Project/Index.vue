<script lang="ts" setup>
import ConfirmationDialog from '@/Components/dialogs/ConfirmationDialog.vue'
import ProjectListItem from '@/Components/ProjectListItem.vue'
import { Button } from '@/Components/ui/button'
import { Project } from '@/types'
import { Head, Link, usePage, usePoll } from '@inertiajs/vue3'
import { CirclePlus, Plus } from 'lucide-vue-next'
import { watch } from 'vue'

const props = defineProps<{
    projects: Project[]
    current_project_id?: number
}>()

const { start, stop } = usePoll(5000, undefined, {
    autoStart: false
})

const page = usePage()
if (page.props.recording) {
    start()
} else {
    stop()
}

watch(
    () => page.props.recording,
    (recording) => {
        if (recording) {
            start()
        } else {
            stop()
        }
    }
)
</script>

<template>
    <Head title="Projects" />

    <div class="mb-4 flex items-center justify-between gap-4">
        <div class="text-foreground/80 text-base font-medium">{{ $t('app.projects') }}</div>
        <div>
            <Button
                :as="Link"
                :href="route('project.create')"
                prefetch
                preserve-scroll
                preserve-state
                size="sm"
                variant="outline"
            >
                <Plus />
                {{ $t('app.create new project') }}
            </Button>
        </div>
    </div>

    <div class="flex grow flex-col gap-2 overflow-y-auto pb-6" v-if="props.projects.length">
        <ProjectListItem
            :is-current="project.id === props.current_project_id"
            :key="project.id"
            :project="project"
            v-for="project in props.projects"
        />
    </div>
    <div class="mt-32 flex grow justify-center" v-else>
        <div class="w-2/3">
            <div class="flex items-start space-x-4 py-4">
                <CirclePlus />
                <div class="flex-1 space-y-1">
                    <p class="text-sm leading-none font-medium">
                        {{ $t('app.create your first project') }}
                    </p>
                    <p class="text-muted-foreground text-sm">
                        {{ $t('app.track your working time per project and keep an eye on costs.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <ConfirmationDialog />
</template>
