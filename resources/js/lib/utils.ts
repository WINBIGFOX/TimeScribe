import { type ClassValue, clsx } from 'clsx'
import moment from 'moment/min/moment-with-locales'
import { twMerge } from 'tailwind-merge'

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs))
}

export function getCurrencySymbol(locale, currency) {
    return (0)
        .toLocaleString(locale, {
            style: 'currency',
            currency,
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        })
        .replace(/\d/g, '')
        .trim()
}

export function secToFormat(
    seconds: number,
    withoutHours?: boolean,
    withoutSeconds?: boolean,
    noLeadingZero?: boolean,
    withAbs?: boolean
) {
    const positive = seconds >= 0

    seconds = Math.abs(seconds)

    const hours = Math.floor(seconds / 3600)
    const minutes = Math.floor((seconds % 3600) / 60)
    const secs = Math.floor(seconds % 60)

    let output = ''

    if (!withoutHours || hours > 0) {
        output = `${String(hours).padStart(2, '0')}:`
    }
    output += `${String(minutes).padStart(2, '0')}`
    if (!withoutSeconds) {
        output += `:${String(secs).padStart(2, '0')}`
    }

    if (noLeadingZero && output.startsWith('0')) {
        output = output.slice(1, output.length)
    }

    if (withAbs || !positive) {
        output = `${positive ? '+' : '-'}${output}`
    }

    return output
}

export function weekdayTranslate(weekday: string) {
    const englishWeekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']
    if (englishWeekdays.includes(weekday)) {
        return weekday
    }

    const locales = ['da', 'en', 'de', 'fr', 'it', 'pt-br', 'zh-cn']

    const currentLocale = moment.locale()

    for (const locale of locales) {
        moment.locale(locale)
        const localizedWeekdays = moment.weekdays()

        const index = localizedWeekdays.findIndex((day) => day.toLowerCase() === weekday.toLowerCase())

        if (index !== -1) {
            moment.locale('en')
            const englishWeekday = moment.weekdays()[index]
            moment.locale(currentLocale)
            return englishWeekday
        }
    }

    moment.locale(currentLocale)

    return weekday
}

export function categoryIcon(category: string) {
    switch (category) {
        case 'public.app-category.business':
            return '💼'
        case 'public.app-category.developer-tools':
            return '🛠️'
        case 'public.app-category.education':
            return '🎓'
        case 'public.app-category.entertainment':
            return '🎭'
        case 'public.app-category.finance':
            return '💰'
        case 'public.app-category.games':
            return '🎮'
        case 'public.app-category.graphics-design':
            return '🎨'
        case 'public.app-category.healthcare-fitness':
            return '💪'
        case 'public.app-category.lifestyle':
            return '🌟'
        case 'public.app-category.medical':
            return '🩺'
        case 'public.app-category.music':
            return '🎵'
        case 'public.app-category.news':
            return '📰'
        case 'public.app-category.photography':
            return '📷'
        case 'public.app-category.productivity':
            return '✅'
        case 'public.app-category.reference':
            return '📚'
        case 'public.app-category.social-networking':
            return '💬'
        case 'public.app-category.sports':
            return '🏅'
        case 'public.app-category.travel':
            return '✈️'
        case 'public.app-category.utilities':
            return '⚙️'
        case 'public.app-category.video':
            return '🎬'
        case 'public.app-category.weather':
            return '☀️'
        case 'public.app-category.action-games':
            return '🔫'
        case 'public.app-category.adventure-games':
            return '🗺️'
        case 'public.app-category.arcade-games':
            return '🕹️'
        case 'public.app-category.board-games':
            return '♟️'
        case 'public.app-category.card-games':
            return '🃏'
        case 'public.app-category.casino-games':
            return '🎰'
        case 'public.app-category.dice-games':
            return '🎲'
        case 'public.app-category.educational-games':
            return '📘'
        case 'public.app-category.family-games':
            return '👨‍👩‍👧‍👦'
        case 'public.app-category.kids-games':
            return '🧸'
        case 'public.app-category.music-games':
            return '🎶'
        case 'public.app-category.puzzle-games':
            return '🧩'
        case 'public.app-category.racing-games':
            return '🏎️'
        case 'public.app-category.role-playing-games':
            return '🧙'
        case 'public.app-category.simulation-games':
            return '🛸'
        case 'public.app-category.sports-games':
            return '🏈'
        case 'public.app-category.strategy-games':
            return '♟️'
        case 'public.app-category.trivia-games':
            return '❓'
        case 'public.app-category.word-games':
            return '🔤'
    }
    return '❓'
}
