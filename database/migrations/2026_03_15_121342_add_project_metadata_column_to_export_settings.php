<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('export.column_metadata', true);
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('export.column_metadata');
    }
};
