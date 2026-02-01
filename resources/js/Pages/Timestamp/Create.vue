<script lang="ts" setup>
import SheetDialog from '@/Components/dialogs/SheetDialog.vue'
import { TimeSelect } from '@/Components/ui-custom/time-select'
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import { Textarea } from '@/Components/ui/textarea'
import { Project } from '@/types'
import { Head, router, useForm } from '@inertiajs/vue3'
import { BriefcaseBusiness, Coffee, MoveRight } from 'lucide-vue-next'

const props = defineProps<{
    min_time: string
    max_time: string
    start_time?: string
    end_time?: string
    submit_route: string
    projects: Project[]
    type?: 'work' | 'break'
}>()

const form = useForm({
    started_at: props.start_time ?? props.min_time,
    ended_at: props.end_time ?? props.max_time,
    type: props.type ?? 'work',
    description: '',
    project_id: '0' as string | number | undefined
})

const submit = () => {
    router.flushAll()
    form.transform((data) => {
        if (data.project_id === '0' || data.type !== 'work') {
            data.project_id = undefined
        }
        return data
    }).post(props.submit_route, {
        preserveScroll: true,
        preserveState: 'errors'
    })
}
</script>

<template>
    <Head title="Timestamp Create" />
    <SheetDialog :close="$t('app.cancel')" :loading="form.processing" :submit="$t('app.add time')" @submit="submit">
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
                <Select :disabled="!!props.type" v-model="form.type">
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
                    :max="form.ended_at"
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
                    v-model="form.ended_at"
                />
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
        <div class="flex flex-col gap-2 py-4" v-if="props.projects.length && form.type === 'work'">
            <span class="text-sm leading-none font-medium">{{ $t('app.project') }}</span>
            <Select v-model="form.project_id">
                <SelectTrigger class="w-full whitespace-normal">
                    <div>
                        <SelectValue class="line-clamp-1" :placeholder="$t('app.project')" />
                    </div>
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="0">-</SelectItem>
                    <SelectItem v-for="project in props.projects" :key="project.id" :value="project.id">
                        {{ project.icon }} {{ project.name }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>
    </SheetDialog>
</template>
