<script lang="ts" setup>
import SheetDialog from '@/Components/dialogs/SheetDialog.vue'
import { Badge } from '@/Components/ui/badge'
import { Button } from '@/Components/ui/button'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { ExternalLink } from 'lucide-vue-next'

const props = defineProps<{
    submit_route: string
}>()

const form = useForm({
    source: 'clockify'
})

const submit = () => {
    router.flushAll()
    form.post(props.submit_route, {
        preserveScroll: true,
        preserveState: 'errors'
    })
}
</script>

<template>
    <Head title="Clockify Import" />
    <SheetDialog
        :close="$t('app.cancel')"
        :description="
            $t(
                'app.to import data from clockify, you must first export it. Simply follow the step-by-step instructions below.'
            )
        "
        :loading="form.processing"
        :submit="$t('app.import csv file')"
        :title="$t('app.import clockify data')"
        @submit="submit"
    >
        <div class="mt-4 flex flex-col gap-6 text-sm">
            <div class="flex flex-col gap-1">
                <div class="flex items-center gap-2">
                    <Badge>{{ $t('app.step :number', { number: '1' }) }}</Badge>
                    <span class="font-medium">{{ $t('app.open report view') }}</span>
                </div>
                <span class="text-muted-foreground">{{ $t('app.open the detailed reports in clockify.') }}</span>
                <Button
                    :as="Link"
                    :data="{ url: 'https://app.clockify.me/reports/detailed' }"
                    :href="route('open')"
                    size="sm"
                    variant="outline"
                >
                    <ExternalLink />
                    {{ $t('app.open report view') }}
                </Button>
            </div>
            <div class="flex flex-col gap-1">
                <div class="flex items-center gap-2">
                    <Badge>{{ $t('app.step :number', { number: '2' }) }}</Badge>
                    <span class="font-medium">{{ $t('app.select time period') }}</span>
                </div>
                <span class="text-muted-foreground">{{ $t('app.select the time period you want to export.') }}</span>
            </div>
            <div class="flex flex-col gap-1">
                <div class="flex items-center gap-2">
                    <Badge>{{ $t('app.step :number', { number: '3' }) }}</Badge>
                    <span class="font-medium">{{ $t('app.export csv file') }}</span>
                </div>
                <span class="text-muted-foreground">
                    {{ $t('app.click "export" at the top of the table and choose "save as csv".') }}
                </span>
            </div>
        </div>

        <div class="border-border mt-6 border-t pt-6 text-sm">
            {{
                $t(
                    'app.once you’ve completed all steps, you’ll have a csv file with your time data. click “import csv file” to import the data into timescribe.'
                )
            }}
        </div>
    </SheetDialog>
</template>
