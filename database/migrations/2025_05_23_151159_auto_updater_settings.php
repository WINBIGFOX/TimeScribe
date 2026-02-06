<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('auto_updater.autoUpdate', true);
        $this->migrator->add('auto_updater.lastCheck');
        $this->migrator->add('auto_updater.lastVersion');
        $this->migrator->add('auto_updater.isDownloaded', false);
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('auto_updater.autoUpdate');
        $this->migrator->deleteIfExists('auto_updater.lastCheck');
        $this->migrator->deleteIfExists('auto_updater.lastVersion');
        $this->migrator->deleteIfExists('auto_updater.isDownloaded');
    }
};
