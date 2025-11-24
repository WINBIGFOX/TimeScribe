<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.default_overview', 'week');
    }

    public function down(): void
    {
        $this->migrator->delete('general.default_overview');
    }
};
