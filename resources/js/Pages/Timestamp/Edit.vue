<script lang="ts" setup>
import SheetDialog from '@/Components/dialogs/SheetDialog.vue'
import { TimeSelect } from '@/Components/ui-custom/time-select'
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import { Textarea } from '@/Components/ui/textarea'
import { Timestamp } from '@/types'
import { Head, router, useForm } from '@inertiajs/vue3'
import { BriefcaseBusiness, Coffee, MoveRight } from 'lucide-vue-next'
import moment from 'moment/min/moment-with-locales'

const props = defineProps<{
    min_time: string
    max_time: string
    submit_route: string
    timestamp: Timestamp
}>()

const form = useForm({
    started_at: moment(props.timestamp.started_at.date, 'YYYY-MM-DD HH:mm:ss').format('HH:mm'),
    ended_at: moment(props.timestamp.ended_at?.date, 'YYYY-MM-DD HH:mm:ss').format('HH:mm'),
    type: props.timestamp.type,
    description: props.timestamp.description
})

const submit = () => {
    form.patch(props.submit_route, {
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
                <Select v-model="form.type">
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
                    :twelve-hour-format="$page.props.locale === 'en-US'"
                    v-model="form.started_at"
                />
                <MoveRight class="text-muted-foreground size-4" />
                <TimeSelect
                    :label="$t('app.end at:')"
                    :max="max_time"
                    :min="form.started_at"
                    :twelve-hour-format="$page.props.locale === 'en-US'"
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
    </SheetDialog>
</template>
