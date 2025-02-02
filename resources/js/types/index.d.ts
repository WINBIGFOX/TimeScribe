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

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {};
