<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.time_display_format', 'clock');
    }

    public function down(): void
    {
        $this->migrator->delete('general.time_display_format');
    }
};
