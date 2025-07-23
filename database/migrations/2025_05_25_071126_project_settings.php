<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('project.currentProject', null);
        $this->migrator->add('project.showProjectTime', false);
        $this->migrator->add('project.defaultCurrency', null);
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('project.currentProject');
        $this->migrator->deleteIfExists('project.showProjectTime');
    }
};
