<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.id', uuid_create());
        $this->migrator->add('general.locale', 'en-GB');
        $this->migrator->add('general.timezone', null);
        $this->migrator->add('general.showTimerOnUnlock', true);
        $this->migrator->add('general.holidayRegion', null);
        $this->migrator->add('general.stopBreakAutomatic', null);
        $this->migrator->add('general.stopBreakAutomaticActivationTime', null);
        $this->migrator->add('general.stopWorkTimeReset', null);
        $this->migrator->add('general.stopBreakTimeReset', null);
        $this->migrator->add('general.appActivityTracking', false);
        $this->migrator->add('general.wizard_completed', false);
        $this->migrator->add('general.theme', 'system');
    }
};
