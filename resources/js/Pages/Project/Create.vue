<script lang="ts" setup>
import { Head, router, useForm } from '@inertiajs/vue3'

import SheetDialog from '@/Components/dialogs/SheetDialog.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import { Project } from '@/types'
import BasicLayout from '@/Layouts/BasicLayout.vue'
import EmojiPicker from 'vue3-emoji-picker'
import 'vue3-emoji-picker/css'

defineOptions({
    layout: BasicLayout
})

const props = defineProps<{
    submit_route: string
    parent_id?: number
    projects?: Project[]
}>()

const form = useForm({
    name: '',
    description: '',
    color: '#000000',
    icon: '',
    hourly_rate: 0,
    parent_id: props.parent_id || null
})

const submit = () => {
    router.flushAll()
    form.post(props.submit_route, {
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
    <Head title="Project Create" />
    <SheetDialog
        :close="$t('app.cancel')"
        :submit="$t('app.save')"
        :title="$t('app.create project')"
        @submit="submit"
    >
        <div class="grid gap-6 py-4">
            <div class="flex flex-col gap-2">
                <span class="text-sm leading-none font-medium">{{ $t('app.basic information') }}</span>
                <div class="grid grid-cols-4 items-center gap-4">
                    <label class="text-right text-sm">Name</label>
                    <Input v-model="form.name" class="col-span-3" />
                </div>
                <div class="grid grid-cols-4 items-center gap-4">
                    <label class="text-right text-sm">Description</label>
                    <Input v-model="form.description" class="col-span-3" />
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <span class="text-sm leading-none font-medium">{{ $t('app.appearance') }}</span>
                <div class="grid grid-cols-4 items-center gap-4">
                    <label class="text-right text-sm">Color</label>
                    <div class="col-span-3 flex items-center gap-2">
                        <input type="color" v-model="form.color" class="h-8 w-8 cursor-pointer rounded border-0" />
                        <Input v-model="form.color" />
                    </div>
                </div>
                <div class="grid grid-cols-4 items-center gap-4">
                    <label class="text-right text-sm">Icon</label>
                    <div class="col-span-3 flex items-center gap-2">
                        <div
                            :style="{ backgroundColor: form.color }"
                            class="flex h-8 w-8 items-center justify-center rounded-md text-white cursor-pointer"
                        >
                            <span v-if="form.icon">{{ form.icon }}</span>
                        </div>
                        <Input v-model="form.icon" />
                        <Popover>
                            <PopoverTrigger>
                                <Button type="button" variant="outline" size="icon" class="h-8 w-8">
                                    <span v-if="form.icon">{{ form.icon }}</span>
                                    <span v-else>😀</span>
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent class="w-auto p-0">
                                <EmojiPicker @select="setEmoji" native hide-search hide-group-names theme="auto" />
                            </PopoverContent>
                        </Popover>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <span class="text-sm leading-none font-medium">{{ $t('app.billing') }}</span>
                <div class="grid grid-cols-4 items-center gap-4">
                    <label class="text-right text-sm">Hourly Rate (€)</label>
                    <Input v-model="form.hourly_rate" type="number" step="0.01" class="col-span-3" />
                </div>
            </div>

            <div class="grid grid-cols-4 items-center gap-4" v-if="!props.parent_id && props.projects">
                <label class="text-right text-sm">Projekt</label>
                <Select v-model="form.parent_id" class="col-span-3">
                    <SelectTrigger>
                        <SelectValue placeholder="Projekt auswählen" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem :value="null">Kein (als Projekt anlegen)</SelectItem>
                        <SelectItem v-for="project in props.projects" :key="project.id" :value="project.id">
                            {{ project.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>
    </SheetDialog>
</template>
