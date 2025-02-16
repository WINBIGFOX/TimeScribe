export interface Date {
    diff: string;
    formatted: string;
    date: string;
    day: string;
}

export interface WeekdayObject {
    plan?: number;
    fallbackPlan?: number;
    date: Date;
    workTime: number;
    breakTime: number;
    noWorkTime: number;
    timestamps: unknown[];
    activeWork: boolean;
}

export interface Timestamp {
    id: number;
    type: string;
    started_at: Date;
    ended_at?: Date;
    last_ping_at?: Date;
    can_start_edit?: boolean;
    can_end_edit?: boolean;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {};
