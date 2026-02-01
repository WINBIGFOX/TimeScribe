<script lang="ts" setup>
import { cn } from '@/lib/utils'
import { Link } from '@inertiajs/vue3'
import { trans } from 'laravel-vue-i18n'
import { ChevronsLeft, ChevronsRight } from 'lucide-vue-next'
import moment from 'moment/min/moment-with-locales'
import { computed, type HTMLAttributes } from 'vue'

const props = defineProps<{
    class?: HTMLAttributes['class']
    date: string
    type: 'day' | 'week' | 'month' | 'year'
    route: string
}>()

const items = computed(() => {
    const currentMoment = moment(props.date, 'YYYY-MM-DD')
    const thirdPreviousMoment = currentMoment.clone().subtract(3, props.type)
    const secondPreviousMoment = currentMoment.clone().subtract(2, props.type)
    const previousMoment = currentMoment.clone().subtract(1, props.type)
    const nextMoment = currentMoment.clone().add(1, props.type)
    const secondNextMoment = currentMoment.clone().add(2, props.type)
    const thirdNextMoment = currentMoment.clone().add(3, props.type)

    const shortDateFormat =
        props.type === 'day'
            ? 'dd Do'
            : props.type === 'week'
              ? `[${trans('app.cw')}] W`
              : props.type === 'month'
                ? 'MMM YY'
                : 'YYYY'
    const longDateFormat =
        props.type === 'day'
            ? 'dddd L'
            : props.type === 'week'
              ? `[${currentMoment.clone().startOf('week').format('Do')}] – [${currentMoment.clone().endOf('week').format('Do MMM YY')}]`
              : props.type === 'month'
                ? 'MMMM YYYY'
                : 'YYYY'

    return {
        current: {
            label: currentMoment.format(longDateFormat),
            link: route(props.route, { date: currentMoment.format('YYYY-MM-DD') })
        },
        previous: {
            label: previousMoment.format(shortDateFormat),
            link: route(props.route, { date: previousMoment.format('YYYY-MM-DD') })
        },
        secondPrevious: {
            label: secondPreviousMoment.format(shortDateFormat),
            link: route(props.route, { date: secondPreviousMoment.format('YYYY-MM-DD') })
        },
        thirdPrevious: {
            label: thirdPreviousMoment.format(shortDateFormat),
            link: route(props.route, { date: thirdPreviousMoment.format('YYYY-MM-DD') })
        },
        next: {
            label: nextMoment.format(shortDateFormat),
            link: route(props.route, { date: nextMoment.format('YYYY-MM-DD') })
        },
        secondNext: {
            label: secondNextMoment.format(shortDateFormat),
            link: route(props.route, { date: secondNextMoment.format('YYYY-MM-DD') })
        },
        thirdNext: {
            label: thirdNextMoment.format(shortDateFormat),
            link: route(props.route, { date: thirdNextMoment.format('YYYY-MM-DD') })
        }
    }
})
</script>

<template>
    <div
        :class="
            cn('*:hover:text-foreground flex items-center tabular-nums *:text-center *:transition-colors', props.class)
        "
    >
        <Link :href="items.thirdPrevious.link" class="text-foreground/15" prefetch preserve-scroll>
            <ChevronsLeft class="size-4" />
        </Link>
        <Link :href="items.secondPrevious.link" class="text-foreground/25 pl-3" prefetch preserve-scroll>
            {{ items.secondPrevious.label }}
        </Link>
        <Link
            :href="items.previous.link"
            class="text-foreground/50 border-border ml-3 border-l pl-3"
            prefetch
            preserve-scroll
        >
            {{ items.previous.label }}
        </Link>
        <Link
            :href="items.current.link"
            class="border-border mx-3 border-x px-3 font-semibold"
            prefetch
            preserve-scroll
        >
            {{ items.current.label }}
        </Link>
        <Link
            :href="items.next.link"
            class="text-foreground/50 border-border mr-3 border-r pr-3"
            prefetch
            preserve-scroll
        >
            {{ items.next.label }}
        </Link>
        <Link :href="items.secondNext.link" class="text-foreground/25 pr-3" prefetch preserve-scroll>
            {{ items.secondNext.label }}
        </Link>
        <Link :href="items.thirdNext.link" class="text-foreground/15" prefetch preserve-scroll>
            <ChevronsRight class="size-4" />
        </Link>
    </div>
</template>
