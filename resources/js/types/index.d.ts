declare module 'vue3-emoji-picker'

export interface Date {
    diff: string
    formatted: string
    date: string
    day: string
}

export type Enum = Record<string, string>

export interface WeekdayObject {
    plan?: number
    fallbackPlan?: number
    date: Date
    workTime: number
    breakTime: number
    noWorkTime: number
    timestamps: unknown[]
    activeWork: boolean
    absences: Absence[]
}

export interface WorkSchedule {
    id: number
    valid_from: Date
    is_current?: boolean
    sunday: number
    monday: number
    tuesday: number
    wednesday: number
    thursday: number
    friday: number
    saturday: number
}

export interface Timestamp {
    id: number
    type: string
    started_at: Date
    ended_at?: Date
    description?: string
    last_ping_at?: Date
    source?: string
    project?: Project
}

export interface ActivityHistory {
    id: number
    app_name: string
    app_identifier: string
    app_icon: string
    app_category?: string
    started_at: Date
    ended_at?: Date
}

export interface Project {
    id: number
    name: string
    description?: string
    color: string
    icon?: string
    hourly_rate?: number
    timestamps?: Timestamp[]
    parent?: Project
    children?: Project[]
}

export interface AppActivityHistory {
    id: number
    app_name: string
    app_identifier: string
    app_icon: string
    app_category?: string
    started_at: Date
    ended_at?: Date
    duration: number
}

export interface Absence {
    id: number
    type: 'vacation' | 'sick'
    date: Date
    duration?: number
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    js_locale: string
    locale: string
    timezone: string
    app_version: string
    recording: boolean
    environment: 'Windows' | 'Darwin' | 'Linux' | 'Unknown'
}
