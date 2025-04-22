export interface Date {
    diff: string
    formatted: string
    date: string
    day: string
}

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
    locale: string
    app_version: string
    recording: boolean
}
