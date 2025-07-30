<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('fly_timer.showWithStart', false);
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('fly_timer.showWithStart');
    }
};
