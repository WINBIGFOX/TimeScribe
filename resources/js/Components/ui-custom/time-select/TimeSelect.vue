<script lang="ts" setup>
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue
} from '@/Components/ui/select'
import { cn } from '@/lib/utils'
import { computed, type HTMLAttributes, ref, watch } from 'vue'

const modelValue = defineModel<string>()

const props = withDefaults(
    defineProps<{
        label?: string
        disabled?: boolean
        class?: HTMLAttributes['class']
        min?: string
        max?: string
        twelveHourFormat?: boolean
    }>(),
    {
        min: '00:00',
        max: '23:59'
    }
)

const minHour = ref(0)
const minMinute = ref(0)
const maxHour = ref(23)
const maxMinute = ref(59)

const hourValue = ref()
const minuteValue = ref()

const parseModelValue = () => {
    if (!modelValue.value) {
        hourValue.value = minHour.value
        minuteValue.value = minMinute.value
        return
    }
    let [hour, minute] = modelValue.value.split(':').map(Number)
    if (isNaN(hour) || isNaN(minute)) {
        hour = minHour.value
        minute = minMinute.value
    }

    if (hourValue.value !== hour) {
        hourValue.value = hour
    }
    if (minuteValue.value !== minute) {
        minuteValue.value = minute
    }
}

const validation = () => {
    const [_minHour, _minMinute] = props.min.split(':').map(Number)
    const [_maxHour, _maxMinute] = props.max.split(':').map(Number)

    if (isNaN(_minHour) || isNaN(_minMinute)) {
        minHour.value = 0
        minMinute.value = 0
    }
    if (isNaN(_maxHour) || isNaN(_maxMinute)) {
        maxHour.value = 23
        maxMinute.value = 59
    }

    minHour.value = Math.max(0, _minHour)
    minMinute.value = Math.max(0, _minMinute)
    maxHour.value = Math.min(23, _maxHour)
    maxMinute.value = Math.min(59, _maxMinute)

    if (isNaN(hourValue.value) || hourValue.value < minHour.value) {
        hourValue.value = minHour.value
    }
    if (minuteValue.value < 0) {
        minuteValue.value = 0
    }
    if (minuteValue.value > 59) {
        minuteValue.value = 59
    }
    if (hourValue.value < 0) {
        hourValue.value = 0
    }
    if (hourValue.value > 23) {
        hourValue.value = 23
    }
    if (hourValue.value > maxHour.value) {
        hourValue.value = maxHour.value
    }
    if (isNaN(minuteValue.value)) {
        minuteValue.value = minMinute.value
    }
    if (hourValue.value === minHour.value && minuteValue.value < minMinute.value) {
        minuteValue.value = minMinute.value
    }
    if (hourValue.value === maxHour.value && minuteValue.value > maxMinute.value) {
        minuteValue.value = maxMinute.value
    }
}

const formatModelValue = () => {
    modelValue.value = `${String(hourValue.value).padStart(2, '0')}:${String(minuteValue.value).padStart(2, '0')}`
}

const hourLabel = (hour: number, withoutTwelveHourFormat = false) => {
    if (props.twelveHourFormat) {
        if (hour === 0) {
            return withoutTwelveHourFormat ? '12' : '12 AM'
        }
        if (hour === 12) {
            return withoutTwelveHourFormat ? '12' : '12 PM'
        }
        if (hour > 12) {
            return withoutTwelveHourFormat ? String(hour - 12) : `${hour - 12} PM`
        }
        if (hour < 12) {
            return withoutTwelveHourFormat ? String(hour) : `${hour} AM`
        }
    }
    return String(hour).padStart(2, '0')
}

const hourSelect = computed(() => {
    const hourOptions: { value: number; label: string }[] = []
    for (let i = minHour.value; i <= maxHour.value; i++) {
        hourOptions.push({
            value: i,
            label: hourLabel(i)
        })
    }
    return hourOptions
})

const minuteSelect = computed(() => {
    let internalMinMinute = minMinute.value
    let internalMaxMinute = maxMinute.value

    if (hourValue.value !== minHour.value) {
        internalMinMinute = 0
    } else {
        internalMinMinute = Math.max(internalMinMinute, 0)
    }

    if (hourValue.value !== maxHour.value) {
        internalMaxMinute = 59
    } else {
        internalMaxMinute = Math.min(internalMaxMinute, 59)
    }

    const minuteOptions: { value: number; label: string }[] = []
    for (let i = internalMinMinute; i <= internalMaxMinute; i++) {
        minuteOptions.push({
            value: i,
            label: String(i).padStart(2, '0')
        })
    }
    return minuteOptions
})

parseModelValue()
validation()
formatModelValue()

watch(modelValue, () => {
    parseModelValue()
    validation()
})

watch(hourValue, () => {
    validation()
    formatModelValue()
})

watch(minuteValue, () => {
    validation()
    formatModelValue()
})

watch(
    () => [props.max, props.min],
    () => {
        validation()
        formatModelValue()
    }
)
</script>

<template>
    <div
        :class="
            cn('*:hover:text-foreground flex items-center tabular-nums *:text-center *:transition-colors', props.class)
        "
    >
        <div
            class="border-input dark:bg-input/30 flex w-fit items-center rounded-md border bg-transparent text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px]"
        >
            <div class="pl-3" v-if="props.label">{{ props.label }}</div>

            <Select :disabled="props.disabled" v-model="hourValue">
                <SelectTrigger
                    class="data-[placeholder]:text-foreground rounded-r-none border-none pr-1 shadow-none dark:bg-transparent [&_svg]:hidden"
                >
                    <SelectValue placeholder="00">
                        {{ hourLabel(hourValue, true) }}
                    </SelectValue>
                </SelectTrigger>
                <SelectContent class="min-w-min">
                    <SelectGroup>
                        <SelectLabel>{{ $t('app.hour') }}</SelectLabel>
                        <SelectItem
                            :key="hour.value"
                            :value="hour.value"
                            class="data-[state=checked]:text-primary data-[state=checked]:bg-primary/10 justify-center px-0 [&_span.absolute]:hidden"
                            v-for="hour in hourSelect"
                        >
                            {{ hour.label }}
                        </SelectItem>
                    </SelectGroup>
                </SelectContent>
            </Select>
            :
            <Select :disabled="props.disabled" v-model="minuteValue">
                <SelectTrigger
                    :class="{
                        'pr-2': props.twelveHourFormat
                    }"
                    class="data-[placeholder]:text-foreground rounded-l-none border-none pl-1 shadow-none dark:bg-transparent [&_svg]:hidden"
                >
                    <SelectValue placeholder="00" />
                </SelectTrigger>
                <SelectContent class="min-w-min">
                    <SelectGroup>
                        <SelectLabel>{{ $t('app.minute') }}</SelectLabel>
                        <SelectItem
                            :key="minute.value"
                            :value="minute.value"
                            class="data-[state=checked]:text-primary data-[state=checked]:bg-primary/10 justify-center px-0 [&_span.absolute]:hidden"
                            v-for="minute in minuteSelect"
                        >
                            {{ minute.label }}
                        </SelectItem>
                    </SelectGroup>
                </SelectContent>
            </Select>
            <span class="pr-4" v-if="props.twelveHourFormat">
                {{ hourValue <= 11 ? 'AM' : 'PM' }}
            </span>
        </div>
    </div>
</template>
