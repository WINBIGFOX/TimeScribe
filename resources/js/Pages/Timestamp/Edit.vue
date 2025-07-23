<script lang="ts" setup>
import SheetDialog from '@/Components/dialogs/SheetDialog.vue'
import { TimeSelect } from '@/Components/ui-custom/time-select'
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import { Textarea } from '@/Components/ui/textarea'
import { Project, Timestamp } from '@/types'
import { Head, router, useForm } from '@inertiajs/vue3'
import { BriefcaseBusiness, Coffee, MoveRight } from 'lucide-vue-next'
import moment from 'moment/min/moment-with-locales'

const props = defineProps<{
    min_time: string
    max_time?: string
    submit_route: string
    timestamp: Timestamp
    projects: Project[]
}>()

const form = useForm({
    started_at: moment(props.timestamp.started_at.date, 'YYYY-MM-DD HH:mm:ss').format('HH:mm'),
    ended_at: props.timestamp.ended_at
        ? moment(props.timestamp.ended_at.date, 'YYYY-MM-DD HH:mm:ss').format('HH:mm')
        : undefined,
    type: props.timestamp.type,
    description: props.timestamp.description,
    project_id: (props.timestamp.project?.id ?? '0') as string | number | undefined
})

const submit = () => {
    router.flushAll()
    form.transform((data) => {
        if (data.project_id === '0' || data.type !== 'work') {
            data.project_id = undefined
        }
        return data
    }).patch(props.submit_route, {
        preserveScroll: true,
        preserveState: 'errors'
    })
}
const destroy = () => {
    router.delete(
        route('timestamp.destroy', {
            timestamp: props.timestamp.id
        }),
        {
            data: {
                confirm: false
            },
            preserveScroll: true,
            preserveState: 'errors'
        }
    )
}
</script>

<template>
    <Head title="Timestamp Edit" />
    <SheetDialog
        :close="$t('app.cancel')"
        :destroy="$t('app.remove')"
        :loading="form.processing"
        :submit="$t('app.save')"
        @destroy="destroy"
        @submit="submit"
    >
        <template #title>
            <div class="flex items-center gap-2">
                <div
                    :class="{
                        'bg-primary': form.type === 'work',
                        'bg-pink-400': form.type === 'break'
                    }"
                    class="text-primary-foreground flex size-8 shrink-0 items-center justify-center rounded-md"
                >
                    <BriefcaseBusiness class="size-5" v-if="form.type === 'work'" />
                    <Coffee class="size-5" v-if="form.type === 'break'" />
                </div>
                <Select :disabled="!props.timestamp.ended_at" v-model="form.type">
                    <SelectTrigger>
                        <SelectValue class="text-base" placeholder="Type">
                            {{ $t(form.type === 'work' ? 'app.work hours' : 'app.break time') }}
                        </SelectValue>
                    </SelectTrigger>
                    <SelectContent>
                        <SelectGroup>
                            <SelectItem value="work">
                                {{ $t('app.work hours') }}
                            </SelectItem>
                            <SelectItem value="break">
                                {{ $t('app.break time') }}
                            </SelectItem>
                        </SelectGroup>
                    </SelectContent>
                </Select>
            </div>
        </template>

        <div class="flex flex-col gap-2 py-4">
            <span class="text-sm leading-none font-medium">{{ $t('app.time span') }}</span>
            <div class="flex items-center gap-4">
                <TimeSelect
                    :label="$t('app.start at:')"
                    :max="form.ended_at ?? max_time"
                    :min="min_time"
                    :twelve-hour-format="['en_US'].includes($page.props.locale)"
                    v-model="form.started_at"
                />
                <MoveRight class="text-muted-foreground size-4" />
                <TimeSelect
                    :label="$t('app.end at:')"
                    :max="max_time"
                    :min="form.started_at"
                    :twelve-hour-format="['en_US'].includes($page.props.locale)"
                    v-if="max_time && props.timestamp.ended_at"
                    v-model="form.ended_at"
                />
                <div class="bg-muted text-muted-foreground mx-1 flex items-center gap-2 rounded-lg px-3 py-1" v-else>
                    <div class="size-3 shrink-0 animate-pulse rounded-full bg-red-500" />
                    {{ $t('app.now') }}
                </div>
            </div>
            <div class="text-destructive text-sm" v-if="form.errors.started_at">
                {{ form.errors.started_at }}
            </div>
            <div class="text-destructive text-sm" v-if="form.errors.ended_at">
                {{ form.errors.ended_at }}
            </div>
        </div>
        <div class="flex flex-col gap-2 py-4">
            <span class="text-sm leading-none font-medium">{{ $t('app.notes') }}</span>
            <Textarea class="h-40" v-model="form.description" />
        </div>
        <div class="flex flex-col gap-2 py-4" v-if="(props.projects.length || form.project_id) && form.type === 'work'">
            <span class="text-sm leading-none font-medium">{{ $t('app.project') }}</span>
            <Select v-model="form.project_id">
                <SelectTrigger class="w-full whitespace-normal">
                    <div>
                        <SelectValue class="line-clamp-1" :placeholder="$t('app.project')" />
                    </div>
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="0">-</SelectItem>
                    <SelectItem
                        v-if="props.timestamp.project && props.timestamp.project.archived_at"
                        :value="props.timestamp.project.id"
                    >
                        {{ props.timestamp.project.icon }} {{ props.timestamp.project.name }}
                    </SelectItem>
                    <SelectItem v-for="project in props.projects" :key="project.id" :value="project.id">
                        {{ project.icon }} {{ project.name }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>
        <div class="flex flex-col gap-2 py-4" v-if="props.timestamp.source">
            <span class="text-sm leading-none font-medium">
                {{ $t('app.imported from :name', { name: props.timestamp.source }) }}
            </span>
        </div>
    </SheetDialog>
</template>
