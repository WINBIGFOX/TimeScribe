<script lang="ts" setup>
import { EmptyState } from '@/Components/ui-custom/empty-state'
import { Button } from '@/Components/ui/button'
import BasicLayout from '@/Layouts/BasicLayout.vue'
import { secToFormat } from '@/lib/utils'
import { ActivityHistory, Project } from '@/types'
import { Head, Link, router, usePoll } from '@inertiajs/vue3'
import { useColorMode } from '@vueuse/core'
import { ChartColumnBig, Coffee, Cog, PictureInPicture, Play, Plus, Sparkles, Square, Tag, X } from 'lucide-vue-next'
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'

defineOptions({
    layout: BasicLayout
})

const props = defineProps<{
    currentType?: 'work' | 'break'
    workTime: number
    breakTime: number
    currentProject?: Project
    currentAppActivity?: ActivityHistory
    activeAppActivity: boolean
    updateAvailable: boolean
    projects?: Project[]
}>()

let timer: NodeJS.Timeout

const workSeconds = ref(props.workTime)
const breakSeconds = ref(props.breakTime)

const workTimeFormatted = computed(() => secToFormat(workSeconds.value))
const breakTimeFormatted = computed(() => secToFormat(breakSeconds.value, true))
const openProjectList = ref(false)
const showProject = ref(!!props.currentProject)

const hideProjectList = () => {
    openProjectList.value = false
}

const clearAutoFocus = () => {
    window.requestAnimationFrame(() => {
        const activeElement = document.activeElement

        if (activeElement instanceof HTMLElement && activeElement !== document.body) {
            activeElement.blur()
        }
    })
}

const showProjectList = () => {
    router.reload({
        only: ['projects'],
        onSuccess: () => {
            openProjectList.value = true
        }
    })
}

const tick = () => {
    if (props.currentType === 'work') {
        workSeconds.value += 1
    } else if (props.currentType === 'break') {
        breakSeconds.value += 1
    }
}

const reload = () => {
    router.flushAll()
    router.reload({
        only: ['workTime', 'breakTime', 'currentType', 'currentProject'],
        showProgress: false,
        onFinish: clearAutoFocus
    })
}

if (window.Native) {
    window.Native.on('App\\Events\\TimerStarted', reload)
    window.Native.on('App\\Events\\TimerStopped', reload)
    window.Native.on('Native\\Desktop\\Events\\MenuBar\\MenuBarShown', reload)
    window.Native.on('Native\\Desktop\\Events\\MenuBar\\MenuBarHidden', hideProjectList)
    window.Native.on('App\\Events\\MenubarProjectPickerRequested', showProjectList)
}

onMounted(() => {
    timer = setInterval(tick, 1000)
    clearAutoFocus()
})

usePoll(
    5000,
    {
        only: ['currentAppActivity', 'updateAvailable']
    },
    {
        autoStart: props.activeAppActivity && (props.currentType === 'work' || props.currentType === 'break'),
        keepAlive: true
    }
)

onBeforeUnmount(() => {
    clearInterval(timer)
})

watch(
    () => props.workTime,
    (newVal) => {
        workSeconds.value = newVal
    }
)

watch(
    () => props.breakTime,
    (newVal) => {
        breakSeconds.value = newVal
    }
)

const { state } = useColorMode()

const loading = ref(false)

router.on('start', () => {
    loading.value = true
})
router.on('finish', () => {
    loading.value = false
})

const setProject = (project: Project) => {
    hideProjectList()
    showProject.value = true
    router.post(
        route('menubar.set-project', { project: project.id }),
        {},
        {
            preserveScroll: true,
            preserveState: true
        }
    )
}

const removeProject = () => {
    showProject.value = false
    router.post(
        route('menubar.remove-project'),
        {},
        {
            preserveScroll: true,
            preserveState: true
        }
    )
}
</script>

