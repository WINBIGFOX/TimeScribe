<script lang="ts" setup>
import TimestampTypeBadge from '@/Components/TimestampTypeBadge.vue'
import { PageHeader } from '@/Components/ui-custom/page-header'
import { TimeWheel } from '@/Components/ui-custom/time-wheel'
import { Button } from '@/Components/ui/button'
import { secToFormat } from '@/lib/utils'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { useCssVar } from '@vueuse/core'
import { ApexOptions } from 'apexcharts'
import de from 'apexcharts/dist/locales/de.json'
import en from 'apexcharts/dist/locales/en.json'
import fr from 'apexcharts/dist/locales/fr.json'
import it from 'apexcharts/dist/locales/it.json'
import ptBr from 'apexcharts/dist/locales/pt-br.json'
import zhCn from 'apexcharts/dist/locales/zh-cn.json'
import { trans } from 'laravel-vue-i18n'
import moment from 'moment/min/moment-with-locales'

const props = defineProps<{
    date: string
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

const showWeek = (opts) => {
    router.get(props.links[opts.dataPointIndex], {
        preserveScroll: true,
        preserveState: true
    })
}

const localeMapping = {
    'da-DK': 'da',
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

const data = {
    series: buildSeries(),
    chartOptions: {
        colors: buildColors(),
        chart: {
            events: {
                dataPointSelection: (_1, _2, opts) => showWeek(opts)
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
                borderRadiusWhenStacked: 'last' // 'all', 'last'
            }
        },
        dataLabels: {
            enabled: false
        },
        xaxis: {
            stepSize: 0,
            type: 'datetime',
            categories: props.xaxis,
            labels: {
                hideOverlappingLabels: false,
                format: 'dd',
                datetimeFormatter: {
                    day: 'dd'
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
                formatter: (value) => {
                    return moment(value, 'x').format('dd. D MMMM')
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
    <Head title="Month Overview" />
    <PageHeader :title="$t('app.monthly overview')">
        <div class="flex flex-1 items-center justify-center text-sm">
            <TimeWheel :date="props.date" route="overview.month.show" type="month" />
        </div>
        <Button
            :as="Link"
            :href="route('overview.month.show', { date: moment().format('YYYY-MM-DD') })"
            prefetch
            size="sm"
            variant="outline"
        >
            {{ $t('app.today') }}
        </Button>
    </PageHeader>
    <div class="mb-6 h-full">
        <apexchart :options="data.chartOptions" :series="data.series" height="100%" type="bar"></apexchart>
    </div>
    <div class="flex gap-2">
        <TimestampTypeBadge :duration="props.sumWorkTime" type="work" />
        <TimestampTypeBadge :duration="props.sumBreakTime" type="break" />
        <TimestampTypeBadge v-if="props.hasWorkSchedules" :duration="Math.max(props.sumOvertime, 0)" type="overtime" />
        <TimestampTypeBadge v-if="props.hasWorkSchedules" :duration="(props.sumPlan ?? 0) * 60 * 60" type="plan" />
    </div>
</template>
