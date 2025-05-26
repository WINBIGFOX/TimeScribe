<script lang="ts" setup>
import ConfirmationDialog from '@/Components/dialogs/ConfirmationDialog.vue'
import { Button } from '@/Components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/Components/ui/dropdown-menu'
import { Project } from '@/types'
import { Head, Link, router } from '@inertiajs/vue3'
import { CalendarPlus, Edit, MoreHorizontal, Plus, Trash } from 'lucide-vue-next'
import { computed } from 'vue'

const props = defineProps<{
    projects: Project[]
}>()

// Filter projects (projects without parent)
const projects = computed(() => {
    return props.projects.filter(project => !project.parent)
})

// Delete project
const deleteProject = (project: Project) => {
    router.delete(route('project.destroy', { project: project.id }), {
        preserveScroll: true
    })
}

// Edit project
const editProject = (project: Project) => {
    router.get(route('project.edit', { project: project.id }), {
        preserveScroll: true
    })
}
</script>

<template>
    <Head title="Projects and Sub-Projects" />

    <div class="mb-4 flex items-center justify-between gap-4">
        <div class="text-foreground/80 text-base font-medium">{{ $t('app.projects and sub-projects') }}</div>
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
                <CalendarPlus />
                {{ $t('app.create new project/sub-project') }}
            </Button>
        </div>
    </div>

    <div class="flex grow flex-col gap-6 overflow-y-auto pb-6">
        <!-- Projects -->
        <div v-for="project in projects" :key="project.id" class="flex flex-col">
            <!-- Project -->
            <div class="mb-2 rounded-md border border-border p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div
                            :style="{ backgroundColor: project.color }"
                            class="flex h-8 w-8 items-center justify-center rounded-md text-white"
                        >
                            <span v-if="project.icon">{{ project.icon }}</span>
                        </div>
                        <div class="font-medium">{{ project.name }}</div>
                    </div>
                    <DropdownMenu>
                        <DropdownMenuTrigger as="div">
                            <Button variant="ghost" size="icon" class="h-8 w-8">
                                <MoreHorizontal class="h-4 w-4" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent>
                            <DropdownMenuItem @click="editProject(project)">
                                <Edit class="mr-2 h-4 w-4" />
                                Edit
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="deleteProject(project)">
                                <Trash class="mr-2 h-4 w-4" />
                                Delete
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
                <div v-if="project.description" class="mt-2 text-sm text-muted-foreground">
                    {{ project.description }}
                </div>
                <div v-if="project.hourly_rate" class="mt-1 text-sm text-muted-foreground">
                    Hourly Rate: {{ project.hourly_rate }}€
                </div>
                <div class="mt-4 flex justify-between">
                    <Button
                        :as="Link"
                        :href="route('project.create', { parent_id: project.id })"
                        size="sm"
                        variant="outline"
                        class="text-xs"
                    >
                        <Plus class="mr-1 h-3 w-3" />
                        Add Sub-Project
                    </Button>
                </div>
            </div>

            <!-- Project Sub-Projects -->
            <div v-if="project.children && project.children.length > 0" class="ml-6 border-l border-border pl-4">
                <div v-for="project in project.children" :key="project.id" class="mb-2">
                    <div class="rounded-md border border-border p-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div
                                    :style="{ backgroundColor: project.color }"
                                    class="flex h-6 w-6 items-center justify-center rounded-md text-white"
                                >
                                    <span v-if="project.icon" class="text-sm">{{ project.icon }}</span>
                                </div>
                                <div class="text-sm font-medium">{{ project.name }}</div>
                            </div>
                            <DropdownMenu>
                                <DropdownMenuTrigger as="div">
                                    <Button variant="ghost" size="icon" class="h-6 w-6">
                                        <MoreHorizontal class="h-3 w-3" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent>
                                    <DropdownMenuItem @click="editProject(project)">
                                        <Edit class="mr-2 h-4 w-4" />
                                        Edit
                                    </DropdownMenuItem>
                                    <DropdownMenuItem @click="deleteProject(project)">
                                        <Trash class="mr-2 h-4 w-4" />
                                        Delete
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                        <div v-if="project.description" class="mt-1 text-xs text-muted-foreground">
                            {{ project.description }}
                        </div>
                        <div v-if="project.hourly_rate" class="mt-1 text-xs text-muted-foreground">
                            Hourly Rate: {{ project.hourly_rate }}€
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <ConfirmationDialog />
</template>
