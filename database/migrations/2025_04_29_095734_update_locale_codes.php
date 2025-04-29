<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->migrator->update(
            'general.locale',
            fn (string $locale) => str_replace('-', '_', $locale)
        );
    }
};
