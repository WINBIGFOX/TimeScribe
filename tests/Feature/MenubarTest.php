<?php

declare(strict_types=1);

use App\Enums\TimestampTypeEnum;
use App\Models\Project;
use App\Models\Timestamp;
use App\Settings\ProjectSettings;
use Illuminate\Support\Facades\Date;
use Inertia\Testing\AssertableInertia as Assert;

it('shows the accumulated work time of the current project in the menubar', function (): void {
    $this->travelTo(Date::parse('2026-09-15 12:00:00'));

    $project = Project::create([
        'name' => 'Website redesign',
        'color' => '#123456',
    ]);
    $otherProject = Project::create([
        'name' => 'Other project',
        'color' => '#654321',
    ]);

    Timestamp::create([
        'type' => TimestampTypeEnum::WORK,
        'started_at' => Date::parse('2026-09-15 09:00:00'),
        'ended_at' => Date::parse('2026-09-15 10:00:00'),
        'last_ping_at' => Date::parse('2026-09-15 10:00:00'),
        'project_id' => $project->id,
        'paid' => false,
    ]);
    Timestamp::create([
        'type' => TimestampTypeEnum::WORK,
        'started_at' => Date::parse('2026-09-15 10:00:00'),
        'ended_at' => Date::parse('2026-09-15 10:30:00'),
        'last_ping_at' => Date::parse('2026-09-15 10:30:00'),
        'project_id' => $otherProject->id,
        'paid' => false,
    ]);
    Timestamp::create([
        'type' => TimestampTypeEnum::BREAK,
        'started_at' => Date::parse('2026-09-15 10:30:00'),
        'ended_at' => Date::parse('2026-09-15 11:00:00'),
        'last_ping_at' => Date::parse('2026-09-15 11:00:00'),
        'paid' => false,
    ]);
    Timestamp::create([
        'type' => TimestampTypeEnum::WORK,
        'started_at' => Date::parse('2026-09-15 11:00:00'),
        'last_ping_at' => Date::parse('2026-09-15 12:00:00'),
        'project_id' => $project->id,
        'paid' => false,
    ]);

    $projectSettings = resolve(ProjectSettings::class);
    $projectSettings->currentProject = $project->id;
    $projectSettings->save();

    $this->get(route('menubar.index'))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('MenuBar')
            ->where('currentProjectTime', 7200)
        );
});

it('shows zero current project time when no project is selected', function (): void {
    $this->travelTo(Date::parse('2026-09-15 12:00:00'));

    Timestamp::create([
        'type' => TimestampTypeEnum::WORK,
        'started_at' => Date::parse('2026-09-15 11:00:00'),
        'last_ping_at' => Date::parse('2026-09-15 12:00:00'),
        'paid' => false,
    ]);

    $this->get(route('menubar.index'))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('MenuBar')
            ->where('currentProjectTime', 0)
        );
});
