<script lang="ts" setup>
import DateRangePicker from '@/Components/DateRangePicker.vue'
import { EmptyState } from '@/Components/ui-custom/empty-state'
import { PageHeader } from '@/Components/ui-custom/page-header'
import { categoryIcon } from '@/lib/utils'
import { AppActivityHistory } from '@/types'
import { Head, router, usePage, usePoll } from '@inertiajs/vue3'
import { trans } from 'laravel-vue-i18n'
import { nextTick, onMounted, onUnmounted, ref, watch } from 'vue'

const props = defineProps<{
    historyApp: {
        name: string
        icon: string
        identifier: string
        category: string
        color: string
        sum: number
        count: number
        items: AppActivityHistory[]
    }[]
    historyCategory: {
        name: string
        color: string
        sum: number
        identifier: string
        count: number
        items: AppActivityHistory[]
    }[]
    startDate: string
    endDate: string
    minDate: string
    maxDate: string
    active: boolean
}>()

const startAnimation = ref(false)

onMounted(() => {
    nextTick(() => {
        startAnimation.value = true
    })
})
onUnmounted(() => {
    startAnimation.value = false
})

const secToFormat = (seconds: number) => {
    const hours = Math.floor(seconds / 3600)
    const minutes = Math.floor((seconds % 3600) / 60)

    return hours > 0 ? `${hours} ${trans('app.h')} ${minutes} ${trans('app.min')}` : `${minutes} ${trans('app.min')}`
}

const dateRange = ref({
    start: props.startDate,
    end: props.endDate
})

const loading = ref(false)
watch(dateRange, () => {
    router.reload({
        data: {
            startDate: dateRange.value.start,
            endDate: dateRange.value.end
        },
        onStart: () => {
            loading.value = true
        },
        onFinish: () => {
            loading.value = false
        },
        showProgress: true
    })
})
const page = usePage()
const { stop, start } = usePoll(
    15 * 1000,
    {
        onSuccess: () => {
            if (!page.props.recording) {
                stop()
            }
        },
        showProgress: false
    },
    {
        autoStart: page.props.recording
    }
)

if (window.Native) {
    window.Native.on('App\\Events\\TimerStarted', () => {
        router.flushAll()
        router.reload({
            showProgress: false,
            onSuccess: () => {
                start()
            }
        })
    })
}
</script>

<template>
    <Head title="App-Activity" />
    <PageHeader :title="$t('app.app activities')">
        <DateRangePicker :max="props.maxDate" :min="props.minDate" v-model="dateRange" />
    </PageHeader>
    <div
        :class="{ 'opacity-50': loading }"
        class="flex grow gap-10 overflow-hidden transition-opacity duration-500"
        v-if="props.historyApp.length"
    >
        <div class="flex flex-1 flex-col gap-1.5 overflow-y-auto pb-4 text-sm">
            <div :key="app.identifier" class="flex items-center gap-1.5" v-for="app in props.historyApp">
                <img :alt="app.name" :src="app.icon" class="size-10" onerror="this.style.opacity = '0'" />
                <div class="flex flex-1 flex-col gap-1.5">
                    <div class="flex justify-between gap-2 leading-none">
                        <span>{{ app.name }}</span>
                        <div class="text-muted-foreground ml-auto tabular-nums" v-if="app.sum >= 60">
                            {{ secToFormat(app.sum) }}
                        </div>
                        <div class="text-muted-foreground ml-auto tabular-nums" v-else>> 1 {{ $t('app.min') }}</div>
                    </div>
                    <div class="bg-muted h-1.75 shrink-0 overflow-hidden rounded-full">
                        <div
                            :style="`width: ${startAnimation ? (app.sum / props.historyApp[0].sum) * 100 : 0}%`"
                            class="bg-primary h-full w-0 rounded-full transition-[width] duration-1500"
                        />
                    </div>
                </div>
            </div>
        </div>
        <div class="flex flex-1 flex-col gap-1.5 overflow-y-auto pb-4 text-sm">
            <div :key="category.name" class="flex items-center gap-1" v-for="category in props.historyCategory">
                <span class="flex size-10 items-center justify-center text-2xl leading-none">
                    {{ categoryIcon(category.identifier) }}
                </span>
                <div class="flex flex-1 flex-col gap-1.5">
                    <div class="flex justify-between gap-2 leading-none">
                        <span>{{ category.name }}</span>
                        <div class="text-muted-foreground ml-auto tabular-nums" v-if="category.sum >= 60">
                            {{ secToFormat(category.sum) }}
                        </div>
                        <div class="text-muted-foreground ml-auto tabular-nums" v-else>> 1 {{ $t('app.min') }}</div>
                    </div>
                    <div class="bg-muted h-1.75 shrink-0 overflow-hidden rounded-full">
                        <div
                            :style="`width: ${startAnimation ? (category.sum / props.historyCategory[0].sum) * 100 : 0}%`"
                            class="h-full w-0 rounded-full bg-pink-400 transition-[width] duration-1500"
                        />
                    </div>
                </div>
            </div>
            <div class="flex justify-center pt-10">
                <span
                    class="text-destructive bg-destructive/20 ml-2 rounded px-1.5 py-0.5 text-sm"
                    v-if="$page.props.environment === 'Windows'"
                >
                    {{ $t('app.not available on windows') }}
                </span>
            </div>
        </div>
    </div>
    <div class="flex grow items-center justify-center" v-else>
        <EmptyState
            :description="
                $t(
                    'app.no app activity has been recorded yet. start the working time timer to record the app activity.'
                )
            "
            :title="$t('app.no app activity recorded')"
            v-if="props.active"
        />
        <EmptyState
            :action-href="route('settings.general.edit')"
            :action-label="$t('app.go to settings')"
            :description="
                $t(
                    'app.activity recording is deactivated. Activate \'record app activity\' in the settings, to record future app activities.'
                )
            "
            :title="$t('app.app activity is deactivated')"
            v-else
        />
    </div>
</template>
