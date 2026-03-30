<?php

declare(strict_types=1);

use App\Models\Project;
use App\Models\Timestamp;
use App\Services\Import\ClockifyImportService;
use App\Settings\ProjectSettings;
use Illuminate\Support\Facades\Date;

afterEach(function (): void {
    Date::setTestNow();
});

dataset('clockify exports', [
    'de' => 'de.csv',
    'en' => 'en.csv',
    'es' => 'es.csv',
    'fr' => 'fr.csv',
    'pt' => 'pt.csv',
]);

it('imports the current clockify export format across supported languages', function (string $fileName): void {
    Date::setTestNow(Date::parse('2026-03-26 12:00:00'));

    $settings = resolve(ProjectSettings::class);
    $settings->defaultCurrency = 'EUR';
    $settings->save();

    $csvPath = base_path('tests/Feature/ClockifyExports/'.$fileName);

    (new ClockifyImportService($csvPath))->import();

    $timestamp = Timestamp::with('project')->sole();
    $project = Project::sole();

    expect($timestamp->source)->toBe('Clockify');
    expect($timestamp->started_at->format('Y-m-d H:i:s'))->toBe('2026-03-26 09:30:00');
    expect($timestamp->ended_at->format('Y-m-d H:i:s'))->toBe('2026-03-26 11:00:00');
    expect($timestamp->project?->is($project))->toBeTrue();
    expect($project->name)->toBe('Website Relaunch');
    expect((float) $project->hourly_rate)->toBe(85.0);
    expect($project->currency)->toBe('USD');
})->with('clockify exports');
