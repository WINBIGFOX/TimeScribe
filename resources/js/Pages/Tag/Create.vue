<script lang="ts" setup>
import { Head, router, useForm } from '@inertiajs/vue3'

import SheetDialog from '@/Components/dialogs/SheetDialog.vue'
import BasicLayout from '@/Layouts/BasicLayout.vue'

defineOptions({
    layout: BasicLayout
})

const props = defineProps<{
    submit_route: string
}>()

const submit = () => {
    router.flushAll()
    form.post(props.submit_route, {
        preserveScroll: true,
        preserveState: true
    })
}

const form = useForm({})
</script>

<template>
    <Head title="Project/Tag Create" />
    <SheetDialog
        :close="$t('app.cancel')"
        :submit="$t('app.save')"
        :title="$t('app.create project/tag')"
        @submit="submit"
    >
    </SheetDialog>
</template>