<template>
    <Head title="Menubar" />

    <!-- Prevent focus on page load -->
    <button class="opacity-0" type="button"></button>

    <div class="bg-background flex h-dvh flex-col select-none">
        <div class="fixed inset-x-0 top-0 flex justify-end">
            <Button
                :as="Link"
                :href="
                    route('window.updater.open', {
                        darkMode: state === 'dark' ? 1 : 0
                    })
                "
                class="text-primary flex flex-1 items-center justify-start gap-2 py-2 text-sm"
                preserve-scroll
                preserve-state
                v-if="props.updateAvailable"
                variant="ghost"
            >
                <Sparkles class="size-4" />
                {{ $t('app.update available') }}
            </Button>
            <div>
                <Button
                    :as="Link"
                    :href="route('window.fly-timer.open')"
                    preserve-scroll
                    preserve-state
                    size="icon"
                    variant="ghost"
                >
                    <PictureInPicture />
                </Button>
                <Button
                    :as="Link"
                    :href="
                        route('window.settings.open', {
                            darkMode: state === 'dark' ? 1 : 0
                        })
                    "
                    preserve-scroll
                    preserve-state
                    size="icon"
                    variant="ghost"
                >
                    <Cog />
                </Button>
            </div>
        </div>
        <div
            :class="{
                'pt-8': props.currentType !== 'work' || !props.currentAppActivity || props.updateAvailable,
                'pt-0': props.currentType === 'work' && props.currentAppActivity && !props.updateAvailable
            }"
            class="flex shrink-0 grow flex-col items-center justify-center transition-all duration-1000"
        >
            <div
                :class="{
                    'opacity-50': props.currentType === 'break'
                }"
                class="text-center transition-opacity duration-1000"
            >
                <div
                    :class="{
                        'mb-2 h-10 scale-100 opacity-100': props.currentType === 'work' && props.currentAppActivity,
                        'mb-0 h-0 scale-0 opacity-0': props.currentType !== 'work' || !props.currentAppActivity
                    }"
                    class="flex items-center justify-center gap-2 overflow-hidden transition-all duration-1000"
                >
                    <img
                        :src="props.currentAppActivity.app_icon"
                        alt="App-Icon"
                        class="pointer-events-none size-10"
                        v-if="props.currentAppActivity?.app_icon"
                    />
                    <span>
                        {{ props.currentAppActivity?.app_name }}
                    </span>
                </div>

                <div
                    :class="{
                        'text-4xl': props.currentType !== 'break',
                        'text-2xl': props.currentType === 'break'
                    }"
                    class="font-bold tracking-tighter tabular-nums transition-all duration-1000"
                >
                    {{ workTimeFormatted }}
                </div>
                <div
                    :class="{
                        'text-[0.70rem]': props.currentType !== 'break',
                        'text-[0.50rem]': props.currentType === 'break'
                    }"
                    class="text-muted-foreground uppercase transition-all duration-1000"
                >
                    {{ $t('app.work hours') }}
                </div>
            </div>
            <transition
                class="transform-gpu transition-all duration-1000"
                enter-from-class="opacity-0 scale-0 h-0"
                enter-to-class="opacity-100 scale-100 h-14"
                leave-from-class="opacity-100 scale-100 h-14"
                leave-to-class="opacity-0 scale-0 h-0"
            >
                <div class="text-center" v-if="props.currentType === 'break'">
                    <div class="text-4xl font-bold tracking-tighter tabular-nums transition-all duration-1000">
                        {{ breakTimeFormatted }}
                    </div>
                    <div class="text-muted-foreground text-[0.70rem] uppercase transition-all duration-1000">
                        {{ $t('app.break') }}
                    </div>
                </div>
            </transition>
        </div>
        <div class="flex flex-col overflow-hidden">
            <Transition
                class="transform-gpu overflow-clip transition-all duration-1000"
                enter-from-class="opacity-80 max-h-0"
                enter-to-class="opacity-100 max-h-14"
                leave-from-class="opacity-100 max-h-14"
                leave-to-class="opacity-80 max-h-0"
            >
                <div v-if="props.currentProject && showProject" class="px-2">
                    <div
                        :style="'--project-color: ' + (props.currentProject.color ?? '#000000')"
                        class="transition-color mt-2 flex h-9 shrink-0 items-center gap-2 rounded-md border-l-6 border-l-(--project-color) bg-(--project-color)/10 pl-2 text-sm font-medium duration-1000 dark:bg-(--project-color)/20"
                    >
                        <div class="flex h-9 shrink-0 items-center text-xl" v-if="props.currentProject.icon">
                            {{ props.currentProject.icon }}
                        </div>
                        <div class="line-clamp-1">
                            {{ props.currentProject.name }}
                        </div>
                        <Button
                            @click="removeProject"
                            class="mr-0.5 ml-auto px-2! shadow-none"
                            size="sm"
                            variant="outline"
                        >
                            <X class="size-4" />
                        </Button>
                    </div>
                </div>
            </Transition>
            <Transition
                class="transform-gpu transition-all duration-500"
                enter-from-class="opacity-80 translate-y-full"
                enter-to-class="opacity-100 translate-y-0"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-80 translate-y-full"
            >
                <div
                    v-if="props.projects && openProjectList"
                    class="bg-background absolute inset-0 flex grow flex-col overflow-hidden"
                >
                    <div class="flex items-center justify-between px-2 py-2">
                        <div class="text-muted-foreground text-sm">{{ $t('app.projects') }}</div>
                        <Button variant="outline" size="sm" class="px-2!" @click="openProjectList = false">
                            <X />
                        </Button>
                    </div>
                    <div class="scroll-shadow-y flex grow flex-col gap-1 overflow-y-auto">
                        <template :key="project.id" v-for="project in props.projects">
                            <div
                                :style="'--project-color: ' + (project.color ?? '#000000')"
                                class="mx-2 flex h-9 items-center gap-2 rounded-md border-l-6 border-l-(--project-color) bg-(--project-color)/10 py-1 pl-2 text-sm font-medium dark:bg-(--project-color)/20"
                                v-if="project.id !== props.currentProject?.id"
                            >
                                <div class="flex h-9 shrink-0 items-center text-xl" v-if="project.icon">
                                    {{ project.icon }}
                                </div>
                                <div class="line-clamp-1">
                                    {{ project.name }}
                                </div>
                                <Button
                                    @click="setProject(project)"
                                    class="mr-0.5 ml-auto px-2! shadow-none"
                                    size="sm"
                                    variant="outline"
                                >
                                    <Play class="size-4" />
                                </Button>
                            </div>
                        </template>

                        <Button
                            :as="Link"
                            :href="
                                route('window.new-project.open', {
                                    darkMode: state === 'dark' ? 1 : 0
                                })
                            "
                            class="mx-2 mb-2"
                            preserve-scroll
                            preserve-state
                            size="sm"
                            v-if="props.projects.length"
                            variant="secondary"
                        >
                            <Plus class="size-4" />
                            {{ $t('app.create new project') }}
                        </Button>

                        <div class="px-2 text-sm" v-if="props.projects.length === 0">
                            <EmptyState
                                :action-href="
                                    route('window.new-project.open', {
                                        darkMode: state === 'dark' ? 1 : 0
                                    })
                                "
                                :action-label="$t('app.create new project')"
                                :icon="Tag"
                                class="h-auto grow p-4 text-sm!"
                                :title="$t('app.no projects created')"
                                :description="$t('app.create a new project to track project times.')"
                            />
                        </div>
                    </div>
                </div>
            </Transition>
        </div>
        <div>
            <div class="flex gap-2 p-2">
                <Button
                    :as="Link"
                    :href="
                        route('window.overview.open', {
                            darkMode: state === 'dark' ? 1 : 0
                        })
                    "
                    class="flex-1 shrink-0"
                    preserve-scroll
                    preserve-state
                    size="sm"
                    variant="outline"
                >
                    <ChartColumnBig />
                    {{ $t('app.overview') }}
                </Button>
                <Button
                    @click="showProjectList"
                    class="border-2 border-dashed !px-2 shadow-none"
                    size="sm"
                    variant="outline"
                >
                    <Tag class="size-4" />
                </Button>
            </div>
            <div class="bg-muted dark:bg-muted/60 flex gap-2 p-2">
                <Button
                    :as="Link"
                    :disabled="loading"
                    :href="route('menubar.storeWork')"
                    @click="router.flushAll()"
                    class="flex-1 shrink-0 px-0 disabled:opacity-100"
                    method="POST"
                    preserve-scroll
                    preserve-state
                    size="lg"
                    v-if="props.currentType === null"
                >
                    <Play />
                    {{ $t('app.start') }}
                </Button>
                <Button
                    :as="Link"
                    :disabled="loading"
                    :href="route('menubar.storeStop')"
                    @click="router.flushAll()"
                    class="flex-1 shrink-0 px-0 disabled:opacity-100"
                    method="POST"
                    preserve-scroll
                    preserve-state
                    size="lg"
                    v-if="props.currentType !== null"
                    variant="destructive"
                >
                    <Square />
                    {{ $t('app.stop') }}
                </Button>
                <Button
                    :as="Link"
                    :disabled="loading"
                    :href="route('menubar.storeBreak')"
                    @click="router.flushAll()"
                    class="flex-1 shrink-0 px-0 disabled:opacity-100"
                    method="POST"
                    preserve-scroll
                    preserve-state
                    size="lg"
                    v-if="props.currentType === 'work'"
                    variant="outline"
                >
                    <Coffee />
                    {{ $t('app.break') }}
                </Button>
                <Button
                    :as="Link"
                    :disabled="loading"
                    :href="route('menubar.storeWork')"
                    @click="router.flushAll()"
                    class="flex-1 shrink-0 px-0 disabled:opacity-100"
                    method="POST"
                    preserve-scroll
                    preserve-state
                    size="lg"
                    v-if="props.currentType === 'break'"
                >
                    <Play />
                    {{ $t('app.continue') }}
                </Button>
            </div>
        </div>
    </div>
</template>
