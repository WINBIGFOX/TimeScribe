<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('export.column_type', true);
        $this->migrator->add('export.column_description', true);
        $this->migrator->add('export.column_project', true);
        $this->migrator->add('export.column_import_source', true);
        $this->migrator->add('export.column_start_date', true);
        $this->migrator->add('export.column_start_time', true);
        $this->migrator->add('export.column_end_date', true);
        $this->migrator->add('export.column_end_time', true);
        $this->migrator->add('export.column_duration', true);
        $this->migrator->add('export.column_hourly_rate', true);
        $this->migrator->add('export.column_billable_amount', true);
        $this->migrator->add('export.column_currency', true);
        $this->migrator->add('export.column_paid', true);
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('export.column_type');
        $this->migrator->deleteIfExists('export.column_description');
        $this->migrator->deleteIfExists('export.column_project');
        $this->migrator->deleteIfExists('export.column_import_source');
        $this->migrator->deleteIfExists('export.column_start_date');
        $this->migrator->deleteIfExists('export.column_start_time');
        $this->migrator->deleteIfExists('export.column_end_date');
        $this->migrator->deleteIfExists('export.column_end_time');
        $this->migrator->deleteIfExists('export.column_duration');
        $this->migrator->deleteIfExists('export.column_hourly_rate');
        $this->migrator->deleteIfExists('export.column_billable_amount');
        $this->migrator->deleteIfExists('export.column_currency');
        $this->migrator->deleteIfExists('export.column_paid');
    }
};
