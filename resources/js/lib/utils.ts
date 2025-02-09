import { type ClassValue, clsx } from 'clsx';
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

export function insertColon(str: string, index: number) {
    return str.substring(0, index) + ':' + str.substring(index);
}
