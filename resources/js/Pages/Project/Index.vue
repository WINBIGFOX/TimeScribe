<script lang="ts" setup>
import ConfirmationDialog from '@/Components/dialogs/ConfirmationDialog.vue'
import { Button } from '@/Components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/Components/ui/dropdown-menu'
import { secToFormat } from '@/lib/utils'
import { Project } from '@/types'
import { Head, Link, router } from '@inertiajs/vue3'
import { Archive, CalendarPlus, Edit, MoreHorizontal, Timer, Coins } from 'lucide-vue-next'

const props = defineProps<{
    projects: Project[]
}>()

// Delete project
const deleteProject = (project: Project) => {
    router.delete(route('project.destroy', { project: project.id }), {
        data: {
            confirm: false
        },
        preserveScroll: true,
        preserveState: true
    })
}

// Edit project
const editProject = (project: Project) => {
    router.get(route('project.edit', { project: project.id }), {
        preserveScroll: true,
        preserveState: true
    })
}
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
                <CalendarPlus />
                {{ $t('app.create new project') }}
            </Button>
        </div>
    </div>

    <div class="flex grow flex-col gap-2 overflow-y-auto pb-6">
        <!-- Projects -->
        <div :key="project.id" class="flex flex-col" v-for="project in props.projects">
            <!-- Project -->
            <div
                :style="'--project-color: ' + (project.color ?? '#000000')"
                class="b-2 rounded-md border-l-6 border-l-[var(--project-color)] bg-[var(--project-color)]/10 p-4 dark:bg-[var(--project-color)]/20"
            >
                <div class="flex items-center justify-between">
                    <div class="flex flex-1 items-center gap-2">
                        <span class="text-2xl" v-if="project.icon">{{ project.icon }}</span>
                        <div class="font-medium flex-1">{{ project.name }}</div>

                        <div class="flex w-24 shrink-0 items-center gap-1" v-if="project.work_time">
                            <Coins class="text-muted-foreground size-4" />
                            <span class="font-medium">
                                {{
                                    project.work_time
                                }}
                            </span>
                            <span class="text-muted-foreground text-xs">
                                €
                            </span>
                        </div>
                        <div class="flex w-24 shrink-0 items-center gap-1" v-if="project.work_time">
                            <Timer class="text-muted-foreground size-4" />
                            <span class="font-medium">
                                {{
                                    project.work_time > 59
                                        ? secToFormat(project.work_time, false, true, true)
                                        : project.work_time
                                }}
                            </span>
                            <span class="text-muted-foreground text-xs">
                                {{ project.work_time > 59 ? $t('app.h') : $t('app.s') }}
                            </span>
                        </div>
                    </div>
                    <DropdownMenu>
                        <DropdownMenuTrigger as="div">
                            <Button class="h-8 w-8" size="icon" variant="ghost">
                                <MoreHorizontal class="h-4 w-4" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent>
                            <DropdownMenuItem
                                :as="Link"
                                :href="route('project.edit', { project: project.id })"
                                prefetch
                                preserve-scroll
                                preserve-state
                            >
                                <Edit class="mr-2 h-4 w-4" />
                                Edit
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="deleteProject(project)">
                                <Archive class="mr-2 h-4 w-4" />
                                Archive
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
                <div class="text-muted-foreground mt-2 text-sm" v-if="project.description">
                    {{ project.description }}
                </div>
                <div class="text-muted-foreground mt-1 text-sm" v-if="project.hourly_rate">
                    Hourly Rate: {{ project.hourly_rate }}€
                </div>
            </div>
        </div>
    </div>

    <ConfirmationDialog />
</template>
