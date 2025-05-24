<script lang="ts" setup>
import { Button } from '@/Components/ui/button'
import { Switch } from '@/Components/ui/switch'
import { Date } from '@/types'
import { Head, Link, useForm, usePoll } from '@inertiajs/vue3'
import { useColorMode } from '@vueuse/core'
import { ArrowRight, RefreshCcw } from 'lucide-vue-next'
import { watch } from 'vue'

const props = defineProps<{
    auto_update: boolean
    last_check: Date
    last_version: string
    is_downloaded: boolean
}>()

useColorMode()

if (!props.is_downloaded) {
    usePoll(
        1000,
        {
            only: ['last_check', 'last_version', 'is_downloaded'],
            showProgress: false
        },
        {
            autoStart: true
        }
    )
}

const form = useForm({
    auto_update: props.auto_update
})

watch(
    () => form.auto_update,
    () => form.patch(route('updater.updateAutoUpdate'))
)
</script>

<template>
    <Head title="Updater" />
    <div class="absolute inset-x-0 top-0 -z-10 h-14" style="-webkit-app-region: drag" />

    <div class="bg-background flex h-screen flex-col items-center justify-center gap-4 p-8 pt-10">
        <svg class="text-primary size-14 fill-current" viewBox="100 100 824 824" xmlns="http://www.w3.org/2000/svg">
            <g>
                <path
                    d=" M 318.02 100.59 C 448.00 99.80 577.99 99.79 707.98 100.60 C 744.58 100.98 782.34 102.76 816.39 117.57 C 860.57 136.21 895.99 174.28 911.41 219.69 C 926.65 264.79 923.51 313.14 924.14 360.00 C 923.92 477.34 924.59 594.69 923.80 712.01 C 923.50 738.87 922.17 766.02 915.25 792.10 C 901.22 847.63 857.56 894.27 803.15 912.09 C 792.36 915.68 781.23 918.03 770.07 920.08 C 767.71 920.29 765.34 920.46 763.06 921.09 C 760.05 921.35 757.01 921.38 754.08 922.12 C 741.04 922.99 728.00 923.70 714.93 923.85 C 579.37 924.51 443.63 924.50 308.07 923.86 C 295.66 923.63 283.28 922.93 270.90 922.13 C 267.96 921.42 264.92 921.34 261.92 921.10 C 259.62 920.50 257.26 920.30 254.91 920.09 C 251.26 919.26 247.57 918.68 243.89 918.07 C 228.70 914.78 213.77 910.03 199.83 903.11 C 157.82 882.53 125.01 844.18 111.37 799.41 C 98.97 758.84 100.85 715.90 100.45 673.99 C 99.72 567.33 99.84 460.67 99.85 354.00 C 101.65 306.75 97.80 257.59 115.72 212.72 C 136.95 157.40 188.70 115.85 247.00 105.97 C 270.39 101.55 294.28 100.87 318.02 100.59 M 618.39 365.24 C 598.37 379.47 585.61 404.21 588.89 428.97 C 591.31 458.07 613.67 479.38 631.93 500.03 C 651.18 520.05 670.80 542.14 676.73 570.13 C 679.44 586.19 679.60 603.48 672.80 618.62 C 661.27 646.28 622.77 653.87 599.03 637.84 C 583.15 625.81 578.97 600.89 590.21 584.38 C 600.05 569.01 619.07 564.09 636.20 563.07 C 634.76 549.96 623.93 539.44 611.40 536.48 C 597.00 532.76 581.61 535.67 568.46 542.23 C 534.00 560.68 524.72 608.08 541.21 641.62 C 558.07 673.66 597.90 681.81 631.07 680.60 C 664.25 679.15 699.17 669.33 722.62 644.57 C 752.55 612.84 757.74 562.08 738.74 523.50 C 722.01 486.70 682.91 468.88 660.61 436.46 C 646.06 416.07 645.49 379.08 671.58 367.73 C 684.55 362.67 700.52 361.71 712.94 368.82 C 725.58 378.37 726.57 396.89 720.79 410.60 C 717.39 418.66 712.26 426.28 704.69 430.95 C 707.00 433.97 710.28 435.92 713.52 437.81 C 725.40 442.70 740.23 439.51 748.88 429.95 C 761.66 414.51 763.37 391.56 755.82 373.41 C 749.13 360.55 736.71 351.58 722.87 347.79 C 687.81 338.52 648.12 343.74 618.39 365.24 M 311.41 369.47 C 296.16 379.50 282.73 393.02 274.75 409.59 C 267.66 423.92 264.62 440.06 264.68 455.98 C 264.35 470.47 267.67 488.29 282.49 494.77 C 291.29 498.89 301.25 500.12 310.89 500.02 C 303.58 487.94 305.02 473.45 305.03 459.97 C 306.37 437.82 312.41 413.94 329.75 398.77 C 347.29 384.01 371.21 381.41 393.27 381.45 C 372.92 478.71 351.79 575.85 330.86 673.00 C 351.85 673.00 372.83 673.00 393.82 673.00 C 414.16 578.72 434.22 484.39 454.72 390.15 C 477.82 395.34 501.22 400.18 525.00 400.03 C 538.58 399.08 553.42 396.59 563.28 386.27 C 574.23 375.42 576.87 359.39 577.89 344.67 C 541.18 359.55 500.85 353.47 462.98 347.24 C 412.08 340.67 356.23 341.17 311.41 369.47 Z"
                    opacity="1.00"
                />
            </g>
        </svg>

        <h1 class="text-2xl font-bold">{{ $t('app.update available') }}</h1>
        <p class="text-muted-foreground text-center text-sm text-balance">
            {{ $t('app.a new version of the app is available. please install the latest version to enjoy new features and improvements.') }}
        </p>
        <div class="flex items-center gap-4 text-sm font-medium">
            <div>{{ $t('app.current') }}: {{ $page.props.app_version }}</div>
            <ArrowRight class="size-4" />
            <div>{{ $t('app.new') }}: {{ props.last_version }}</div>
        </div>
        <div class="flex gap-4" v-if="props.is_downloaded">
            <Button :as="Link" :href="route('updater.install')" method="post">
                <RefreshCcw />
                {{ $t('app.update now') }}
            </Button>
        </div>
        <div class="flex grow flex-col justify-end">
            <div class="flex items-center gap-2 text-sm">
                <Switch id="auto-download" v-model="form.auto_update" />
                <label for="auto-download">{{ $t('app.automatic updates') }}</label>
            </div>
        </div>
    </div>
</template>
