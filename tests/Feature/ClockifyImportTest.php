<?php

declare(strict_types=1);

use App\Models\Timestamp;
use App\Services\Import\ClockifyImportService;

function clockifyFixture(string $name): string
{
    return __DIR__.'/../Fixtures/clockify/'.$name;
}

it('imports an export with an extra Date of creation column and 12-hour times', function (): void {
    (new ClockifyImportService(clockifyFixture('clockify-de.csv')))->import();

    expect(Timestamp::count())->toBe(1);
    $timestamp = Timestamp::first();
    expect($timestamp->started_at->format('Y-m-d H:i:s'))->toBe('2026-03-24 18:00:00')
        ->and($timestamp->ended_at->format('Y-m-d H:i:s'))->toBe('2026-03-24 20:00:00')
        ->and($timestamp->source)->toBe('Clockify')
        ->and($timestamp->project->name)->toBe('CogSys');
});

it('imports a German-localized export', function (): void {
    (new ClockifyImportService(clockifyFixture('clockify-en.csv')))->import();

    expect(Timestamp::count())->toBe(1);
    $timestamp = Timestamp::first();
    expect($timestamp->started_at->format('Y-m-d H:i:s'))->toBe('2026-03-24 18:00:00')
        ->and($timestamp->ended_at->format('Y-m-d H:i:s'))->toBe('2026-03-24 20:00:00')
        ->and($timestamp->project->name)->toBe('CogSys');
});

it('imports an export without billable columns and times without seconds', function (): void {
    (new ClockifyImportService(clockifyFixture('clockify-sep.csv')))->import();

    expect(Timestamp::count())->toBe(4);
});

it('reports which columns are missing instead of a generic format error', function (): void {
    $path = tempnam(sys_get_temp_dir(), 'clockify').'.csv';
    file_put_contents($path, "Project,Start Date\nFoo,03/24/2026\n");

    expect(fn (): mixed => new ClockifyImportService($path))->toThrow(Exception::class, 'Start Time');
});
