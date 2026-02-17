<script lang="ts" setup>
import { PageHeader } from '@/Components/ui-custom/page-header'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import { Switch } from '@/Components/ui/switch'
import { Head, router, useForm } from '@inertiajs/vue3'
import { useDebounceFn } from '@vueuse/core'
import { Coins, FileOutput, FileText, Timer } from 'lucide-vue-next'
import { watch } from 'vue'

const props = defineProps<{
    column_type: boolean
    column_description: boolean
    column_project: boolean
    column_import_source: boolean
    column_start_date: boolean
    column_start_time: boolean
    column_end_date: boolean
    column_end_time: boolean
    column_duration: boolean
    column_hourly_rate: boolean
    column_billable_amount: boolean
    column_currency: boolean
    column_paid: boolean
    pdf_paper_size: string
    pdf_orientation: string
}>()

const form = useForm({
    column_type: props.column_type,
    column_description: props.column_description,
    column_project: props.column_project,
    column_import_source: props.column_import_source,
    column_start_date: props.column_start_date,
    column_start_time: props.column_start_time,
    column_end_date: props.column_end_date,
    column_end_time: props.column_end_time,
    column_duration: props.column_duration,
    column_hourly_rate: props.column_hourly_rate,
    column_billable_amount: props.column_billable_amount,
    column_currency: props.column_currency,
    column_paid: props.column_paid,
    pdf_paper_size: props.pdf_paper_size,
    pdf_orientation: props.pdf_orientation,
})

const submit = () => {
    router.flushAll()
    form.patch(route('settings.export.update'), {
        preserveScroll: true,
        preserveState: true
    })
}

const debouncedSubmit = useDebounceFn(submit, 500)
watch(() => ({ ...form.data() }), debouncedSubmit, { deep: true })
</script>

<template>
    <Head title="Settings - Export" />
    <PageHeader :title="$t('app.data export')" />
    <div>
        <div class="flex items-start space-x-4 py-4">
            <FileText />
            <div class="flex-1 space-y-1">
                <p class="text-sm leading-none font-medium">{{ $t('app.pdf settings') }}</p>
                <p class="text-muted-foreground text-sm">
                    {{ $t('app.configure pdf export options.') }}
                </p>
                <div class="mt-4 grid grid-cols-2 gap-x-8 gap-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm">{{ $t('app.paper size') }}</span>
                        <Select v-model="form.pdf_paper_size">
                            <SelectTrigger class="w-32">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="a4">A4</SelectItem>
                                <SelectItem value="letter">{{ $t('app.letter') }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm">{{ $t('app.orientation') }}</span>
                        <Select v-model="form.pdf_orientation">
                            <SelectTrigger class="w-32">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="portrait">{{ $t('app.portrait') }}</SelectItem>
                                <SelectItem value="landscape">{{ $t('app.landscape') }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-start space-x-4 border-t py-4 pt-8">
            <FileOutput />
            <div class="flex-1 space-y-1">
                <p class="text-sm leading-none font-medium">{{ $t('app.export columns') }}</p>
                <p class="text-muted-foreground text-sm">
                    {{ $t('app.choose which columns to include in csv, excel and pdf exports.') }}
                </p>
                <div class="mt-4 grid grid-cols-2 gap-x-8 gap-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm">{{ $t('app.type') }}</span>
                        <Switch v-model="form.column_type" />
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm">{{ $t('app.description') }}</span>
                        <Switch v-model="form.column_description" />
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm">{{ $t('app.project') }}</span>
                        <Switch v-model="form.column_project" />
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm">{{ $t('app.import source') }}</span>
                        <Switch v-model="form.column_import_source" />
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-start space-x-4 border-t py-4 pt-8">
            <Timer />
            <div class="flex-1 space-y-1">
                <p class="text-sm leading-none font-medium">{{ $t('app.time columns') }}</p>
                <p class="text-muted-foreground text-sm">
                    {{ $t('app.choose which time-related columns to include.') }}
                </p>
                <div class="mt-4 grid grid-cols-2 gap-x-8 gap-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm">{{ $t('app.start date') }}</span>
                        <Switch v-model="form.column_start_date" />
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm">{{ $t('app.start time') }}</span>
                        <Switch v-model="form.column_start_time" />
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm">{{ $t('app.end date') }}</span>
                        <Switch v-model="form.column_end_date" />
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm">{{ $t('app.end time') }}</span>
                        <Switch v-model="form.column_end_time" />
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm">{{ $t('app.duration') }}</span>
                        <Switch v-model="form.column_duration" />
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-start space-x-4 border-t py-4 pt-8">
            <Coins />
            <div class="flex-1 space-y-1">
                <p class="text-sm leading-none font-medium">{{ $t('app.billing columns') }}</p>
                <p class="text-muted-foreground text-sm">
                    {{ $t('app.choose which billing-related columns to include.') }}
                </p>
                <div class="mt-4 grid grid-cols-2 gap-x-8 gap-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm">{{ $t('app.hourly rate') }}</span>
                        <Switch v-model="form.column_hourly_rate" />
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm">{{ $t('app.billable amount') }}</span>
                        <Switch v-model="form.column_billable_amount" />
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm">{{ $t('app.currency') }}</span>
                        <Switch v-model="form.column_currency" />
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm">{{ $t('app.paid') }}</span>
                        <Switch v-model="form.column_paid" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
