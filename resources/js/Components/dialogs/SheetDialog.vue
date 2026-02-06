<script lang="ts" setup>
import ConfirmationDialog from '@/Components/dialogs/ConfirmationDialog.vue'
import { Button } from '@/Components/ui/button'
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle } from '@/Components/ui/sheet'

import { DialogClose, DialogFooter } from '@/Components/ui/dialog'
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger
} from '@/Components/ui/dropdown-menu'
import { router, usePage } from '@inertiajs/vue3'
import { useModal } from 'inertia-modal'
import { Edit, Loader2, Trash } from 'lucide-vue-next'
import { ref, watch } from 'vue'

const props = withDefaults(
    defineProps<{
        title?: string
        description?: string
        close?: string
        submit?: string
        destroy?: string
        edit?: string
        scrollable?: boolean
        submitHref?: string
        loading?: boolean
        disabled?: boolean
        size?: 'md' | 'lg'
        forceModal?: boolean
        global?: boolean
    }>(),
    {
        size: 'md',
        global: true
    }
)

const openModel = defineModel('open', {
    type: Boolean,
    default: false
})
const emit = defineEmits(['submit', 'destroy', 'edit'])

const page = usePage()
const { show, redirect } = useModal()
const open = ref<boolean>(page.props.modal && props.global ? show : (openModel.value ?? false))
watch(open, (value: boolean) => {
    openModel.value = value
    if (!value && props.global) {
        setTimeout(() => {
            redirect({ preserveScroll: true, preserveState: true })
        }, 200)
    }
})
watch(openModel, (value: boolean) => {
    open.value = value
})

const internalSubmit = () => {
    if (props.submitHref) {
        setTimeout(() => {
            router.visit(props.submitHref || '')
        }, 100)
        if (props.global) {
            open.value = false
        }
    } else {
        emit('submit')
    }
}
</script>

<template>
    <Sheet v-model:open="open">
        <SheetContent
            :class="{
                'sm:max-w-[425px]': props.size === 'md',
                'sm:max-w-none': props.size === 'lg'
            }"
            class="inset-y-2 right-2 h-auto rounded-lg border"
        >
            <form @submit.prevent="internalSubmit" class="flex h-full flex-col gap-4 pb-4 outline-none">
                <SheetHeader class="pb-0">
                    <SheetTitle class="leading-normal tracking-tight">
                        <slot name="title">
                            {{ props.title }}
                        </slot>
                    </SheetTitle>
                    <SheetDescription>
                        <slot name="description">
                            {{ props.description }}
                        </slot>
                    </SheetDescription>
                </SheetHeader>
                <div
                    :class="{
                        'overflow-y-auto overscroll-none': props.scrollable,
                        'overflow-hidden': !props.scrollable
                    }"
                    class="flex grow flex-col px-4"
                >
                    <slot />
                </div>

                <DialogFooter class="px-4">
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
                                    <Loader2 class="mr-2 h-4 w-4 animate-spin" v-if="props.loading" />
                                    {{ $t('app.actions') }}
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent>
                                <DropdownMenuLabel>{{ $t('app.actions') }}</DropdownMenuLabel>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem @click="$emit('edit')">
                                    <Edit class="mr-2 h-4 w-4" />
                                    <span>{{ props.edit }}</span>
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="$emit('destroy')" class="text-destructive">
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
                            <Loader2 class="mr-2 h-4 w-4 animate-spin" v-if="props.loading" />
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
                            <Loader2 class="mr-2 h-4 w-4 animate-spin" v-if="props.loading" />
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
                        <Button :disabled="props.loading || props.disabled" type="submit" v-if="props.submit">
                            <Loader2 class="mr-2 h-4 w-4 animate-spin" v-if="props.loading" />
                            {{ props.submit }}
                        </Button>
                    </slot>
                </DialogFooter>
            </form>
        </SheetContent>
    </Sheet>
    <ConfirmationDialog />
</template>
