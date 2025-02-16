<script lang="ts" setup>
import ConfirmationDialog from '@/Components/dialogs/ConfirmationDialog.vue';
import { Button } from '@/Components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import {
    Drawer,
    DrawerClose,
    DrawerContent,
    DrawerDescription,
    DrawerFooter,
    DrawerHeader,
    DrawerTitle,
} from '@/Components/ui/drawer';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { router, usePage } from '@inertiajs/vue3';
import { useMediaQuery } from '@vueuse/core';
import { useModal } from 'inertia-modal';
import { Edit, Loader2, Trash } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = withDefaults(
    defineProps<{
        title?: string;
        description?: string;
        close?: string;
        submit?: string;
        destroy?: string;
        edit?: string;
        scrollable?: boolean;
        submitHref?: string;
        loading?: boolean;
        disabled?: boolean;
        size?: 'md' | 'lg';
        forceModal?: boolean;
    }>(),
    {
        size: 'md',
    },
);

const openModel = defineModel('open', {
    type: Boolean,
    default: false,
});
const page = usePage();
const emit = defineEmits(['submit', 'destroy', 'edit']);
const { show, redirect } = useModal();
const open = ref<boolean>(page.props.modal ? show : false);
watch(open, (value: boolean) => {
    openModel.value = value;
    if (!value) {
        setTimeout(() => {
            redirect({ preserveScroll: true, preserveState: true });
        }, 200);
    }
});
watch(openModel, (value: boolean) => {
    open.value = value;
});

const isDesktop = useMediaQuery('(min-width: 768px)');

const internalSubmit = () => {
    if (props.submitHref) {
        setTimeout(() => {
            router.visit(props.submitHref || '');
        }, 100);
        open.value = false;
    } else {
        emit('submit');
    }
};
</script>

