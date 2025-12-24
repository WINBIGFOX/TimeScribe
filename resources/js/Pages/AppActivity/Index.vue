<script lang="ts" setup>
import DateRangePicker from '@/Components/DateRangePicker.vue'
import { EmptyState } from '@/Components/ui-custom/empty-state'
import { PageHeader } from '@/Components/ui-custom/page-header'
import { categoryIcon } from '@/lib/utils'
import { AppActivityHistory } from '@/types'
import { Head, router, usePage, usePoll } from '@inertiajs/vue3'
import { trans } from 'laravel-vue-i18n'
import { computed, ref, watch } from 'vue'

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

const appBar = computed(() => {
    const sum = props.historyApp.reduce((acc, app) => {
        return acc + app.sum
    }, 0)
    return props.historyApp.map((app) => {
        return {
            item: app,
            percentage: ((app.sum / sum) * 100).toFixed(2)
        }
    })
})

const categoryBar = computed(() => {
    const sum = props.historyCategory.reduce((acc, category) => {
        return acc + category.sum
    }, 0)
    return props.historyCategory.map((category) => {
        return {
            item: category,
            percentage: ((category.sum / sum) * 100).toFixed(2)
        }
    })
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
        class="mb-4 flex h-10 shrink-0 gap-10 transition-opacity duration-500"
        v-if="props.historyApp.length"
    >
        <div class="flex-1">
            <div class="flex gap-[1px]">
                <template :key="item.item.identifier" v-for="item in appBar">
                    <div
                        :style="{
                            width: item.percentage + '%',
                            background: item.item.color
                        }"
                        class="flex h-10 items-center justify-center rounded transition-[scale] hover:z-10 hover:scale-110"
                    >
                        <img
                            :alt="item.item.name"
                            :src="item.item.icon"
                            class="size-6"
                            onerror="this.style.opacity = '0'"
                            v-if="parseInt(item.percentage) > 10"
                        />
                    </div>
                </template>
            </div>
        </div>
        <div class="flex-1">
            <div class="flex gap-[1px]">
                <template :key="item.item.identifier" v-for="item in categoryBar">
                    <div
                        :style="{
                            width: item.percentage + '%',
                            background: item.item.color
                        }"
                        class="flex h-10 items-center justify-center rounded transition-[scale] hover:z-10 hover:scale-110"
                    >
                        <span v-if="parseInt(item.percentage) > 10">
                            {{ categoryIcon(item.item.identifier) }}
                        </span>
                    </div>
                </template>
            </div>
        </div>
    </div>
    <div
        :class="{ 'opacity-50': loading }"
        class="flex grow gap-10 overflow-hidden transition-opacity duration-500"
        v-if="props.historyApp.length"
    >
        <div class="flex flex-1 flex-col gap-1.5 overflow-y-auto pb-4 text-sm">
            <div
                :key="app.identifier"
                :style="{ 'border-color': app.color }"
                class="flex items-center gap-1 border-r-4 pr-2"
                v-for="app in props.historyApp"
            >
                <img :alt="app.name" :src="app.icon" class="size-5" onerror="this.style.opacity = '0'" />

                {{ app.name }}
                <div class="text-muted-foreground ml-auto tabular-nums" v-if="app.sum >= 60">
                    {{ secToFormat(app.sum) }}
                </div>
                <div class="text-muted-foreground ml-auto tabular-nums" v-else>> 1 {{ $t('app.min') }}</div>
            </div>
        </div>
        <div class="flex flex-1 flex-col gap-1.5 overflow-y-auto pb-4 text-sm">
            <div
                :key="category.name"
                :style="{ 'border-color': category.color }"
                class="flex items-center gap-1 border-r-4 pr-2"
                v-for="category in props.historyCategory"
            >
                {{ categoryIcon(category.identifier) }}
                {{ category.name }}
                <div class="text-muted-foreground ml-auto tabular-nums" v-if="category.sum >= 60">
                    {{ secToFormat(category.sum) }}
                </div>
                <div class="text-muted-foreground ml-auto tabular-nums" v-else>> 1 {{ $t('app.min') }}</div>
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
            v-if="props.active"
            :title="$t('app.no app activity recorded')"
            :description="
                $t(
                    'app.no app activity has been recorded yet. start the working time timer to record the app activity.'
                )
            "
        />
        <EmptyState
            v-else
            :title="$t('app.app activity is deactivated')"
            :description="
                $t(
                    'app.activity recording is deactivated. Activate \'record app activity\' in the settings, to record future app activities.'
                )
            "
            :action-href="route('settings.general.edit')"
            :action-label="$t('app.go to settings')"
        />
    </div>
</template>
