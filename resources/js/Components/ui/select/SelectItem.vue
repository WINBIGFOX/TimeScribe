<script lang="ts" setup>
import { cn } from '@/lib/utils'
import { Check } from '@lucide/vue'
import { SelectItem, SelectItemIndicator, type SelectItemProps, SelectItemText, useForwardProps } from 'reka-ui'
import { computed, type HTMLAttributes } from 'vue'

const props = defineProps<SelectItemProps & { class?: HTMLAttributes['class'] }>()

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props

    return delegated
})

const forwardedProps = useForwardProps(delegatedProps)
</script>

<template>
    <SelectItem
        :class="
            cn(
                `focus:bg-accent focus:text-accent-foreground [&_svg:not([class*='text-'])]:text-muted-foreground relative flex w-full cursor-default items-center gap-2 rounded-sm py-1.5 text-sm outline-hidden select-none not-rtl:pr-8 not-rtl:pl-2 data-[disabled]:pointer-events-none data-[disabled]:opacity-50 rtl:flex-row-reverse rtl:pr-2 rtl:pl-8 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4 *:[span]:last:flex *:[span]:last:items-center *:[span]:last:gap-2`,
                props.class
            )
        "
        v-bind="forwardedProps"
        data-slot="select-item"
    >
        <span class="absolute flex size-3.5 items-center justify-center not-rtl:right-2 rtl:left-2">
            <SelectItemIndicator>
                <Check class="size-4" />
            </SelectItemIndicator>
        </span>

        <SelectItemText>
            <slot />
        </SelectItemText>
    </SelectItem>
</template>
