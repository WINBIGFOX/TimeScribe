<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Native\Laravel\Facades\AutoUpdater;

class CheckUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for updates';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        AutoUpdater::checkForUpdates();
    }
}
