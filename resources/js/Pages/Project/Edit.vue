<script lang="ts" setup>
import { Head, router, useForm } from '@inertiajs/vue3'

import SheetDialog from '@/Components/dialogs/SheetDialog.vue'
import { ColorSelect } from '@/Components/ui-custom/color-select'
import { EmojiSelect } from '@/Components/ui-custom/emoji-select'
import { Input } from '@/Components/ui/input'
import {
    NumberField,
    NumberFieldContent,
    NumberFieldDecrement,
    NumberFieldIncrement,
    NumberFieldInput
} from '@/Components/ui/number-field'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import { Textarea } from '@/Components/ui/textarea'
import BasicLayout from '@/Layouts/BasicLayout.vue'
import { Enum, Project } from '@/types'
import 'vue3-emoji-picker/css'

defineOptions({
    layout: BasicLayout
})

const props = defineProps<{
    submit_route: string
    project: Project
    currencies: Enum
}>()

const form = useForm({
    name: props.project.name,
    description: props.project.description || '',
    color: props.project.color,
    icon: props.project.icon || '',
    hourly_rate: props.project.hourly_rate || 0,
    currency: props.project.currency || undefined
})

const submit = () => {
    router.flushAll()
    form.patch(props.submit_route, {
        preserveScroll: true,
        preserveState: true
    })
}
</script>

<template>
    <Head title="Projekt bearbeiten" />
    <SheetDialog
        :close="$t('app.cancel')"
        :submit="$t('app.save')"
        :title="$t('app.edit :item', { item: $t('app.project') })"
        @submit="submit"
    >
        <div class="flex flex-col gap-2 py-4">
            <span class="text-sm leading-none font-medium">{{ $t('app.project name') }}</span>
            <Input v-model="form.name" />
            <div class="text-destructive text-sm" v-if="form.errors.name">
                {{ form.errors.name }}
            </div>
        </div>
        <div class="flex flex-col gap-2 py-4">
            <span class="text-sm leading-none font-medium">{{ $t('app.description') }}</span>
            <Textarea class="h-10" v-model="form.description" />
            <div class="text-destructive text-sm" v-if="form.errors.description">
                {{ form.errors.description }}
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div class="flex flex-col gap-2 py-4">
                <span class="text-sm leading-none font-medium">{{ $t('app.color') }}</span>
                <ColorSelect required v-model="form.color" />
            </div>
            <div class="flex flex-col gap-2 py-4">
                <span class="text-sm leading-none font-medium">{{ $t('app.emoji') }}</span>
                <EmojiSelect v-model="form.icon" />
            </div>
            <div class="text-destructive col-span-2 text-sm" v-if="form.errors.color">
                {{ form.errors.color }}
            </div>
            <div class="text-destructive col-span-2 text-sm" v-if="form.errors.icon">
                {{ form.errors.icon }}
            </div>
        </div>
        <div class="flex flex-col gap-2 py-4">
            <span class="text-sm leading-none font-medium">{{ $t('app.hourly rate') }}</span>
            <div class="flex gap-2">
                <Select v-model="form.currency">
                    <SelectTrigger class="w-32">
                        <SelectValue :placeholder="$t('app.select currency')" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="(value, key) in props.currencies" :key="key" :value="key">
                            {{ value }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <NumberField
                    :format-options="{
                        style: form.currency ? 'currency' : 'decimal',
                        currency: form.currency,
                        currencyDisplay: 'symbol',
                        currencySign: 'accounting',
                        minimumFractionDigits: 0
                    }"
                    :locale="$page.props.js_locale"
                    :min="0"
                    :step="1"
                    class="w-32"
                    v-model.lazy="form.hourly_rate"
                >
                    <NumberFieldContent>
                        <NumberFieldDecrement />
                        <NumberFieldInput />
                        <NumberFieldIncrement />
                    </NumberFieldContent>
                </NumberField>
            </div>
            <div class="text-destructive col-span-2 text-sm" v-if="form.errors.currency">
                {{ form.errors.currency }}
            </div>
            <div class="text-destructive col-span-2 text-sm" v-if="form.errors.hourly_rate">
                {{ form.errors.hourly_rate }}
            </div>
        </div>
    </SheetDialog>
</template>
