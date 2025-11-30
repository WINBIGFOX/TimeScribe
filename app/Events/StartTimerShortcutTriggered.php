<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StartTimerShortcutTriggered
{
    use Dispatchable, SerializesModels;

    public function __construct(public string $shortcut) {}
}
