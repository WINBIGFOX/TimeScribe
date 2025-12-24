<script lang="ts" setup>
import { Button } from '@/Components/ui/button'
import { cn } from '@/lib/utils'
import { Keyboard, X } from 'lucide-vue-next'
import { computed, onBeforeUnmount, ref, watch, type HTMLAttributes } from 'vue'

const props = withDefaults(
    defineProps<{
        class?: HTMLAttributes['class']
        placeholder?: string
        disabled?: boolean
        required?: boolean
    }>(),
    {
        placeholder: undefined
    }
)

const modelValue = defineModel<string | null>()

type Modifier = 'Cmd' | 'CmdOrCtrl' | 'Ctrl' | 'Alt' | 'Option' | 'AltGr' | 'Shift' | 'Super' | 'Meta'

const MODIFIER_ORDER: Modifier[] = ['Cmd', 'CmdOrCtrl', 'Ctrl', 'Alt', 'Option', 'AltGr', 'Shift', 'Super', 'Meta']
const MODIFIER_SYMBOLS: Record<Modifier, string> = {
    Cmd: '⌘',
    CmdOrCtrl: '⌘/Ctrl',
    Ctrl: '⌃',
    Alt: '⌥',
    Option: '⌥',
    AltGr: 'AltGr',
    Shift: '⇧',
    Super: 'Super',
    Meta: '⌘'
}
const KEY_SYMBOLS: Record<string, string> = {
    Enter: '⏎',
    Esc: '⎋',
    Space: '␣',
    Plus: '+',
    Backspace: '⌫',
    Delete: '⌦',
    Up: '↑',
    Down: '↓',
    Left: '←',
    Right: '→',
    Home: '↖',
    End: '↘',
    PageUp: '⇞',
    PageDown: '⇟'
}
const digits = Array.from({ length: 10 }, (_, index) => String(index))
const letters = Array.from({ length: 26 }, (_, index) => String.fromCharCode(65 + index))
const functionKeys = Array.from({ length: 24 }, (_, index) => `F${index + 1}`)

const allowedKeys = new Set<string>([
    ...digits,
    ...letters,
    ...functionKeys,
    'Backspace',
    'Delete',
    'Insert',
    'Enter',
    'Up',
    'Down',
    'Left',
    'Right',
    'Home',
    'End',
    'PageUp',
    'PageDown',
    'Esc',
    'VolumeUp',
    'VolumeDown',
    'VolumeMute',
    'MediaNextTrack',
    'MediaPreviousTrack',
    'MediaStop',
    'MediaPlayPause',
    'PrintScreen',
    'Numlock',
    'Scrolllock',
    'Space',
    'Plus'
])

const recording = ref(false)
const error = ref<string | null>(null)
const preview = ref<string | null>(null)
const fieldRef = ref<HTMLElement>()
const isMac = computed(() => {
    if (typeof navigator === 'undefined') {
        return false
    }

    return /Mac|iPod|iPhone|iPad/.test(navigator.platform)
})

const sortModifiers = (modifiers: Modifier[]): Modifier[] => {
    const uniqueModifiers = Array.from(new Set(modifiers))

    return uniqueModifiers.sort((first, second) => MODIFIER_ORDER.indexOf(first) - MODIFIER_ORDER.indexOf(second))
}

const normalizeKey = (event: KeyboardEvent): string | null => {
    const key = event.key
    const code = event.code
    if (!key) {
        return null
    }

    if (key === ' ' || key === 'Spacebar' || key === '\u00a0') {
        return 'Space'
    }

    if (key === '+') {
        return 'Plus'
    }

    if (key.length === 1) {
        if (/[a-zA-Z]/.test(key)) {
            return key.toUpperCase()
        }

        if (/[0-9]/.test(key)) {
            return key
        }
    }

    const upperKey = key.toUpperCase()
    if (/^F\\d{1,2}$/.test(upperKey)) {
        const numberPart = Number(upperKey.slice(1))
        if (numberPart >= 1 && numberPart <= 24) {
            return `F${numberPart}`
        }
    }

    const keyMap: Record<string, string> = {
        ArrowDown: 'Down',
        ArrowLeft: 'Left',
        ArrowRight: 'Right',
        ArrowUp: 'Up',
        Backspace: 'Backspace',
        Del: 'Delete',
        Delete: 'Delete',
        Down: 'Down',
        End: 'End',
        Enter: 'Enter',
        Esc: 'Esc',
        Escape: 'Esc',
        Home: 'Home',
        Insert: 'Insert',
        Left: 'Left',
        MediaNextTrack: 'MediaNextTrack',
        MediaPlayPause: 'MediaPlayPause',
        MediaPreviousTrack: 'MediaPreviousTrack',
        MediaStop: 'MediaStop',
        PageDown: 'PageDown',
        PageUp: 'PageUp',
        PrintScreen: 'PrintScreen',
        Return: 'Enter',
        Right: 'Right',
        ScrollLock: 'Scrolllock',
        Scrolllock: 'Scrolllock',
        Space: 'Space',
        Up: 'Up',
        VolumeDown: 'VolumeDown',
        VolumeMute: 'VolumeMute',
        VolumeUp: 'VolumeUp'
    }

    const normalized = keyMap[key]
    if (normalized && allowedKeys.has(normalized)) {
        return normalized
    }

    const numLockKey = key === 'NumLock' ? 'Numlock' : null
    if (numLockKey && allowedKeys.has(numLockKey)) {
        return numLockKey
    }

    if (code?.startsWith('Key') && code.length === 4) {
        const letter = code.slice(3).toUpperCase()
        if (allowedKeys.has(letter)) {
            return letter
        }
    }

    if (code?.startsWith('Digit') && code.length === 6) {
        const digit = code.slice(5)
        if (allowedKeys.has(digit)) {
            return digit
        }
    }

    if (code === 'Space') {
        return 'Space'
    }

    return null
}

