<?php

namespace App\Console\Commands;

use App\Services\TimestampService;
use Illuminate\Console\Command;

class TimestampPing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:timestamp-ping';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        TimestampService::ping();
    }
}
