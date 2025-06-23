<script lang="ts" setup>
import { Button } from '@/Components/ui/button'
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover'
import { cn } from '@/lib/utils'
import { Trash } from 'lucide-vue-next'
import { HTMLAttributes, ref } from 'vue'
import EmojiPicker from 'vue3-emoji-picker'

const props = defineProps<{
    class?: HTMLAttributes['class']
    required?: boolean
}>()
const modelValue = defineModel<string>()
const open = ref(false)
const setEmoji = (emoji: { i: string }) => {
    modelValue.value = emoji.i
    open.value = false
}
</script>

<template>
    <div class="flex gap-2">
        <Popover :open="open">
            <PopoverTrigger as-child>
                <Button
                    @click="open = true"
                    :class="cn('flex-1', modelValue ? 'p-0 text-xl' : undefined, props.class)"
                    variant="outline"
                >
                    {{ !modelValue ? $t('app.select emoji') : modelValue }}
                </Button>
            </PopoverTrigger>
            <PopoverContent class="w-auto p-0" side="left" @focus-outside="open = false">
                <EmojiPicker @select="setEmoji" class="shadow-none!" hide-group-names hide-search native theme="auto" />
            </PopoverContent>
        </Popover>
        <Button @click="modelValue = undefined" size="icon" v-if="modelValue && !props.required" variant="outline">
            <Trash />
        </Button>
    </div>
</template>
