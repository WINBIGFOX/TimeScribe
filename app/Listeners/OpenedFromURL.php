<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Services\LocaleService;
use App\Services\TimestampService;
use Illuminate\Support\Uri;
use Native\Desktop\Events\App\OpenedFromURL as OpenedFromURLEvent;

class OpenedFromURL
{
    /**
     * Handle the event.
     */
    public function handle(OpenedFromURLEvent $event): void
    {
        new LocaleService;
        $url = Uri::of($event->url);
        if ($url->host() === 'start') {
            switch ($url->path()) {
                case 'work': TimestampService::startWork();
                    break;
                case 'break': TimestampService::startBreak();
                    break;
            }
        }
        if ($url->host() === 'stop') {
            TimestampService::stop();
        }
    }
}
