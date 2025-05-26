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

defineOptions({
    layout: BasicLayout
})

const props = defineProps<{
    submit_route: string
    project: Project
    projects?: Project[]
}>()

const form = useForm({
    name: props.project.name,
    description: props.project.description || '',
    color: props.project.color,
    icon: props.project.icon || '',
    hourly_rate: props.project.hourly_rate || 0,
    parent_id: props.project.parent?.id
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
        <div class="grid gap-6 py-4">
            <div class="flex flex-col gap-2">
                <span class="text-sm leading-none font-medium">{{ $t('app.basic information') }}</span>
                <div class="grid grid-cols-4 items-center gap-4">
                    <label class="text-right text-sm">Name</label>
                    <Input class="col-span-3" v-model="form.name" />
                </div>
                <div class="grid grid-cols-4 items-center gap-4">
                    <label class="text-right text-sm">Description</label>
                    <Input class="col-span-3" v-model="form.description" />
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <span class="text-sm leading-none font-medium">{{ $t('app.appearance') }}</span>
                <div class="grid grid-cols-4 items-center gap-4">
                    <label class="text-right text-sm">Color</label>
                    <div class="col-span-3 flex items-center gap-2">
                        <input class="h-8 w-8 cursor-pointer rounded border-0" type="color" v-model="form.color" />
                        <Input v-model="form.color" />
                    </div>
                </div>
                <div class="grid grid-cols-4 items-center gap-4">
                    <label class="text-right text-sm">Icon</label>
                    <div class="col-span-3 flex items-center gap-2">
                        <div
                            :style="{ backgroundColor: form.color }"
                            class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md text-white"
                        >
                            <span v-if="form.icon">{{ form.icon }}</span>
                        </div>
                        <Input v-model="form.icon" />
                        <Popover>
                            <PopoverTrigger>
                                <Button class="h-8 w-8" size="icon" type="button" variant="outline">
                                    <span v-if="form.icon">{{ form.icon }}</span>
                                    <span v-else>😀</span>
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent class="w-auto p-0">
                                <EmojiPicker @select="setEmoji" hide-group-names hide-search native theme="auto" />
                            </PopoverContent>
                        </Popover>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <span class="text-sm leading-none font-medium">{{ $t('app.billing') }}</span>
                <div class="grid grid-cols-4 items-center gap-4">
                    <label class="text-right text-sm">Hourly Rate (€)</label>
                    <Input class="col-span-3" step="0.01" type="number" v-model="form.hourly_rate" />
                </div>
            </div>

            <div class="grid grid-cols-4 items-center gap-4" v-if="props.projects">
                <label class="text-right text-sm">Projekt</label>
                <Select class="col-span-3" v-model="form.parent_id">
                    <SelectTrigger>
                        <SelectValue placeholder="Projekt auswählen" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem :value="null">Kein (als Projekt anlegen)</SelectItem>
                        <SelectItem :key="project.id" :value="project.id" v-for="project in props.projects">
                            {{ project.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>
    </SheetDialog>
</template>
