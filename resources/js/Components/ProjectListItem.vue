<script setup lang="ts">
import { Button } from '@/Components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/Components/ui/dropdown-menu'
import { secToFormat } from '@/lib/utils'
import { Project } from '@/types'
import { Link, router } from '@inertiajs/vue3'
import {
    Archive,
    ArchiveRestore,
    CircleCheckBig,
    CircleEqual,
    CircleSlash,
    Edit,
    FolderOpen,
    MoreHorizontal,
    Timer
} from 'lucide-vue-next'
import { computed } from 'vue'

const props = defineProps<{
    project: Project
    isCurrent?: boolean
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

const calcAmount = (paid: boolean) => {
    if (!props.project.hourly_rate) {
        return undefined
    }
    const duration =
        props.project.timestamps
            ?.filter((t) => t.paid === paid)
            .reduce((partialSum, a) => partialSum + a.duration, 0) ?? 0
    return ((duration / 60) * props.project.hourly_rate) / 60
}

const amountPaid = computed(() => calcAmount(true))
const amountOpen = computed(() => calcAmount(false))
</script>

<template>
    <div class="flex flex-col">
        <!-- Project -->
        <div
            :style="'--project-color: ' + (props.project.color ?? '#000000')"
            class="b-2 rounded-md border-l-6 border-l-(--project-color) bg-(--project-color)/10 p-4 dark:bg-(--project-color)/20"
        >
            <div class="flex items-center justify-between tabular-nums">
                <div class="flex flex-1 items-center gap-2">
                    <span class="text-2xl" v-if="props.project.icon">{{ props.project.icon }}</span>
                    <div class="flex-1 pr-2 font-medium">{{ props.project.name }}</div>
                    <div
                        class="flex shrink-0 items-center gap-1"
                        v-if="props.project.work_time && (!props.project.hourly_rate || !props.project.currency)"
                    >
                        <Timer class="text-muted-foreground size-4" />
                        <span class="font-medium">
                            {{
                                props.project.work_time > 59
                                    ? secToFormat(props.project.work_time, false, true, true)
                                    : props.project.work_time
                            }}
                        </span>
                        <span class="text-muted-foreground text-xs">
                            {{ props.project.work_time > 59 ? $t('app.h') : $t('app.s') }}
                        </span>
                    </div>
                </div>
                <Button
                    :as="Link"
                    :href="route('project.show', { project: props.project.id })"
                    size="sm"
                    variant="ghost"
                    preserve-scroll
                    preserve-state
                >
                    <FolderOpen />
                </Button>
                <DropdownMenu v-if="!props.project.archived_at">
                    <DropdownMenuTrigger as="div">
                        <Button class="h-8 w-8" size="icon" variant="ghost">
                            <MoreHorizontal class="h-4 w-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent>
                        <DropdownMenuItem
                            :as="Link"
                            :href="route('project.edit', { project: props.project.id })"
                            prefetch
                            preserve-scroll
                            preserve-state
                        >
                            <Edit class="mr-2 h-4 w-4" />
                            {{ $t('app.edit :item', { item: $t('app.project') }) }}
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="deleteProject(project)">
                            <Archive class="mr-2 h-4 w-4" />
                            {{ $t('app.archive') }}
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
                <Button
                    :as="Link"
                    :href="route('project.restore', { project: props.project.id })"
                    size="sm"
                    method="patch"
                    variant="ghost"
                    preserve-scroll
                    preserve-state
                    v-else
                >
                    <ArchiveRestore />
                </Button>
            </div>
            <div class="text-muted-foreground mt-2 text-sm" v-if="props.project.description">
                {{ props.project.description }}
            </div>
            <div class="flex items-center justify-between" v-if="props.project.hourly_rate && props.project.currency">
                <div class="text-muted-foreground mt-1 text-sm">
                    {{ $t('app.hourly rate') }}:
                    {{
                        props.project.hourly_rate.toLocaleString($page.props.js_locale, {
                            style: 'currency',
                            currency: props.project.currency,
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 2
                        })
                    }}
                </div>
                <div class="flex gap-4">
                    <div class="flex items-center gap-2 text-stone-500">
                        <CircleSlash class="size-4" />
                        <span class="text-sm tabular-nums">
                            {{
                                amountOpen?.toLocaleString($page.props.js_locale, {
                                    style: 'currency',
                                    currency: props.project.currency,
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                })
                            }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2 text-emerald-500">
                        <CircleCheckBig class="size-4" />
                        <span class="text-sm tabular-nums">
                            {{
                                amountPaid?.toLocaleString($page.props.js_locale, {
                                    style: 'currency',
                                    currency: props.project.currency,
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                })
                            }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2 text-blue-500">
                        <CircleEqual class="size-4" />
                        <span class="text-sm tabular-nums">
                            {{
                                props.project.billable_amount?.toLocaleString($page.props.js_locale, {
                                    style: 'currency',
                                    currency: props.project.currency,
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                })
                            }}
                        </span>
                    </div>
                    <div class="text-muted-foreground flex items-center gap-2">
                        <Timer class="size-4" />
                        <span class="text-sm tabular-nums">
                            {{
                                (props.project.work_time ?? 0) > 59
                                    ? secToFormat(props.project.work_time ?? 0, false, true, true)
                                    : (props.project.work_time ?? 0).toFixed(0)
                            }}
                            {{ (props.project.work_time ?? 0) > 59 ? $t('app.h') : $t('app.s') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
