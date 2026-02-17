<script lang="ts" setup>
import TimestampTypeBadge from '@/Components/TimestampTypeBadge.vue'
import { Calendar } from '@/Components/ui/calendar'
import { Button } from '@/Components/ui/button'
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover'
import { PageHeader } from '@/Components/ui-custom/page-header'
import { secToFormat } from '@/lib/utils'
import { Head, router, usePage } from '@inertiajs/vue3'
import { useCssVar } from '@vueuse/core'
import { useDateFormatter } from 'reka-ui'
import { ApexOptions } from 'apexcharts'
import de from 'apexcharts/dist/locales/de.json'
import en from 'apexcharts/dist/locales/en.json'
import fr from 'apexcharts/dist/locales/fr.json'
import it from 'apexcharts/dist/locales/it.json'
import ptBr from 'apexcharts/dist/locales/pt-br.json'
import zhCn from 'apexcharts/dist/locales/zh-cn.json'
import { parseDate } from '@internationalized/date'
import { trans } from 'laravel-vue-i18n'
import moment from 'moment/min/moment-with-locales'
import { computed, ref } from 'vue'

const props = defineProps<{
    startDate: string
    endDate: string
    workTimes: number[]
    breakTimes: number[]
    plans: number[]
    overtimes: number[]
    xaxis: string[]
    hasWorkSchedules: boolean
    sumBreakTime: number
    sumWorkTime: number
    sumOvertime: number
    sumPlan: number
    links: string[]
}>()

const startValue = ref(parseDate(props.startDate))
const endValue = ref(parseDate(props.endDate))
const startOpen = ref(false)
const endOpen = ref(false)

const locale = usePage().props.js_locale as string
const formatter = useDateFormatter(locale)

function navigate(start: string, end: string) {
    router.get(
        route('overview.range.show', { start, end }),
        {},
        { preserveScroll: true, preserveState: false }
    )
}

function selectStart(val) {
    startOpen.value = false
    const start = val.compare(endValue.value) > 0 ? endValue.value : val
    const end = val.compare(endValue.value) > 0 ? val : endValue.value
    navigate(start.toString(), end.toString())
}

function selectEnd(val) {
    endOpen.value = false
    const start = val.compare(startValue.value) < 0 ? val : startValue.value
    const end = val.compare(startValue.value) < 0 ? startValue.value : val
    navigate(start.toString(), end.toString())
}

const showDay = (opts) => {
    router.get(props.links[opts.dataPointIndex])
}

const localeMapping = {
    'da-DK': 'en',
    'de-DE': 'de',
    'en-GB': 'en',
    'en-US': 'en',
    'fr-FR': 'fr',
    'fr-CA': 'fr',
    'it-IT': 'it',
    'pt-BR': 'pt-br',
    'zh-CN': 'zh-cn'
}
const currentLocale = localeMapping[usePage().props.js_locale]

const buildSeries = () => {
    const series = [] as Record<string, string | number[]>[]
    series.push({
        name: trans('app.work hours'),
        data: props.workTimes
    })
    if (props.hasWorkSchedules) {
        series.push({
            name: trans('app.overtime'),
            data: props.overtimes
        })
    }
    series.push({
        name: trans('app.break time'),
        data: props.breakTimes
    })
    return series
}

const buildColors = () => {
    const colors = [] as string[]
    colors.push('var(--color-primary)')
    if (props.hasWorkSchedules) {
        colors.push('var(--color-amber-400)')
    }
    colors.push('var(--color-pink-400)')
    return colors
}

const series = computed(() => buildSeries())
const categories = computed(() => props.xaxis)

