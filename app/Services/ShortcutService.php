<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\OpenOverviewShortcutTriggered;
use App\Events\PauseTimerShortcutTriggered;
use App\Events\StartTimerShortcutTriggered;
use App\Events\StopTimerShortcutTriggered;
use App\Settings\ShortcutSettings;
use Native\Desktop\Facades\GlobalShortcut;

class ShortcutService
{
    public function refresh(ShortcutSettings $settings, array $previousShortcuts = []): void
    {
        $this->unregisterAll(array_merge($previousShortcuts, $this->shortcutsFromSettings($settings)));
        $this->registerAll($settings);
    }

    public function registerAll(ShortcutSettings $settings): void
    {
        $this->registerShortcut($settings->startShortcut, StartTimerShortcutTriggered::class);
        $this->registerShortcut($settings->stopShortcut, StopTimerShortcutTriggered::class);
        $this->registerShortcut($settings->pauseShortcut, PauseTimerShortcutTriggered::class);
        $this->registerShortcut($settings->overviewShortcut, OpenOverviewShortcutTriggered::class);
    }

    public function unregisterAll(array $shortcuts): void
    {
        collect($shortcuts)
            ->filter()
            ->each(fn (string $shortcut) => $this->unregisterShortcut($shortcut));
    }

    private function registerShortcut(?string $shortcut, string $eventClass): void
    {
        if ($shortcut === null) {
            return;
        }

        GlobalShortcut::key($shortcut)
            ->event($eventClass)
            ->register();
    }

    private function unregisterShortcut(?string $shortcut): void
    {
        if ($shortcut === null) {
            return;
        }

        GlobalShortcut::key($shortcut)
            ->unregister();
    }

    /**
     * @return array<int, string|null>
     */
    private function shortcutsFromSettings(ShortcutSettings $settings): array
    {
        return [
            $settings->startShortcut,
            $settings->stopShortcut,
            $settings->pauseShortcut,
            $settings->overviewShortcut,
        ];
    }
}
