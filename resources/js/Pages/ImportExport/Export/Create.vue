<script lang="ts" setup>
import DateRangePicker from '@/Components/DateRangePicker.vue'
import SheetDialog from '@/Components/dialogs/SheetDialog.vue'
import Csv from '@/Components/icons/Csv.vue'
import Pdf from '@/Components/icons/Pdf.vue'
import Xls from '@/Components/icons/Xls.vue'
import { Field, FieldContent, FieldGroup, FieldLabel, FieldSet } from '@/Components/ui/field'
import { RadioGroup, RadioGroupItem } from '@/Components/ui/radio-group'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import { Switch } from '@/Components/ui/switch'
import { Project } from '@/types'
import { Head, useForm } from '@inertiajs/vue3'
import {
    BriefcaseBusiness,
    Calendar,
    CalendarRange,
    Clock,
    Coffee,
    Columns3,
    DollarSign,
    File,
    GripVertical,
    Proportions,
    Ratio,
    SquareCheck,
    Tag,
    Type,
    Cross,
    TreePalm,
    Drama
} from '@lucide/vue'
import { computed } from 'vue'
import Draggable from 'vuedraggable'

const props = defineProps<{
    exportType: 'csv' | 'pdf' | 'excel'
    projects: Project[]
    exportColumns: Record<
        string,
        {
            label: string
            is_visible: boolean
            type: string
            key: string
        }
    >
    pdfOrientation?: string
    pdfPaperSize?: string
    types?: string[]
    submit_route: string
}>()

const form = useForm({
    export_type: props.exportType,
    export_columns: Object.values(props.exportColumns),
    projects: [],
    pdf_paper_size: props.pdfPaperSize ?? ('a4' as string | undefined),
    pdf_orientation: props.pdfOrientation ?? ('Landscape' as string | undefined),
    types: props.types ?? ['work', 'break'],
    file_name_format: '<PROJECT>_<NOW_DATE>',
    date_range: undefined as { start: string; end: string } | undefined
})

const isColumnVisible = (key: string): boolean => {
    return form.export_columns.find((item) => item.key === key)?.is_visible ?? true
}

const typeIconMapping = {
    time: Clock,
    date: Calendar,
    string: Type,
    currency: DollarSign,
    boolean: SquareCheck
}

const actionLabel = () => {
    switch (form.export_type) {
        case 'csv':
            return 'app.export csv file'
        case 'pdf':
            return 'app.export pdf file'
        case 'excel':
            return 'app.export excel file'
    }
}

const submit = () => {
    form.transform((formObject) => {
        if (formObject.export_type !== 'pdf') {
            delete formObject.pdf_paper_size
            delete formObject.pdf_orientation
        }
        return formObject
    }).post(props.submit_route, {
        preserveScroll: true,
        preserveState: 'errors'
    })
}

const isDisabled = computed((): boolean => {
    if (form.types.length === 0) {
        return true
    }
    if (form.export_columns.filter((column) => column.is_visible).length === 0) {
        return true
    }
    return false
})
</script>