const collectModifiers = (event: KeyboardEvent): Modifier[] => {
    const modifiers: Modifier[] = []
    const hasAltGraph = event.getModifierState('AltGraph')
    const hasMeta = event.metaKey
    const hasCtrl = event.ctrlKey
    const hasAlt = event.altKey

    if (hasMeta && hasCtrl) {
        modifiers.push('CmdOrCtrl')
    } else if (hasMeta) {
        modifiers.push(isMac.value ? 'Cmd' : 'Meta')
    } else if (hasCtrl) {
        modifiers.push('Ctrl')
    }

    if (hasAltGraph) {
        modifiers.push('AltGr')
    } else if (hasAlt) {
        modifiers.push(isMac.value ? 'Option' : 'Alt')
    }

    if (event.shiftKey) {
        modifiers.push('Shift')
    }

    return sortModifiers(modifiers)
}

const formatShortcut = (modifiers: Modifier[], key: string): string | null => {
    if (!allowedKeys.has(key)) {
        return null
    }

    if (modifiers.length === 0) {
        return null
    }

    const sortedModifiers = sortModifiers(modifiers)

    return [...sortedModifiers, key].join('+')
}

const formatDisplay = (value: string | null | undefined): string | null => {
    if (!value) {
        return null
    }

    const parts = value.split('+')

    return parts.map((part) => MODIFIER_SYMBOLS[part as Modifier] ?? KEY_SYMBOLS[part] ?? part).join(' + ')
}

const handleKeydown = (event: KeyboardEvent) => {
    if (!recording.value) {
        return
    }

    event.preventDefault()
    event.stopPropagation()

    const modifiers = collectModifiers(event)
    const normalizedKey = normalizeKey(event)

    const isModifierOnlyKey = ['Shift', 'Meta', 'Alt', 'AltGraph', 'Control', 'Ctrl'].includes(event.key)
    if (isModifierOnlyKey) {
        preview.value = modifiers.length > 0 ? modifiers.join('+') : null
        error.value = null
        return
    }

    preview.value = normalizedKey ? [...modifiers, normalizedKey].join('+') : modifiers.join('+')

    if (!normalizedKey) {
        error.value = 'app.this key is not supported.'
        return
    }

    if (modifiers.length === 0) {
        error.value = 'app.add at least one modifier key.'
        return
    }

    const shortcut = formatShortcut(modifiers, normalizedKey)
    if (!shortcut) {
        error.value = 'app.this shortcut is not supported.'
        return
    }

    modelValue.value = shortcut
    error.value = null
    recording.value = false
}

const attachListeners = () => {
    if (typeof window === 'undefined') {
        return
    }

    window.addEventListener('keydown', handleKeydown, { capture: true })
    window.addEventListener('blur', stopRecording)
}

const detachListeners = () => {
    if (typeof window === 'undefined') {
        return
    }

    window.removeEventListener('keydown', handleKeydown, { capture: true })
    window.removeEventListener('blur', stopRecording)
}

watch(recording, (isRecording) => {
    if (isRecording) {
        attachListeners()
    } else {
        detachListeners()
    }
})

onBeforeUnmount(() => {
    detachListeners()
})

const startRecording = () => {
    if (props.disabled) {
        return
    }

    error.value = null
    preview.value = null
    recording.value = true
    fieldRef.value?.focus()
}

const stopRecording = () => {
    recording.value = false
    error.value = null
}

const clearShortcut = () => {
    if (props.required) {
        return
    }

    modelValue.value = undefined
}

const displayValue = computed(() => {
    if (recording.value) {
        return formatDisplay(preview.value) ?? null
    }

    return formatDisplay(modelValue.value)
})

const hasClearButton = computed(() => Boolean(modelValue.value) && !props.required)
</script>

<template>
    <div :class="cn('flex w-full flex-col gap-1', props.class)">
        <div class="relative">
            <div
                ref="fieldRef"
                role="button"
                tabindex="0"
                :aria-disabled="props.disabled"
                :class="
                    cn(
                        'border-input dark:bg-input/30 flex h-9 w-full items-center justify-between rounded-md border bg-transparent px-3 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px]',
                        hasClearButton ? 'pr-11' : undefined,
                        recording
                            ? 'border-ring ring-ring/50 focus-visible:border-ring focus-visible:ring-ring/50'
                            : 'focus-visible:border-ring focus-visible:ring-ring/50',
                        props.disabled ? 'cursor-not-allowed opacity-60' : 'cursor-text'
                    )
                "
                @blur="stopRecording"
                @click="startRecording"
                @focus="startRecording"
            >
                <span :class="cn('truncate', !modelValue && !recording ? 'text-muted-foreground' : undefined)">
                    {{
                        displayValue ??
                        (recording ? $t('app.press a shortcut...') : (props.placeholder ?? $t('app.select shortcut')))
                    }}
                </span>
                <span v-if="recording" class="text-muted-foreground flex items-center gap-1 text-xs">
                    <Keyboard class="size-4" />
                    <span>{{ $t('app.recording') }}</span>
                </span>
            </div>
            <Button
                v-if="hasClearButton"
                class="absolute top-1 right-1 h-7 w-7"
                size="icon"
                variant="ghost"
                :disabled="props.disabled"
                @click.stop="clearShortcut"
            >
                <X class="size-4" />
            </Button>
        </div>
        <p v-if="error" class="text-destructive text-xs">
            {{ $t(error) }}
        </p>
    </div>
</template>
