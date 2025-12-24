<script lang="ts" setup>
import { PageHeader } from '@/Components/ui-custom/page-header'
import { ShortcutInput } from '@/Components/ui-custom/shortcut-input'
import { Head, router, useForm } from '@inertiajs/vue3'
import { useDebounceFn } from '@vueuse/core'
import { ChartColumnBig, Coffee, Play, Square, Tag } from 'lucide-vue-next'
import { watch } from 'vue'

const props = defineProps<{
    startShortcut?: string
    stopShortcut?: string
    pauseShortcut?: string
    overviewShortcut?: string
    projectPickerShortcut?: string
}>()

const form = useForm({
    startShortcut: props.startShortcut,
    stopShortcut: props.stopShortcut,
    pauseShortcut: props.pauseShortcut,
    overviewShortcut: props.overviewShortcut,
    projectPickerShortcut: props.projectPickerShortcut
})

const submit = () => {
    router.flushAll()
    form.patch(route('settings.shortcuts.update'), {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => form.transform((data) => data)
    })
}

const debouncedSubmit = useDebounceFn(submit, 500)

watch(
    () => [form.startShortcut, form.stopShortcut, form.pauseShortcut, form.overviewShortcut, form.projectPickerShortcut],
    debouncedSubmit,
    {
        deep: true
    }
)

watch(
    () => [props.startShortcut, props.stopShortcut, props.pauseShortcut, props.overviewShortcut, props.projectPickerShortcut],
    ([start, stop, pause, overview, projectPicker]) => {
        form.defaults({
            startShortcut: start ?? undefined,
            stopShortcut: stop ?? undefined,
            pauseShortcut: pause ?? undefined,
            overviewShortcut: overview ?? undefined,
            projectPickerShortcut: projectPicker ?? undefined
        })
        form.reset()
    },
    { immediate: true, deep: true }
)
</script>

<template>
    <Head title="Settings - Shortcuts" />
    <PageHeader :title="$t('app.shortcuts')" />
    <div>
        <div class="flex items-start space-x-4 py-4">
            <Play />
            <div class="flex flex-1 items-start gap-4">
                <div class="flex-1 space-y-1">
                    <p class="text-sm leading-none font-medium">
                        {{ $t('app.start timer') }}
                    </p>
                    <p class="text-muted-foreground text-sm text-balance">
                        {{ $t('app.start the timer instantly.') }}
                    </p>
                </div>
                <div class="w-full sm:w-1/2">
                    <ShortcutInput :placeholder="$t('app.select shortcut')" v-model="form.startShortcut" />
                    <p class="text-destructive mt-1 text-xs" v-if="form.errors.startShortcut">
                        {{ $t(form.errors.startShortcut) }}
                    </p>
                </div>
            </div>
        </div>
        <div class="flex items-start space-x-4 py-4">
            <Square />
            <div class="flex flex-1 items-start gap-4">
                <div class="flex-1 space-y-1">
                    <p class="text-sm leading-none font-medium">
                        {{ $t('app.stop timer') }}
                    </p>
                    <p class="text-muted-foreground text-sm text-balance">
                        {{ $t('app.stop the current timer.') }}
                    </p>
                </div>
                <div class="w-full sm:w-1/2">
                    <ShortcutInput :placeholder="$t('app.select shortcut')" v-model="form.stopShortcut" />
                    <p class="text-destructive mt-1 text-xs" v-if="form.errors.stopShortcut">
                        {{ $t(form.errors.stopShortcut) }}
                    </p>
                </div>
            </div>
        </div>
        <div class="flex items-start space-x-4 py-4">
            <Coffee />
            <div class="flex flex-1 items-start gap-4">
                <div class="flex-1 space-y-1">
                    <p class="text-sm leading-none font-medium">
                        {{ $t('app.pause timer') }}
                    </p>
                    <p class="text-muted-foreground text-sm text-balance">
                        {{ $t('app.start or end a break.') }}
                    </p>
                </div>
                <div class="w-full sm:w-1/2">
                    <ShortcutInput :placeholder="$t('app.select shortcut')" v-model="form.pauseShortcut" />
                    <p class="text-destructive mt-1 text-xs" v-if="form.errors.pauseShortcut">
                        {{ $t(form.errors.pauseShortcut) }}
                    </p>
                </div>
            </div>
        </div>
        <div class="flex items-start space-x-4 py-4">
            <ChartColumnBig />
            <div class="flex flex-1 items-start gap-4">
                <div class="flex-1 space-y-1">
                    <p class="text-sm leading-none font-medium">
                        {{ $t('app.open overview') }}
                    </p>
                    <p class="text-muted-foreground text-sm text-balance">
                        {{ $t('app.open the overview window.') }}
                    </p>
                </div>
                <div class="w-full sm:w-1/2">
                    <ShortcutInput :placeholder="$t('app.select shortcut')" v-model="form.overviewShortcut" />
                    <p class="text-destructive mt-1 text-xs" v-if="form.errors.overviewShortcut">
                        {{ $t(form.errors.overviewShortcut) }}
                    </p>
                </div>
            </div>
        </div>
        <div class="flex items-start space-x-4 py-4">
            <Tag />
            <div class="flex flex-1 items-start gap-4">
                <div class="flex-1 space-y-1">
                    <p class="text-sm leading-none font-medium">
                        {{ $t('app.show project picker') }}
                    </p>
                    <p class="text-muted-foreground text-sm text-balance">
                        {{ $t('app.show the project picker in the menu bar.') }}
                    </p>
                </div>
                <div class="w-full sm:w-1/2">
                    <ShortcutInput :placeholder="$t('app.select shortcut')" v-model="form.projectPickerShortcut" />
                    <p class="text-destructive mt-1 text-xs" v-if="form.errors.projectPickerShortcut">
                        {{ $t(form.errors.projectPickerShortcut) }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