const data = {
    chartOptions: {
        colors: buildColors(),
        chart: {
            events: {
                dataPointSelection: (_1, _2, opts) => showDay(opts)
            },
            background: 'transparent',
            fontFamily: 'var(--font-sans)',
            locales: [de, en, fr, it, ptBr, zhCn],
            defaultLocale: currentLocale,
            type: 'bar',
            stacked: true,
            toolbar: {
                show: false
            },
            zoom: {
                enabled: false
            },
            animations: {
                enabled: false
            },
            parentHeightOffset: 0,
            offsetX: 0
        },
        plotOptions: {
            bar: {
                horizontal: false,
                borderRadius: 2,
                borderRadiusApplication: 'end',
                borderRadiusWhenStacked: 'last'
            }
        },
        dataLabels: {
            enabled: false
        },
        xaxis: {
            type: 'category',
            categories: [],
            labels: {
                hideOverlappingLabels: true,
                rotate: 0,
                formatter: (value: string) => {
                    const m = moment(value, 'YYYY-MM-DD')
                    return m.date() === 1 ? m.format('D MMM') : String(m.date())
                },
                style: {
                    colors: 'var(--color-foreground)',
                    fontSize: 'var(--text-xs)',
                    fontWeight: 'var(--font-normal)',
                    cssClass: ''
                }
            },
            axisBorder: {
                show: true,
                color: 'var(--color-sidebar-border)'
            },
            axisTicks: {
                show: true,
                borderType: 'solid',
                color: 'var(--color-sidebar-border)',
                width: 6
            }
        },
        noData: {
            text: trans('app.no times available'),
            style: {
                color: 'var(--color-foreground)'
            }
        },
        yaxis: {
            stepSize: 7200,
            labels: {
                offsetX: -15,
                style: {
                    colors: 'var(--color-foreground)',
                    fontSize: '12px',
                    cssClass: ''
                },
                formatter: (value) => {
                    return secToFormat(value, true, true, true)
                }
            },
            axisBorder: {
                show: true,
                color: 'var(--color-sidebar-border)'
            },
            axisTicks: {
                show: true,
                borderType: 'solid',
                color: 'var(--color-sidebar-border)',
                width: 6
            }
        },
        grid: {
            borderColor: 'var(--color-sidebar-border)',
            strokeDashArray: 2,
            row: {
                opacity: 0
            },
            padding: {
                left: -5,
                right: -5
            }
        },
        states: {
            active: {
                filter: {
                    type: 'none'
                }
            },
            hover: {
                filter: {
                    type: 'none'
                }
            }
        },
        fill: {
            opacity: 1
        },
        tooltip: {
            style: {
                fontSize: useCssVar('--text-sm').value
            },
            x: {
                formatter: (_value, { dataPointIndex }) => {
                    return moment(props.xaxis[dataPointIndex]).format('dd. D MMMM')
                }
            },
            y: {
                formatter: (value) => {
                    const time = secToFormat(value, true, true, true)
                    if (value >= 3600) {
                        return `${time} ${trans('app.h')}`
                    }
                    return `${time} ${trans('app.min')}`
                }
            }
        },
        legend: {
            position: 'top',
            horizontalAlign: 'left',
            labels: {
                colors: 'var(--color-foreground)'
            },
            fontSize: '14px',
            offsetX: -35,
            offsetY: 0,
            markers: {
                size: 6,
                shape: 'circle',
                offsetX: -4,
                strokeWidth: 0
            },
            itemMargin: {
                horizontal: 10,
                vertical: 0
            }
        }
    } as ApexOptions
}

const reload = () => {
    router.flushAll()
    router.reload({
        showProgress: false
    })
}

if (window.Native) {
    window.Native.on('App\\Events\\TimerStarted', reload)
    window.Native.on('App\\Events\\TimerStopped', reload)
}
</script>

<template>
    <Head title="Period Overview" />
    <PageHeader :title="$t('app.date range overview')">
        <div class="flex flex-1 items-center justify-center gap-2 text-sm">
            <Popover v-model:open="startOpen">
                <PopoverTrigger as-child>
                    <Button variant="outline" size="sm">
                        {{ formatter.custom(startValue.toDate('UTC'), { dateStyle: 'medium' }) }}
                    </Button>
                </PopoverTrigger>
                <PopoverContent class="w-auto p-0">
                    <Calendar
                        :locale="locale"
                        :model-value="startValue"
                        @update:model-value="selectStart"
                    />
                </PopoverContent>
            </Popover>
            <span class="text-muted-foreground">–</span>
            <Popover v-model:open="endOpen">
                <PopoverTrigger as-child>
                    <Button variant="outline" size="sm">
                        {{ formatter.custom(endValue.toDate('UTC'), { dateStyle: 'medium' }) }}
                    </Button>
                </PopoverTrigger>
                <PopoverContent class="w-auto p-0">
                    <Calendar
                        :locale="locale"
                        :model-value="endValue"
                        @update:model-value="selectEnd"
                    />
                </PopoverContent>
            </Popover>
        </div>
    </PageHeader>
    <div class="mb-6 h-full">
        <apexchart
            :options="{ ...data.chartOptions, xaxis: { ...data.chartOptions.xaxis, categories: categories } }"
            :series="series"
            height="100%"
            type="bar"
        ></apexchart>
    </div>
    <div class="flex gap-2">
        <TimestampTypeBadge :duration="props.sumWorkTime" type="work" />
        <TimestampTypeBadge :duration="props.sumBreakTime" type="break" />
        <TimestampTypeBadge v-if="props.hasWorkSchedules" :duration="Math.max(props.sumOvertime, 0)" type="overtime" />
        <TimestampTypeBadge v-if="props.hasWorkSchedules" :duration="(props.sumPlan ?? 0) * 60 * 60" type="plan" />
    </div>
</template>
