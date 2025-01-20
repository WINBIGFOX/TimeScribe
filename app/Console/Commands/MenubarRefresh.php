<?php

namespace App\Console\Commands;

use App\Enums\TimestampTypeEnum;
use App\Services\TimestampService;
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
        $currentType = TimestampService::getCurrentType();

        if ($currentType === TimestampTypeEnum::WORK) {
            $time = TimestampService::getWorkTime();
            MenuBar::icon(public_path('WorkIconTemplate@2x.png'));
        } elseif ($currentType === TimestampTypeEnum::BREAK) {
            $time = TimestampService::getBreakTime();
            MenuBar::icon(public_path('BreakIconTemplate@2x.png'));
        } else {
            MenuBar::label('');
            MenuBar::icon(public_path('IconTemplate@2x.png'));

            return;
        }

        MenuBar::label(gmdate('G:i', $time));
    }
}
