<script lang="ts" setup>
import { Button } from '@/Components/ui/button'
import { cn } from '@/lib/utils'
import { Link } from '@inertiajs/vue3'
import { CircleAlert } from 'lucide-vue-next'
import type { Component, HTMLAttributes } from 'vue'

const props = defineProps<{
    class?: HTMLAttributes['class']
    icon?: Component
    title: string
    description: string
    actionLabel?: string
    actionHref?: string
    actionClick?: () => void
}>()
</script>

<template>
    <div
        :class="
            cn(
                'bg-muted/40 text-foreground relative flex h-full w-full max-w-3xl flex-col items-center justify-center gap-4 rounded-2xl border border-dashed p-10 text-center',
                props.class
            )
        "
    >
        <div class="bg-primary/10 text-primary flex size-12 items-center justify-center rounded-full">
            <component :is="props.icon ?? CircleAlert" class="h-6 w-6" />
        </div>
        <div class="max-w-xl space-y-2">
            <p class="text-lg font-semibold">
                {{ props.title }}
            </p>
            <p class="text-muted-foreground text-balance">
                {{ props.description }}
            </p>
        </div>
        <div class="flex gap-2" v-if="(props.actionHref || props.actionClick) && props.actionLabel">
            <Button @click="props.actionClick ? props.actionClick() : undefined" :as="props.actionClick ? undefined : Link" :href="props.actionHref ? props.actionHref : undefined" variant="outline">
                <component v-if="props.icon" :is="props.icon" />
                {{ props.actionLabel }}
            </Button>
        </div>
        <slot name="actions" />
    </div>
</template>