<template>
    <Dialog v-if="isDesktop || forceModal" v-model:open="open">
        <DialogContent
            :class="{
                'sm:max-w-[425px]': props.size === 'md',
                'sm:max-w-3xl': props.size === 'lg',
            }"
        >
            <form
                :class="{
                    'max-h-[90dvh] grid-rows-[auto_minmax(0,1fr)_auto]':
                        props.scrollable,
                }"
                @submit.prevent="internalSubmit"
                class="flex flex-col gap-4 outline-none"
            >
                <DialogHeader>
                    <DialogTitle class="leading-normal tracking-tight">
                        <slot name="title">
                            {{ props.title }}
                        </slot>
                    </DialogTitle>
                    <DialogDescription>
                        <slot name="description">
                            {{ props.description }}
                        </slot>
                    </DialogDescription>
                </DialogHeader>
                <div
                    :class="{
                        'overflow-y-auto overscroll-none': props.scrollable,
                    }"
                >
                    <slot />
                </div>

                <DialogFooter>
                    <slot name="footer" />
                    <slot name="actions" v-if="props.edit && props.destroy">
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button
                                    :disabled="props.loading || props.disabled"
                                    class="mr-auto"
                                    type="button"
                                    variant="link"
                                >
                                    <Loader2
                                        class="mr-2 h-4 w-4 animate-spin"
                                        v-if="props.loading"
                                    />
                                    Aktionen
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent>
                                <DropdownMenuLabel>Aktionen</DropdownMenuLabel>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem @click="$emit('edit')">
                                    <Edit class="mr-2 h-4 w-4" />
                                    <span>{{ props.edit }}</span>
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    @click="$emit('destroy')"
                                    class="text-destructive"
                                >
                                    <Trash class="mr-2 h-4 w-4" />
                                    <span>{{ props.destroy }}</span>
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </slot>
                    <slot name="destroy" v-if="!props.edit && props.destroy">
                        <Button
                            :disabled="props.loading || props.disabled"
                            @click="$emit('destroy')"
                            class="text-destructive mr-auto"
                            type="button"
                            v-if="props.destroy"
                            variant="link"
                        >
                            <Loader2
                                class="mr-2 h-4 w-4 animate-spin"
                                v-if="props.loading"
                            />
                            {{ props.destroy }}
                        </Button>
                    </slot>
                    <slot name="edit" v-if="props.edit && !props.destroy">
                        <Button
                            :disabled="props.loading || props.disabled"
                            @click="$emit('edit')"
                            class="mr-auto"
                            type="button"
                            v-if="props.edit"
                            variant="link"
                        >
                            <Loader2
                                class="mr-2 h-4 w-4 animate-spin"
                                v-if="props.loading"
                            />
                            {{ props.edit }}
                        </Button>
                    </slot>
                    <DialogClose as-child>
                        <slot name="close">
                            <Button v-if="props.close" variant="outline">
                                {{ props.close }}
                            </Button>
                        </slot>
                    </DialogClose>
                    <slot name="submit">
                        <Button
                            :disabled="props.loading || props.disabled"
                            type="submit"
                            v-if="props.submit"
                        >
                            <Loader2
                                class="mr-2 h-4 w-4 animate-spin"
                                v-if="props.loading"
                            />
                            {{ props.submit }}
                        </Button>
                    </slot>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <Drawer v-else v-model:open="open">
        <DrawerContent>
            <form
                :class="{
                    'flex grid-rows-[auto_minmax(0,1fr)_auto] flex-col':
                        props.scrollable,
                }"
                @submit.prevent="internalSubmit"
                class="mx-auto max-h-[90dvh] w-full max-w-md outline-none"
                tabindex="1"
            >
                <DrawerHeader class="text-left">
                    <DrawerTitle class="text-2xl">
                        <slot name="title">
                            {{ props.title }}
                        </slot>
                    </DrawerTitle>
                    <DrawerDescription class="text-base">
                        <slot name="description">
                            {{ props.description }}
                        </slot>
                    </DrawerDescription>
                </DrawerHeader>
                <div
                    :class="{
                        'overflow-y-auto overscroll-none': props.scrollable,
                    }"
                    class="px-4"
                >
                    <slot />
                </div>
                <DrawerFooter>
                    <slot name="footer" />
                    <slot name="actions" v-if="props.edit && props.destroy">
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button
                                    :disabled="props.loading || props.disabled"
                                    type="button"
                                    variant="link"
                                >
                                    <Loader2
                                        class="mr-2 h-4 w-4 animate-spin"
                                        v-if="props.loading"
                                    />
                                    Aktionen
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent>
                                <DropdownMenuLabel>Aktionen</DropdownMenuLabel>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem @click="$emit('edit')">
                                    <Edit class="mr-2 h-4 w-4" />
                                    <span>{{ props.edit }}</span>
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    @click="$emit('destroy')"
                                    class="text-destructive"
                                >
                                    <Trash class="mr-2 h-4 w-4" />
                                    <span>{{ props.destroy }}</span>
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </slot>
                    <slot name="destroy" v-if="!props.edit && props.destroy">
                        <Button
                            :disabled="props.loading || props.disabled"
                            @click="$emit('destroy')"
                            class="text-destructive"
                            size="lg"
                            type="button"
                            v-if="props.destroy"
                            variant="link"
                        >
                            <Loader2
                                class="mr-2 h-4 w-4 animate-spin"
                                v-if="props.loading"
                            />
                            {{ props.destroy }}
                        </Button>
                    </slot>
                    <slot name="edit" v-if="props.edit && !props.destroy">
                        <Button
                            :disabled="props.loading || props.disabled"
                            @click="$emit('edit')"
                            size="lg"
                            type="button"
                            v-if="props.edit"
                            variant="link"
                        >
                            <Loader2
                                class="mr-2 h-4 w-4 animate-spin"
                                v-if="props.loading"
                            />
                            {{ props.edit }}
                        </Button>
                    </slot>
                    <slot name="submit">
                        <Button
                            :disabled="props.loading || props.disabled"
                            size="lg"
                            type="submit"
                            v-if="props.submit"
                        >
                            <Loader2
                                class="mr-2 h-4 w-4 animate-spin"
                                v-if="props.loading"
                            />
                            {{ props.submit }}
                        </Button>
                    </slot>
                    <DrawerClose as-child v-if="!props.submit">
                        <slot name="close">
                            <Button
                                size="lg"
                                v-if="props.close"
                                variant="outline"
                            >
                                {{ props.close }}
                            </Button>
                        </slot>
                    </DrawerClose>
                </DrawerFooter>
            </form>
        </DrawerContent>
    </Drawer>

    <ConfirmationDialog />
</template>
