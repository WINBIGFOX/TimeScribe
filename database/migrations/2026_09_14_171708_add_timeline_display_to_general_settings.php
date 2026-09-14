<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        if (! $this->migrator->exists('general.timeline_display')) {
            $this->migrator->add('general.timeline_display', 'detailed');
        }
    }

    public function down(): void
    {
        $this->migrator->delete('general.timeline_display');
    }
};