<template>
    <Head :title="`Export Create ${props.exportType.toUpperCase()}`" />
    <SheetDialog
        :close="$t('app.cancel')"
        :disabled="isDisabled"
        :loading="form.processing"
        :submit="$t(actionLabel())"
        :title="$t(actionLabel())"
        @submit="submit"
        size="lg"
    >
        <div class="flex grow gap-4 overflow-hidden">
            <div class="flex flex-1 flex-col gap-4 overflow-y-auto not-rtl:border-r not-rtl:pr-4 rtl:border-l rtl:pl-4">
                <FieldGroup>
                    <FieldSet>
                        <FieldLabel>{{ $t('app.export format') }}</FieldLabel>
                        <RadioGroup class="flex flex-row gap-2" v-model="form.export_type">
                            <FieldLabel for="type_pdf">
                                <Field class="p-3!" orientation="horizontal">
                                    <FieldContent class="flex flex-row items-center gap-2">
                                        <Pdf class="size-6" stroke-width="1.5" />
                                        PDF
                                    </FieldContent>
                                    <RadioGroupItem id="type_pdf" value="pdf" />
                                </Field>
                            </FieldLabel>
                            <FieldLabel for="type_excel">
                                <Field class="p-3!" orientation="horizontal">
                                    <FieldContent class="flex flex-row items-center gap-2">
                                        <Xls class="size-6" stroke-width="1.5" />
                                        Excel
                                    </FieldContent>
                                    <RadioGroupItem id="type_excel" value="excel" />
                                </Field>
                            </FieldLabel>
                            <FieldLabel for="type_csv">
                                <Field class="p-3!" orientation="horizontal">
                                    <FieldContent class="flex flex-row items-center gap-2">
                                        <Csv class="size-6" stroke-width="1.5" />
                                        CSV
                                    </FieldContent>
                                    <RadioGroupItem id="type_csv" value="csv" />
                                </Field>
                            </FieldLabel>
                        </RadioGroup>
                    </FieldSet>
                </FieldGroup>
                <hr />

                <div class="flex items-center gap-4">
                    <CalendarRange />
                    <div class="flex flex-1 items-center gap-4">
                        <p class="flex-1 text-sm leading-none font-medium">
                            {{ $t('app.date range') }}
                        </p>
                        <DateRangePicker
                            :placeholder="$t('app.all time')"
                            class="w-2/3"
                            clearable
                            v-model="form.date_range"
                        />
                    </div>
                </div>

                <hr />
                <template v-if="form.export_type === 'pdf'">
                    <div class="flex flex-col gap-4">
                        <div class="text-muted-foreground -mb-2 text-xs font-medium">
                            {{ $t('app.pdf settings') }}
                        </div>
                        <div class="flex items-center gap-4">
                            <Proportions />
                            <div class="flex flex-1 items-center gap-4">
                                <p class="flex-1 text-sm leading-none font-medium">
                                    {{ $t('app.paper size') }}
                                </p>
                                <Select v-model="form.pdf_paper_size">
                                    <SelectTrigger class="w-2/3">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="a4">A4</SelectItem>
                                        <SelectItem value="letter">{{ $t('app.letter') }}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <Ratio />
                            <div class="flex flex-1 items-center gap-4">
                                <p class="flex-1 text-sm leading-none font-medium">
                                    {{ $t('app.orientation') }}
                                </p>
                                <Select v-model="form.pdf_orientation">
                                    <SelectTrigger class="w-2/3">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Portrait">
                                            <File />
                                            {{ $t('app.portrait') }}
                                        </SelectItem>
                                        <SelectItem value="Landscape">
                                            <File class="-rotate-90" />
                                            {{ $t('app.landscape') }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </div>
                    <hr />
                </template>
                <div class="flex flex-col gap-4">
                    <div class="text-muted-foreground -mb-2 text-xs font-medium">{{ $t('app.export filter') }}</div>
                    <div class="flex items-center gap-4" v-if="props.projects.length">
                        <Tag />
                        <div class="flex flex-1 items-center gap-4">
                            <p class="flex-1 text-sm leading-none font-medium">
                                {{ $t('app.projects') }}
                            </p>
                            <Select multiple v-model="form.projects">
                                <SelectTrigger
                                    class="min-h-9 w-2/3 py-1.5 whitespace-normal data-[size=default]:h-auto"
                                >
                                    <SelectValue :placeholder="$t('app.all projects')" class="py-px text-left" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem :key="project.id" :value="project.id" v-for="project in props.projects">
                                        {{ project.icon }}&nbsp;{{ project.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 overflow-hidden">
                        <Clock class="shrink-0" />
                        <div class="flex flex-1 items-center gap-4 overflow-hidden">
                            <p class="flex-1 text-sm leading-none font-medium">
                                {{ $t('app.type') }}
                            </p>
                            <Select multiple v-model="form.types">
                                <SelectTrigger class="w-2/3">
                                    <SelectValue :placeholder="$t('app.type')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="work">
                                        <BriefcaseBusiness />
                                        {{ $t('app.work hours') }}
                                    </SelectItem>
                                    <SelectItem value="break">
                                        <Coffee />
                                        {{ $t('app.break time') }}
                                    </SelectItem>
                                    <SelectItem value="sick">
                                        <Cross />
                                        {{ $t('app.sick days') }}
                                    </SelectItem>
                                    <SelectItem value="vacation">
                                        <TreePalm />
                                        {{ $t('app.leave days') }}
                                    </SelectItem>
                                    <SelectItem value="holiday">
                                        <Drama />
                                        {{ $t('app.holidays') }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex w-64 shrink-0 flex-col gap-2 overflow-hidden text-sm">
                <div class="flex items-start gap-2 font-medium">
                    <Columns3 class="mt-0.5 size-4 shrink-0" />
                    {{ $t('app.export columns') }}
                </div>
                <div class="flex flex-col gap-1 overflow-y-auto p-px select-auto">
                    <Draggable :animation="200" ghost-class="ghost" item-key="key" v-model="form.export_columns">
                        <template #item="{ element }">
                            <label
                                :class="{
                                    'bg-primary/10 ring-primary/60 rounded ring-1': isColumnVisible(element.key)
                                }"
                                class="[&.ghost]:ring-primary! bg-muted/40 my-1 flex items-center gap-2 rounded-md py-1 transition-[scale,box-shadow] duration-200 [-webkit-user-drag:element]! not-rtl:pr-2 rtl:pl-2 [&.ghost]:scale-95 [&.ghost]:ring-2 [&.ghost]:ring-inset"
                            >
                                <GripVertical class="text-muted-foreground handle size-4 cursor-ns-resize" />
                                <Component :is="typeIconMapping[element.type]" class="text-muted-foreground size-4" />
                                {{ element.label }}
                                <Switch class="not-rtl:ml-auto rtl:mr-auto" v-model="element.is_visible" />
                            </label>
                        </template>
                    </Draggable>
                </div>
            </div>
        </div>
    </SheetDialog>
</template>
