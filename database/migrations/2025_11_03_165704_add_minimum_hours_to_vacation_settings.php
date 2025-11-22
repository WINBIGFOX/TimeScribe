<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('vacation.minimum_day_hours', 8.0);
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('vacation.minimum_day_hours');
    }
};
