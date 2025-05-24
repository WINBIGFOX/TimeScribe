<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('auto_updater.autoUpdate', true);
        $this->migrator->add('auto_updater.lastCheck', null);
        $this->migrator->add('auto_updater.lastVersion', null);
        $this->migrator->add('auto_updater.isDownloaded', false);
    }
};
