<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\TimestampTypeEnum;
use App\Services\LocaleService;
use App\Services\TimestampService;
use App\Services\TrayIconService;
use Illuminate\Console\Command;
use Native\Laravel\Facades\MenuBar;

class MenubarRefresh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'menubar:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        new LocaleService;
        $currentType = TimestampService::getCurrentType();

        if ($currentType === TimestampTypeEnum::WORK) {
            $time = TimestampService::getWorkTime();
            MenuBar::icon(TrayIconService::getIcon('work'));
        } elseif ($currentType === TimestampTypeEnum::BREAK) {
            $time = TimestampService::getBreakTime();
            MenuBar::icon(TrayIconService::getIcon('break'));
        } else {
            MenuBar::tooltip('');
            MenuBar::label('');
            MenuBar::icon(TrayIconService::getIcon());

            return;
        }

        MenuBar::tooltip(gmdate('G:i', (int) $time));
        MenuBar::label(gmdate('G:i', (int) $time));
    }
}
