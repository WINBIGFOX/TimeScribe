<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\TimestampTypeEnum;
use App\Services\LocaleService;
use App\Services\TimestampService;
use Illuminate\Console\Command;
use Native\Laravel\Enums\SystemThemesEnum;
use Native\Laravel\Facades\MenuBar;
use Native\Laravel\Facades\System;
use Native\Laravel\Support\Environment;

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
            MenuBar::icon($this->getIcon('work'));
        } elseif ($currentType === TimestampTypeEnum::BREAK) {
            $time = TimestampService::getBreakTime();
            MenuBar::icon($this->getIcon('break'));
        } else {
            MenuBar::tooltip('');
            MenuBar::label('');
            MenuBar::icon($this->getIcon('default'));

            return;
        }

        MenuBar::tooltip(gmdate('G:i', (int) $time));
        MenuBar::label(gmdate('G:i', (int) $time));
    }

    private function getIcon(string $iconName): string
    {
        $isMacOs = Environment::isMac();
        $appIconPrefix = $isMacOs ? '' : 'Windows';
        $prefix = '';

        if (! $isMacOs && System::theme() === SystemThemesEnum::DARK) {
            $prefix = 'white/';
        }

        return match ($iconName) {
            'work' => public_path($prefix.'WorkIconTemplate@2x.png'),
            'break' => public_path($prefix.'BreakIconTemplate@2x.png'),
            default => public_path($prefix.$appIconPrefix.'IconTemplate@2x.png')
        };
    }
}
