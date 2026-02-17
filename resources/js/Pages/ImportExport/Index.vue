<script lang="ts" setup>
import DateRangePicker from '@/Components/DateRangePicker.vue'
import { PageHeader } from '@/Components/ui-custom/page-header'
import { Button } from '@/Components/ui/button'
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import { Project } from '@/types'
import { Head, useForm, Link } from '@inertiajs/vue3'
import { FileChartPie, FileText, FileType, FolderInput, FolderOutput, X } from 'lucide-vue-next'
import { ref } from 'vue'

const props = defineProps<{
    projects: Project[]
}>()

const today = new Date()
const formatDate = (d: Date) => d.toISOString().slice(0, 10)
const firstOfMonth = new Date(today.getFullYear(), today.getMonth(), 1)

const dateRangeEnabled = ref(false)
const dateRange = ref<{ start: string; end: string }>({
    start: formatDate(firstOfMonth),
    end: formatDate(today)
})

const form = useForm({
    start_date: '',
    end_date: '',
    project_id: '0'
})

const submitCsv = () => {
    form.transform((data) => ({
        ...data,
        start_date: dateRangeEnabled.value ? dateRange.value.start : undefined,
        end_date: dateRangeEnabled.value ? dateRange.value.end : undefined,
        project_id: data.project_id !== '0' ? data.project_id : undefined
    })).post(route('export.csv'), { preserveScroll: true })
}

const submitExcel = () => {
    form.transform((data) => ({
        ...data,
        start_date: dateRangeEnabled.value ? dateRange.value.start : undefined,
        end_date: dateRangeEnabled.value ? dateRange.value.end : undefined,
        project_id: data.project_id !== '0' ? data.project_id : undefined
    })).post(route('export.excel'), { preserveScroll: true })
}

const submitPdf = () => {
    form.transform((data) => ({
        ...data,
        start_date: dateRangeEnabled.value ? dateRange.value.start : undefined,
        end_date: dateRangeEnabled.value ? dateRange.value.end : undefined,
        project_id: data.project_id !== '0' ? data.project_id : undefined
    })).post(route('export.pdf'), { preserveScroll: true })
}
</script>

<template>
    <Head title="Import/Export" />
    <PageHeader :title="$t('app.import / export')" />
    <div class="mt-4 flex items-start space-x-4">
        <FolderInput />
        <div class="flex-1 space-y-1">
            <p class="text-sm leading-none font-medium">{{ $t('app.data import') }}</p>
            <p class="text-muted-foreground text-sm">
                {{ $t('app.import data from other programs. the following tools are currently supported:') }}
            </p>
            <div class="mt-4 flex gap-4">
                <Button :as="Link" :href="route('import.clockify.create')" variant="outline">
                    <svg fill="none" viewBox="0 0 384 384" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M300.8 0H83.2C37.2499 0 0 37.2499 0 83.2V300.8C0 346.75 37.2499 384 83.2 384H300.8C346.75 384 384 346.75 384 300.8V83.2C384 37.2499 346.75 0 300.8 0Z"
                            fill="#03A9F4"
                        />
                        <path
                            d="M246.804 109.764C253.517 103.043 252.072 91.7483 243.207 88.3469C230.836 83.6006 217.405 81 203.366 81C141.86 81 92 130.92 92 192.5C92 254.08 141.86 304 203.366 304C217.318 304 230.67 301.431 242.978 296.741C251.866 293.353 253.326 282.036 246.6 275.303C242.242 270.94 235.647 269.922 229.799 271.868C221.51 274.627 212.645 276.121 203.431 276.121C157.301 276.121 119.906 238.681 119.906 192.496C119.906 146.311 157.301 108.871 203.431 108.871C212.711 108.871 221.638 110.386 229.979 113.183C235.831 115.146 242.44 114.134 246.804 109.764Z"
                            fill="white"
                        />
                        <path
                            d="M219 193C219 200.732 212.732 207 205 207C197.268 207 191 200.732 191 193C191 185.268 197.268 179 205 179C212.732 179 219 185.268 219 193Z"
                            fill="white"
                        />
                        <path
                            d="M227.792 171.667C222.736 166.654 222.736 158.526 227.792 153.513L269.9 111.76C274.956 106.747 283.153 106.747 288.208 111.76C293.264 116.773 293.264 124.9 288.208 129.913L246.1 171.667C241.044 176.68 232.847 176.68 227.792 171.667Z"
                            fill="white"
                        />
                        <path
                            d="M227.792 213.333C222.736 218.346 222.736 226.474 227.792 231.487L269.9 273.24C274.956 278.253 283.153 278.253 288.208 273.24C293.264 268.227 293.264 260.1 288.208 255.087L246.1 213.333C241.044 208.32 232.847 208.32 227.792 213.333Z"
                            fill="white"
                        />
                    </svg>
                    Clockify
                </Button>
            </div>
        </div>
    </div>
    <div class="mt-4 flex items-start space-x-4 border-t py-4 pt-8">
        <FolderOutput />
        <div class="flex-1 space-y-1">
            <p class="text-sm leading-none font-medium">
                {{ $t('app.data export') }}
            </p>
            <p class="text-muted-foreground text-sm">
                {{
                    $t(
                        'app.export your data from timescribe as a csv or excel file for further processing or documentation.'
                    )
                }}
            </p>
            <div class="mt-4 space-y-3">
                <div class="flex flex-wrap items-end gap-4">
                    <div class="flex flex-col gap-2">
                        <span class="text-sm leading-none font-medium">{{ $t('app.select the time period you want to export.') }}</span>
                        <div class="flex items-center gap-2">
                            <DateRangePicker v-if="dateRangeEnabled" v-model="dateRange" />
                            <Button
                                v-if="!dateRangeEnabled"
                                variant="outline"
                                class="min-w-[250px] justify-start font-normal"
                                @click="dateRangeEnabled = true"
                            >
                                {{ $t('app.all time') }}
                            </Button>
                            <Button
                                v-if="dateRangeEnabled"
                                variant="ghost"
                                size="icon"
                                @click="dateRangeEnabled = false"
                            >
                                <X class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2" v-if="props.projects.length">
                        <span class="text-sm leading-none font-medium">{{ $t('app.project') }}</span>
                        <Select v-model="form.project_id">
                            <SelectTrigger class="min-w-[250px] w-fit">
                                <SelectValue :placeholder="$t('app.all projects')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem value="0">{{ $t('app.all projects') }}</SelectItem>
                                    <SelectItem v-for="project in props.projects" :key="project.id" :value="String(project.id)">
                                        {{ project.icon }} {{ project.name }}
                                    </SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                    </div>
                </div>
                <div class="flex gap-4">
                    <Button class="flex-1" variant="outline" @click="submitCsv" :disabled="form.processing">
                        <FileType />
                        {{ $t('app.export as csv file') }}
                    </Button>
                    <Button class="flex-1" variant="outline" @click="submitExcel" :disabled="form.processing">
                        <FileChartPie />
                        {{ $t('app.export as excel file') }}
                    </Button>
                    <Button class="flex-1" variant="outline" @click="submitPdf" :disabled="form.processing">
                        <FileText />
                        {{ $t('app.export as pdf file') }}
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>
