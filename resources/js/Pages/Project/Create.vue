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
import 'vue3-emoji-picker/css'

defineOptions({
    layout: BasicLayout
})

const props = defineProps<{
    submit_route: string
}>()

const form = useForm({
    name: '',
    description: '',
    color: '',
    icon: '',
    hourly_rate: 0,
    currency: 'USD'
})

const submit = () => {
    router.flushAll()
    form.post(props.submit_route, {
        preserveScroll: true,
        preserveState: true
    })
}
</script>

<template>
    <Head title="Project Create" />
    <SheetDialog :close="$t('app.cancel')" :submit="$t('app.save')" :title="$t('app.create project')" @submit="submit">
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
