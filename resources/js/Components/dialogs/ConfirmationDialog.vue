<script lang="ts" setup>
import {
    AlertDialog,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/Components/ui/alert-dialog';
import { Button } from '@/Components/ui/button';
import { Method, RequestPayload } from '@inertiajs/core';
import { router, usePage } from '@inertiajs/vue3';
import { useMediaQuery } from '@vueuse/core';
import { Loader2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const page = usePage();

const isDesktop = useMediaQuery('(min-width: 768px)');

interface ConfirmationModalData {
    title: string;
    description: string;
    confirmButtonText: string;
    cancelButtonText: string;
    confirmRoute: string;
    confirmParameters: Record<string, string>;
    confirmMethod: Method;
    confirmData: RequestPayload;
}

const confirmationModalData = ref<ConfirmationModalData | undefined>(undefined);

watch(
    () => page.props.errors.confirmationModal,
    (value) => {
        if (value) {
            openAlert.value = true;
            confirmationModalData.value = JSON.parse(
                value,
            ) as ConfirmationModalData;
        } else {
            setTimeout(() => {
                confirmationModalData.value = undefined;
            }, 200);
        }
        openAlert.value = !!value;
    },
);

const openAlert = ref<boolean>(false);
const loading = ref<boolean>(false);

const destroy = () => {
    if (!confirmationModalData.value) {
        return;
    }
    router.visit(
        route(
            confirmationModalData.value.confirmRoute,
            confirmationModalData.value.confirmParameters,
        ),
        {
            method: confirmationModalData.value.confirmMethod,
            data: confirmationModalData.value.confirmData,
            preserveScroll: true,
            preserveState: 'errors',
            onFinish: () => {
                loading.value = false;
                router.flushAll();
            },
            onStart: () => {
                loading.value = true;
            },
            onSuccess: () => {
                page.props.errors.confirmationModal = undefined;
            },
        },
    );
};
</script>

<template>
    <AlertDialog v-model:open="openAlert">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>
                    {{ confirmationModalData?.title }}
                </AlertDialogTitle>
                <AlertDialogDescription class="text-balance">
                    {{ confirmationModalData?.description }}
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <Button
                    :disabled="loading"
                    :size="isDesktop ? 'default' : 'lg'"
                    @click="$page.props.errors.confirmationModal = undefined"
                    class="mt-2 sm:mt-0"
                    type="button"
                    variant="outline"
                >
                    {{ confirmationModalData?.cancelButtonText }}
                </Button>
                <Button
                    :disabled="loading"
                    :size="isDesktop ? 'default' : 'lg'"
                    @click.prevent.stop="destroy"
                    type="button"
                    variant="destructive"
                >
                    <Loader2 class="mr-2 h-4 w-4 animate-spin" v-if="loading" />
                    {{ confirmationModalData?.confirmButtonText }}
                </Button>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
