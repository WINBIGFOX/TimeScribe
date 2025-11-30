<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('shortcuts.startShortcut', null);
        $this->migrator->add('shortcuts.stopShortcut', null);
        $this->migrator->add('shortcuts.pauseShortcut', null);
        $this->migrator->add('shortcuts.overviewShortcut', null);
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('shortcuts.startShortcut');
        $this->migrator->deleteIfExists('shortcuts.stopShortcut');
        $this->migrator->deleteIfExists('shortcuts.pauseShortcut');
        $this->migrator->deleteIfExists('shortcuts.overviewShortcut');
    }
};
