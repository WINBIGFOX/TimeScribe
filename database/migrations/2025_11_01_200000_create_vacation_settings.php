<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('vacation.default_entitlement_days', 30.0);
        $this->migrator->add('vacation.prorate_consumption', true);
        $this->migrator->add('vacation.proration_step', 0.5);
        $this->migrator->add('vacation.auto_carryover', false);
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('vacation.default_entitlement_days');
        $this->migrator->deleteIfExists('vacation.prorate_consumption');
        $this->migrator->deleteIfExists('vacation.proration_step');
        $this->migrator->deleteIfExists('vacation.auto_carryover');
    }
};
