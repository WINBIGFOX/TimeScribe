<script lang="ts" setup>
import ConfirmationDialog from '@/Components/dialogs/ConfirmationDialog.vue'
import ProjectListItem from '@/Components/ProjectListItem.vue'
import { EmptyState } from '@/Components/ui-custom/empty-state'
import { PageHeader } from '@/Components/ui-custom/page-header'
import { Button } from '@/Components/ui/button'
import { Project } from '@/types'
import { Head, Link, usePage, usePoll } from '@inertiajs/vue3'
import { Plus, Tag } from 'lucide-vue-next'
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

    <PageHeader :title="$t('app.projects')">
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
    </PageHeader>

    <div class="flex grow flex-col gap-2 overflow-y-auto pb-6" v-if="props.projects.length">
        <ProjectListItem
            :is-current="project.id === props.current_project_id"
            :key="project.id"
            :project="project"
            v-for="project in props.projects"
        />
    </div>
    <div class="flex grow items-center justify-center" v-else>
        <EmptyState
            :action-href="route('project.create')"
            :action-label="$t('app.create new project')"
            :icon="Tag"
            :title="$t('app.start with your first project')"
            :description="$t('app.organize tasks track time and keep costs in view')"
        />
    </div>

    <ConfirmationDialog />
</template>
