<script setup lang="ts">
import { Button } from '@/Components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/Components/ui/dropdown-menu'
import { getCurrencySymbol, secToFormat } from '@/lib/utils'
import { Project } from '@/types'
import { Link, router } from '@inertiajs/vue3'
import { Archive, Coins, Edit, MoreHorizontal, Timer } from 'lucide-vue-next'

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
</script>

<template>
    <div class="flex flex-col">
        <!-- Project -->
        <div
            :style="'--project-color: ' + (props.project.color ?? '#000000')"
            class="b-2 rounded-md border-l-6 border-l-[var(--project-color)] bg-[var(--project-color)]/10 p-4 dark:bg-[var(--project-color)]/20"
        >
            <div class="flex items-center justify-between tabular-nums">
                <div class="flex flex-1 items-center gap-2">
                    <span class="text-2xl" v-if="props.project.icon">{{ props.project.icon }}</span>
                    <div class="flex-1 font-medium">{{ props.project.name }}</div>

                    <div
                        class="flex w-24 shrink-0 items-center gap-1"
                        v-if="props.project.billable_amount && props.project.currency"
                    >
                        <Coins class="text-muted-foreground size-4" />
                        <span
                            class="*:text-muted-foreground flex items-center gap-1 leading-none font-medium *:text-xs"
                            v-html="
                                props.project.billable_amount
                                    .toLocaleString($page.props.js_locale, {
                                        style: 'currency',
                                        currency: props.project.currency,
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    })
                                    .replace(
                                        getCurrencySymbol($page.props.js_locale, props.project.currency),
                                        '<span>$&amp;</span>'
                                    )
                                    .replace('&nbsp;', '')
                            "
                        >
                        </span>
                    </div>
                    <div class="flex w-24 shrink-0 items-center gap-1" v-if="props.project.work_time">
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
                <DropdownMenu>
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
            </div>
            <div class="text-muted-foreground mt-2 text-sm" v-if="props.project.description">
                {{ props.project.description }}
            </div>
            <div class="text-muted-foreground mt-1 text-sm" v-if="props.project.hourly_rate && props.project.currency">
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
        </div>
    </div>
</template>
