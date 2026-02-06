<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.id', uuid_create());
        $this->migrator->add('general.locale', 'en_GB');
        $this->migrator->add('general.timezone');
        $this->migrator->add('general.showTimerOnUnlock', true);
        $this->migrator->add('general.holidayRegion');
        $this->migrator->add('general.stopBreakAutomatic');
        $this->migrator->add('general.stopBreakAutomaticActivationTime');
        $this->migrator->add('general.stopWorkTimeReset');
        $this->migrator->add('general.stopBreakTimeReset');
        $this->migrator->add('general.appActivityTracking', false);
        $this->migrator->add('general.wizard_completed', false);
        $this->migrator->add('general.theme', 'system');
    }
};
