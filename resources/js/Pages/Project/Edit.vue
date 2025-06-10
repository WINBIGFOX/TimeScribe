<script lang="ts" setup>
import { Head, router, useForm } from '@inertiajs/vue3'

import SheetDialog from '@/Components/dialogs/SheetDialog.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import BasicLayout from '@/Layouts/BasicLayout.vue'
import { Project } from '@/types'
import EmojiPicker from 'vue3-emoji-picker'
import 'vue3-emoji-picker/css'
import {
    NumberField,
    NumberFieldContent,
    NumberFieldDecrement,
    NumberFieldIncrement,
    NumberFieldInput
} from '@/Components/ui/number-field'
import { EmojiSelect } from '@/Components/ui-custom/emoji-select'
import { ColorSelect } from '@/Components/ui-custom/color-select'
import { Textarea } from '@/Components/ui/textarea'

defineOptions({
    layout: BasicLayout
})

const props = defineProps<{
    submit_route: string
    project: Project
}>()

const form = useForm({
    name: props.project.name,
    description: props.project.description || '',
    color: props.project.color,
    icon: props.project.icon || '',
    hourly_rate: props.project.hourly_rate || 0,
    currency: 'USD'
})

const submit = () => {
    router.flushAll()
    form.patch(props.submit_route, {
        preserveScroll: true,
        preserveState: true
    })
}

// Set emoji as icon
const setEmoji = (emoji: { i: string }) => {
    form.icon = emoji.i
}
</script>

<template>
    <Head title="Projekt bearbeiten" />
    <SheetDialog
        :close="$t('app.cancel')"
        :submit="$t('app.save')"
        :title="$t('app.edit project')"
        @submit="submit"
    >
        <div class="flex flex-col gap-2 py-4">
            <span class="text-sm leading-none font-medium">{{ $t('app.project name') }}</span>
            <Input v-model="form.name" />
        </div>
        <div class="flex flex-col gap-2 py-4">
            <span class="text-sm leading-none font-medium">{{ $t('app.description') }}</span>
            <Textarea class="h-10" v-model="form.description" />
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div class="flex flex-col gap-2 py-4">
                <span class="text-sm leading-none font-medium">{{ $t('app.color') }}</span>
                <ColorSelect required v-model="form.color" />
            </div>
            <div class="flex flex-col gap-2 py-4">
                <span class="text-sm leading-none font-medium">{{ $t('app.color') }}</span>
                <EmojiSelect v-model="form.icon" />
            </div>
        </div>
        <div class="flex flex-col gap-2 py-4">
            <span class="text-sm leading-none font-medium">{{ $t('app.hourly rate') }}</span>
            <div class="flex gap-2">
                <Select v-model="form.currency">
                    <SelectTrigger class="w-24">
                        <SelectValue :placeholder="$t('app.select currency')" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="KRW">₩ KRW</SelectItem>
                        <SelectItem value="USD">$ USD</SelectItem>
                        <SelectItem value="EUR">€ EUR</SelectItem>
                    </SelectContent>
                </Select>
                <NumberField
                    :format-options="{
                        style: 'currency',
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
        </div>
    </SheetDialog>
</template>
