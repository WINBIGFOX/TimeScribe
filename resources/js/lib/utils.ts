import { type ClassValue, clsx } from 'clsx';
import moment from 'moment/min/moment-with-locales';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function secToFormat(
    seconds: number,
    withoutHours?: boolean,
    withoutSeconds?: boolean,
    noLeadingZero?: boolean,
    withAbs?: boolean,
) {
    const positive = seconds >= 0;

    if (withAbs) {
        seconds = Math.abs(seconds);
    }

    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = Math.floor(seconds % 60);

    let output = '';

    if (!withoutHours || hours > 0) {
        output = `${String(hours).padStart(2, '0')}:`;
    }
    output += `${String(minutes).padStart(2, '0')}`;
    if (!withoutSeconds) {
        output += `:${String(secs).padStart(2, '0')}`;
    }

    if (noLeadingZero && output.startsWith('0')) {
        output = output.slice(1, output.length);
    }

    if (withAbs) {
        output = `${positive ? '+' : '-'}${output}`;
    }

    return output;
}

export function localeWeekdays() {
    const weekdaysKeys = [
        'sunday',
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
    ];
    const weekdaysName = moment.weekdays();
    const weekdaysMin = moment.weekdaysMin();

    const weekdays = weekdaysName.map((day, index) => ({
        name: day,
        short: weekdaysMin[index],
        key: weekdaysKeys[index],
    }));

    weekdays.push(
        weekdays.shift() as { name: string; short: string; key: string },
    );

    return weekdays;
}
