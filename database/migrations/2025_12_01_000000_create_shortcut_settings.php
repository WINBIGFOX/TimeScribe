<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('shortcuts.startShortcut');
        $this->migrator->add('shortcuts.stopShortcut');
        $this->migrator->add('shortcuts.pauseShortcut');
        $this->migrator->add('shortcuts.overviewShortcut');
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('shortcuts.startShortcut');
        $this->migrator->deleteIfExists('shortcuts.stopShortcut');
        $this->migrator->deleteIfExists('shortcuts.pauseShortcut');
        $this->migrator->deleteIfExists('shortcuts.overviewShortcut');
    }
};
