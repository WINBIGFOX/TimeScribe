<?php

declare(strict_types=1);

namespace App\Enums;

use App\Settings\ExportSettings;

enum ExportColumnEnum: string
{
    use BaseEnumTrait;

    case TYPE = 'type';
    case DESCRIPTION = 'description';
    case PROJECT = 'project';
    case IMPORT_SOURCE = 'import_source';
    case START_DATE = 'start_date';
    case START_TIME = 'start_time';
    case END_DATE = 'end_date';
    case END_TIME = 'end_time';
    case DURATION = 'duration';
    case HOURLY_RATE = 'hourly_rate';
    case BILLABLE_AMOUNT = 'billable_amount';
    case CURRENCY = 'currency';
    case PAID = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::TYPE => __('app.type'),
            self::DESCRIPTION => __('app.description'),
            self::PROJECT => __('app.project'),
            self::IMPORT_SOURCE => __('app.import source'),
            self::START_DATE => __('app.start date'),
            self::START_TIME => __('app.start time'),
            self::END_DATE => __('app.end date'),
            self::END_TIME => __('app.end time'),
            self::DURATION => __('app.duration'),
            self::HOURLY_RATE => __('app.hourly rate').' ('.__('app.h').')',
            self::BILLABLE_AMOUNT => __('app.billable amount'),
            self::CURRENCY => __('app.currency'),
            self::PAID => __('app.paid'),
        };
    }

    public function type(): string
    {
        return match ($this) {
            self::START_DATE, self::END_DATE => 'date',
            self::START_TIME, self::END_TIME, self::DURATION => 'time',
            self::HOURLY_RATE, self::BILLABLE_AMOUNT => 'currency',
            self::PAID => 'boolean',
            default => 'string',
        };
    }

    public static function toResource(): array
    {
        $list = collect(self::toArray());
        $exportSettings = resolve(ExportSettings::class);
        $list = $list->map(fn ($item, $key): array => [
            'label' => $item,
            'is_visible' => $exportSettings->{'column_'.$key},
            'type' => self::from($key)->type(),
            'key' => $key,
        ]);

        $sortingBy = array_flip($exportSettings->column_order ?? []);

        return $list->sortKeysUsing(fn (string $a, string $b): int => ($sortingBy[$a] ?? PHP_INT_MAX) <=> ($sortingBy[$b] ?? PHP_INT_MAX))->all();
    }
}
