<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\LocaleService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    #[\Override]
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict();
        DB::prohibitDestructiveCommands(app()->isProduction());
        Vite::prefetch(concurrency: 3);
        JsonResource::withoutWrapping();

        Route::pattern('date', '\d{4}-\d{2}-\d{2}');
        Route::pattern('datetime', '\d{4}-\d{2}-\d{2}\s\d{2}\:\d{2}\:\d{2}');
        Route::bind('date', fn (string $value): Carbon => $this->parseCarbon($value));
        Route::bind('datetime', fn (string $value): Carbon => $this->parseCarbon($value));
    }

    public function parseCarbon(string $value): Carbon
    {
        new LocaleService;

        return Date::parse($value);
    }
}
