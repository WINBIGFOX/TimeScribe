<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ExportSettings extends Settings
{
    public ?array $column_order = null;

    public bool $column_type;

    public bool $column_description;

    public bool $column_project;

    public bool $column_import_source;

    public bool $column_start_date;

    public bool $column_start_time;

    public bool $column_end_date;

    public bool $column_end_time;

    public bool $column_duration;

    public bool $column_hourly_rate;

    public bool $column_billable_amount;

    public bool $column_currency;

    public bool $column_paid;

    public string $pdf_paper_size = 'a4';

    public string $pdf_orientation = 'Landscape';

    public static function group(): string
    {
        return 'export';
    }
}
