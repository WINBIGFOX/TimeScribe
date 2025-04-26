<?php

declare(strict_types=1);

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;

class OptimizeDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:optimize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize the database by running VACUUM and ANALYZE commands';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        DB::statement('VACUUM;');
        DB::statement('ANALYZE;');
    }
}
