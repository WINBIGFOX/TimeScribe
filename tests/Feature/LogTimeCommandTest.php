<?php

declare(strict_types=1);

use App\Models\Project;
use App\Models\Timestamp;
use Illuminate\Support\Facades\Date;

it('logs a timestamp to an existing project from natural language', function (): void {
    Date::setTestNow(Date::parse('2026-02-22 12:00:00'));

    $project = Project::create(['name' => 'Acme']);

    $this->artisan('timescribe:log', [
        'text' => 'yesterday worked on Acme from 09:00 to 11:00',
    ])->assertExitCode(0);

    $timestamp = Timestamp::first();

    expect($timestamp)->not->toBeNull();
    expect($timestamp->project_id)->toBe($project->id);
    expect($timestamp->started_at->format('Y-m-d H:i'))->toBe('2026-02-21 09:00');
    expect($timestamp->ended_at?->format('Y-m-d H:i'))->toBe('2026-02-21 11:00');
    expect($timestamp->source)->toBe('Codex');
});

it('can create a project via options', function (): void {
    Date::setTestNow(Date::parse('2026-02-22 12:00:00'));

    $this->artisan('timescribe:log', [
        'text' => 'yesterday for 1h',
        '--project' => 'New Project',
        '--create-project' => true,
    ])->assertExitCode(0);

    expect(Project::where('name', 'New Project')->exists())->toBeTrue();
    expect(Timestamp::count())->toBe(1);
});

it('shows overlap and overwrites after confirmation', function (): void {
    Date::setTestNow(Date::parse('2026-02-22 12:00:00'));

    Project::create(['name' => 'Acme']);

    Timestamp::create([
        'type' => 'work',
        'started_at' => Date::parse('2026-02-21 10:00:00'),
        'ended_at' => Date::parse('2026-02-21 12:00:00'),
        'last_ping_at' => Date::parse('2026-02-21 12:00:00'),
        'source' => 'Seed',
    ]);

    $this->artisan('timescribe:log', [
        'text' => 'yesterday worked on Acme from 11:00 to 13:00',
    ])
        ->expectsConfirmation('Overwrite by trimming/splitting these existing timestamps?', 'yes')
        ->assertExitCode(0);

    $timestamps = Timestamp::orderBy('started_at')->get();

    expect($timestamps)->toHaveCount(2);
    expect($timestamps->get(0)->started_at->format('Y-m-d H:i'))->toBe('2026-02-21 10:00');
    expect($timestamps->get(0)->ended_at?->format('Y-m-d H:i'))->toBe('2026-02-21 11:00');
    expect($timestamps->get(1)->started_at->format('Y-m-d H:i'))->toBe('2026-02-21 11:00');
    expect($timestamps->get(1)->ended_at?->format('Y-m-d H:i'))->toBe('2026-02-21 13:00');
});

it('cancels overlap overwrite when confirmation is denied', function (): void {
    Date::setTestNow(Date::parse('2026-02-22 12:00:00'));

    Project::create(['name' => 'Acme']);

    Timestamp::create([
        'type' => 'work',
        'started_at' => Date::parse('2026-02-21 10:00:00'),
        'ended_at' => Date::parse('2026-02-21 12:00:00'),
        'last_ping_at' => Date::parse('2026-02-21 12:00:00'),
        'source' => 'Seed',
    ]);

    $this->artisan('timescribe:log', [
        'text' => 'yesterday worked on Acme from 11:00 to 13:00',
    ])
        ->expectsConfirmation('Overwrite by trimming/splitting these existing timestamps?', 'no')
        ->assertExitCode(1);

    expect(Timestamp::count())->toBe(1);
    expect(Timestamp::first()?->ended_at?->format('Y-m-d H:i'))->toBe('2026-02-21 12:00');
});
