<script lang="ts" setup>
import ConfirmationDialog from '@/Components/dialogs/ConfirmationDialog.vue'
import ProjectListItem from '@/Components/ProjectListItem.vue'
import { EmptyState } from '@/Components/ui-custom/empty-state'
import { PageHeader } from '@/Components/ui-custom/page-header'
import { Button } from '@/Components/ui/button'
import { Project } from '@/types'
import { Head, Link, usePage, usePoll } from '@inertiajs/vue3'
import { Archive, ChevronDown, ChevronUp, Plus, Tag } from 'lucide-vue-next'
import { computed, ref, watch } from 'vue'

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

const showArchived = ref(false)
const activeProjects = computed(() => props.projects.filter((project) => !project.archived_at))
const archivedProjects = computed(() => props.projects.filter((project) => project.archived_at))
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

    <div class="flex grow flex-col gap-4">
        <div class="flex grow flex-col gap-2" v-if="activeProjects.length">
            <template v-for="project in activeProjects" :key="project.id">
                <ProjectListItem
                    v-if="!project.archived_at"
                    :is-current="project.id === props.current_project_id"
                    :project="project"
                />
            </template>
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
        <div v-if="archivedProjects.length">
            <Button @click="showArchived = !showArchived" variant="ghost" size="sm">
                <Archive />
                <ChevronDown v-if="!showArchived" />
                <ChevronUp v-else />
            </Button>
        </div>
        <div class="flex flex-col gap-2" v-if="archivedProjects.length && showArchived">
            <template v-for="project in archivedProjects" :key="project.id">
                <ProjectListItem :is-current="project.id === props.current_project_id" :project="project" />
            </template>
        </div>
    </div>

    <ConfirmationDialog />
</template>
