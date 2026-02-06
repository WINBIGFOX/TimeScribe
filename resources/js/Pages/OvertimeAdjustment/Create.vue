<script setup lang="ts">
import SheetDialog from '@/Components/dialogs/SheetDialog.vue'
import { Button } from '@/Components/ui/button'
import { Calendar } from '@/Components/ui/calendar'
import {
    Field,
    FieldContent,
    FieldDescription,
    FieldGroup,
    FieldLabel,
    FieldSet,
    FieldTitle
} from '@/Components/ui/field'
import {
    NumberField,
    NumberFieldContent,
    NumberFieldDecrement,
    NumberFieldIncrement,
    NumberFieldInput
} from '@/Components/ui/number-field'
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover'
import { RadioGroup, RadioGroupItem } from '@/Components/ui/radio-group'
import { Textarea } from '@/Components/ui/textarea'
import { cn } from '@/lib/utils'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { DateFormatter, getLocalTimeZone, parseDate, type DateValue } from '@internationalized/date'
import { CalendarIcon } from 'lucide-vue-next'
import { ref } from 'vue'
const open = defineModel('open', { default: false })

const props = defineProps<{
    date: string
}>()

const emits = defineEmits(['close'])
const effective_date = ref<DateValue | undefined>()

const page = usePage()

const df = new DateFormatter(page.props.js_locale, {
    dateStyle: 'long'
})

const form = useForm({
    type: 'absolute',
    note: '',
    seconds: 0,
    effective_date: props.date
})
effective_date.value = parseDate(form.effective_date)

const submit = () => {
    form.transform((data) => {
        if (effective_date.value) {
            data.effective_date = effective_date.value.toString()
        }
        data.seconds = data.seconds * 3600
        return data
    }).post(route('overtime-adjustment.store', { date: effective_date.value?.toString() }), {
        preserveScroll: true,
        onSuccess: () => {
            emits('close')
            form.reset()
        }
    })
}
</script>

<template>
    <SheetDialog
        :title="$t('app.add an adjustment')"
        :global="false"
        v-model:open="open"
        :close="$t('app.cancel')"
        :submit="$t('app.save')"
        @submit="submit"
    >
        <Head title="Overtime-Adjustment create" />
        <div class="flex items-center justify-between">
            <span class="text-sm leading-none font-medium">{{ $t('app.effective date') }}</span>
            <Popover>
                <PopoverTrigger as-child>
                    <Button
                        :class="
                            cn(
                                'w-[250px] justify-start text-left font-normal',
                                !effective_date && 'text-muted-foreground'
                            )
                        "
                        variant="outline"
                    >
                        <CalendarIcon class="mr-2 h-4 w-4" />
                        {{
                            effective_date
                                ? df.format(effective_date.toDate(getLocalTimeZone()))
                                : $t('app.pick a date')
                        }}
                    </Button>
                </PopoverTrigger>
                <PopoverContent class="w-auto p-0">
                    <Calendar :locale="$page.props.js_locale" fixed-weeks v-model="effective_date" />
                </PopoverContent>
            </Popover>
        </div>
        <label class="text-destructive text-xs" v-if="form.errors.effective_date">
            {{ form.errors.effective_date }}
        </label>

        <FieldGroup class="mt-4">
            <FieldSet>
                <FieldLabel>{{ $t('app.adjustment mode') }}</FieldLabel>
                <RadioGroup v-model="form.type" class="flex flex-row gap-2">
                    <FieldLabel for="type_absolute">
                        <Field orientation="horizontal">
                            <FieldContent>
                                <FieldTitle>{{ $t('app.overwrite') }}</FieldTitle>
                                <FieldDescription>{{ $t('app.sets a new overtime balance') }}</FieldDescription>
                            </FieldContent>
                            <RadioGroupItem id="type_absolute" value="absolute" />
                        </Field>
                    </FieldLabel>
                    <FieldLabel for="type_relative">
                        <Field orientation="horizontal">
                            <FieldContent>
                                <FieldTitle>{{ $t('app.offset') }}</FieldTitle>
                                <FieldDescription>
                                    {{ $t('app.offsets the adjustment against the overtime balance') }}
                                </FieldDescription>
                            </FieldContent>
                            <RadioGroupItem id="type_relative" value="relative" />
                        </Field>
                    </FieldLabel>
                </RadioGroup>
            </FieldSet>
        </FieldGroup>

        <div class="mt-4 flex items-center gap-2">
            <span class="flex-1 text-sm leading-none font-medium">{{ $t('app.hour adjustment') }}</span>
            <NumberField
                :format-options="{
                    style: 'decimal',
                    signDisplay: 'exceptZero',
                    minimumFractionDigits: 1
                }"
                :locale="$page.props.js_locale"
                :step="0.1"
                class="w-32"
                v-model="form.seconds"
            >
                <NumberFieldContent>
                    <NumberFieldDecrement />
                    <NumberFieldInput />
                    <NumberFieldIncrement />
                </NumberFieldContent>
            </NumberField>
            {{ $t('app.h') }}
        </div>
        <label class="text-destructive text-xs" v-if="form.errors.seconds">
            {{ form.errors.seconds }}
        </label>

        <div class="mt-4 flex flex-col gap-2">
            <span class="text-sm leading-none font-medium">{{ $t('app.notes') }}</span>
            <Textarea class="h-24" v-model="form.note" />
        </div>
    </SheetDialog>
</template>
