<script lang="ts" setup>
import { cn } from '@/lib/utils'
import type { HTMLAttributes } from 'vue'

const props = defineProps<{
    class?: HTMLAttributes['class']
}>()
</script>

<template>
    <fieldset
        :class="
            cn(
                'flex flex-col gap-6',
                'has-[>[data-slot=checkbox-group]]:gap-3 has-[>[data-slot=radio-group]]:gap-3',
                props.class
            )
        "
        data-slot="field-set"
    >
        <slot />
    </fieldset>
</template>
