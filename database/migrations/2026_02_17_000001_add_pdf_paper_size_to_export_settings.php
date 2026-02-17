<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('export.pdf_paper_size', 'a4');
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('export.pdf_paper_size');
    }
};
