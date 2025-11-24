<script lang="ts" setup>
import { PageHeader } from '@/Components/ui-custom/page-header'
import {
    AlertDialog,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger
} from '@/Components/ui/alert-dialog'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { Bug, DatabaseBackup, FolderOpen, Heart, Loader2, Save, Shredder, TriangleAlert } from 'lucide-vue-next'
import { ref } from 'vue'

const confirmDeleteInput = ref('')
const deleteForm = useForm({})

const handleDelete = () =>
    deleteForm.delete(route('bug-and-feedback.delete-all'), {
        preserveScroll: true,
        onSuccess: () => {
            confirmDeleteInput.value = ''
        }
    })
</script>

<template>
    <Head title="Bug and Feedback" />
    <div class="mb-4 flex h-8 items-center justify-between gap-4">
        <div class="text-foreground/80 text-base font-medium">{{ $t('app.bugs and feedback') }}</div>
    </div>

    <div class="flex items-start space-x-4 py-4">
        <Bug />
        <div class="flex-1 space-y-1">
            <p class="text-sm leading-none font-medium">
                {{ $t('app.report a bug & give feedback') }}
            </p>
            <p class="text-muted-foreground text-sm text-balance">
                {{ $t('app.spotted a bug or have a suggestion? we’d love to hear your feedback and help you out!') }}
            </p>
        </div>
        <Button
            :as="Link"
            :data="{ url: 'https://github.com/WINBIGFOX/TimeScribe/issues' }"
            :href="route('open')"
            class="self-center"
            variant="outline"
        >
            <svg class="fill-current" role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"
                />
            </svg>
            {{ $t('app.report a bug') }}
        </Button>
    </div>

    <div class="flex items-start space-x-4 py-4">
        <Heart />
        <div class="flex-1 space-y-1">
            <p class="text-sm leading-none font-medium">
                {{ $t('app.sponsor the project') }}
            </p>
            <p class="text-muted-foreground text-sm text-balance">
                {{
                    $t(
                        'app.support the continued development of this app with a small donation. every contribution helps us build new features and improve quality!'
                    )
                }}
            </p>
        </div>
        <Button
            :as="Link"
            :data="{ url: 'https://github.com/sponsors/WINBIGFOX' }"
            :href="route('open')"
            class="self-center"
            variant="outline"
        >
            <svg class="fill-current" role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"
                />
            </svg>
            {{ $t('app.sponsor the project') }}
        </Button>
    </div>

    <hr class="my-4" />

    <div class="flex items-start space-x-4 py-4">
        <DatabaseBackup />
        <div class="flex-1 space-y-1">
            <p class="text-sm leading-none font-medium">
                {{ $t('app.backup & restore') }}
            </p>
            <p class="text-muted-foreground text-sm">
                {{
                    $t(
                        'app.generate a backup of your application data for safe-keeping or device migration, and restore it anytime with just one click.'
                    )
                }}
            </p>
            <div class="mt-4 flex gap-4">
                <Button :as="Link" :href="route('bug-and-feedback.export')" class="flex-1" variant="outline">
                    <Save />
                    {{ $t('app.create backup') }}
                </Button>
                <Button :as="Link" :href="route('bug-and-feedback.import')" class="flex-1" variant="outline">
                    <FolderOpen />
                    {{ $t('app.restore backup') }}
                </Button>
            </div>
            <div class="text-sm" v-if="$page.props.errors.message">
                {{ $page.props.errors.message }}
            </div>
        </div>
    </div>

    <hr class="my-4" />
    <div
        class="border-destructive/30 bg-destructive/5 text-destructive/80 dark:bg-destructive/10 mt-4 flex items-start space-x-4 rounded-xl border-1 p-4"
    >
        <Shredder />
        <div class="flex-1 space-y-1">
            <p class="text-sm leading-none font-medium">
                {{ $t('app.delete all data') }}
            </p>
            <p class="text-muted-foreground text-sm text-balance">
                {{ $t('app.delete all data description') }}
            </p>
        </div>
        <AlertDialog>
            <AlertDialogTrigger as-child>
                <Button class="self-center" variant="outline">
                    <TriangleAlert />
                    {{ $t('app.delete all data permanently') }}
                </Button>
            </AlertDialogTrigger>
            <AlertDialogContent class="sm:max-w-lg" disable-outside-pointer-events @escape-key-down.prevent.stop>
                <AlertDialogHeader>
                    <AlertDialogTitle>
                        {{ $t('app.warning') }}
                    </AlertDialogTitle>
                    <AlertDialogDescription class="text-balance">
                        {{ $t('app.do you really want to delete all timescribe data? this action cannot be undone.') }}
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <p class="text-sm">
                    {{ $t('app.confirm deletion by entering the word \"delete\".') }}
                </p>
                <div class="flex flex-col gap-2 py-4">
                    <span class="text-sm leading-none font-medium">{{ $t('app.security check') }}</span>
                    <Input :placeholder="$t('app.type delete')" v-model="confirmDeleteInput" />
                </div>
                <AlertDialogFooter>
                    <AlertDialogCancel :disabled="deleteForm.processing">
                        {{ $t('app.cancel') }}
                    </AlertDialogCancel>
                    <Button
                        variant="destructive"
                        :disabled="confirmDeleteInput !== 'DELETE' || deleteForm.processing"
                        @click="handleDelete"
                        type="button"
                    >
                        <Loader2 v-if="deleteForm.processing" />
                        {{ $t('app.delete all data permanently') }}
                    </Button>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </div>
</template>
