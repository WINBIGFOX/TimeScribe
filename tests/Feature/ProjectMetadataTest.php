<?php

declare(strict_types=1);

use App\Enums\ExportColumnEnum;
use App\Enums\TimestampTypeEnum;
use App\Models\Project;
use App\Models\Timestamp;
use App\Services\Export\ExportService;
use App\Settings\ExportSettings;
use Illuminate\Support\Facades\Date;
use Inertia\Testing\AssertableInertia as Assert;

it('stores metadata when creating a project', function (): void {
    $response = $this->post(route('project.store'), [
        'name' => 'API Sync',
        'description' => 'Export integration',
        'metadata' => '{"external_id":"proj_123"}',
        'color' => '#123456',
        'icon' => '🚀',
        'hourly_rate' => 120,
        'currency' => 'EUR',
    ]);

    $response->assertRedirect(route('project.index'));

    expect(Project::query()->sole())
        ->metadata->toBe('{"external_id":"proj_123"}')
        ->currency->toBe('EUR');
});

it('updates metadata on an existing project', function (): void {
    $project = Project::create([
        'name' => 'Client A',
        'description' => 'Before',
        'metadata' => 'old-value',
        'color' => '#123456',
    ]);

    $response = $this->patch(route('project.update', $project), [
        'name' => 'Client A',
        'description' => 'After',
        'metadata' => 'new-value',
        'color' => '#654321',
    ]);

    $response->assertRedirect(route('project.index'));

    expect($project->fresh())
        ->metadata->toBe('new-value')
        ->description->toBe('After');
});

it('includes metadata in the project edit modal props', function (): void {
    $project = Project::create([
        'name' => 'Client B',
        'metadata' => 'client-b',
        'color' => '#334455',
    ]);

    $this->get(route('project.edit', $project))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Project/Index')
            ->where('modal.component', 'Project/Edit')
            ->where('modal.props.project.metadata', 'client-b')
        );
});

it('exports project metadata as a dedicated column', function (): void {
    $settings = resolve(ExportSettings::class);
    $settings->column_order = [
        ExportColumnEnum::PROJECT->value,
        ExportColumnEnum::METADATA->value,
        ExportColumnEnum::DESCRIPTION->value,
    ];
    $settings->column_project = true;
    $settings->column_metadata = true;
    $settings->column_description = true;
    $settings->column_type = false;
    $settings->column_import_source = false;
    $settings->column_start_date = false;
    $settings->column_start_time = false;
    $settings->column_end_date = false;
    $settings->column_end_time = false;
    $settings->column_duration = false;
    $settings->column_hourly_rate = false;
    $settings->column_billable_amount = false;
    $settings->column_currency = false;
    $settings->column_paid = false;
    $settings->save();

    $project = Project::create([
        'name' => 'Client C',
        'metadata' => 'erp-42',
        'color' => '#abcdef',
        'icon' => '🧾',
    ]);

    Timestamp::create([
        'type' => TimestampTypeEnum::WORK,
        'started_at' => Date::parse('2026-03-01 09:00:00'),
        'ended_at' => Date::parse('2026-03-01 10:30:00'),
        'description' => 'Invoice sync',
        'project_id' => $project->id,
        'paid' => false,
    ]);

    Timestamp::create([
        'type' => TimestampTypeEnum::WORK,
        'started_at' => Date::parse('2026-03-02 09:00:00'),
        'ended_at' => Date::parse('2026-03-02 09:15:00'),
        'description' => 'Unassigned',
        'project_id' => null,
        'paid' => false,
    ]);

    $path = tempnam(sys_get_temp_dir(), 'timescribe-export-');

    (new ExportService(
        timestampTypes: [TimestampTypeEnum::WORK->value],
        startDate: '2026-03-01',
        endDate: '2026-03-03',
    ))->exportAsCsv($path);

    $rows = array_map(
        static fn (string $line): array => str_getcsv($line, escape: '\\'),
        file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
    );

    unlink($path);

    expect($rows[0])->toBe(['Project', 'Metadata', 'Description'])
        ->and($rows[1])->toBe(['', '', 'Unassigned'])
        ->and($rows[2])->toBe(['🧾 Client C', 'erp-42', 'Invoice sync']);
});
