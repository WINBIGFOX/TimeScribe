<?php

declare(strict_types=1);

use App\Enums\ExportColumnEnum;
use App\Enums\TimestampTypeEnum;
use App\Jobs\CalculateWeekBalance;
use App\Models\Project;
use App\Models\Timestamp;
use App\Services\Export\ExportService;
use App\Services\TimestampService;
use App\Settings\ExportSettings;
use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\SimpleExcel\SimpleExcelReader;

beforeEach(function (): void {
    $settings = resolve(GeneralSettings::class);
    $settings->timezone = 'UTC';
    $settings->locale = 'en_US';
    $settings->save();
    $this->travelTo(Date::parse('2026-03-04 12:00:00', 'UTC'));
});

/** @param array<string, mixed> $attributes */
function createBillingTimestamp(Project $project, array $attributes = []): Timestamp
{
    return Timestamp::create([
        'type' => TimestampTypeEnum::WORK,
        'project_id' => $project->id,
        'started_at' => Date::parse('2026-03-01 09:00:00'),
        'ended_at' => Date::parse('2026-03-01 10:00:00'),
        'last_ping_at' => Date::parse('2026-03-01 10:00:00'),
        'paid' => false,
        ...$attributes,
    ]);
}

function configureBillingExport(): void
{
    $settings = resolve(ExportSettings::class);
    $settings->column_order = ['description', 'duration', 'billable_amount'];
    foreach (ExportColumnEnum::cases() as $column) {
        $settings->{'column_'.$column->value} = in_array($column->value, $settings->column_order, true);
    }
    $settings->save();
}

it('creates manual timestamps with the requested billing status', function (array $billing, bool $expected): void {
    Queue::fake([CalculateWeekBalance::class]);
    $project = Project::create(['name' => 'Client', 'hourly_rate' => 100, 'currency' => 'EUR']);

    $this->post(route('timestamp.store', ['datetime' => '2026-03-01 09:00:00']), [
        'type' => 'work', 'started_at' => '09:00', 'ended_at' => '10:00', 'project_id' => $project->id,
        ...$billing,
    ])->assertRedirect(route('overview.day.show', ['date' => '2026-03-01']));

    $this->assertDatabaseHas('timestamps', ['project_id' => $project->id, 'billable' => $expected]);
    Queue::assertPushed(CalculateWeekBalance::class);
})->with([
    'default' => [[], true],
    'excluded' => [['billable' => false], false],
    'billable' => [['billable' => true], true],
]);

it('preserves or updates billing status when editing and changing projects', function (array $billing, bool $expected): void {
    Queue::fake([CalculateWeekBalance::class]);
    $project = Project::create(['name' => 'Original', 'hourly_rate' => 100, 'currency' => 'EUR']);
    $otherProject = Project::create(['name' => 'Other', 'hourly_rate' => 120, 'currency' => 'EUR']);
    $timestamp = createBillingTimestamp($project, ['billable' => false]);

    $this->patch(route('timestamp.update', $timestamp), [
        'type' => 'work', 'started_at' => '09:00', 'ended_at' => '10:00', 'project_id' => $otherProject->id,
        ...$billing,
    ])->assertRedirect(route('overview.day.show', ['date' => '2026-03-01']));

    $this->assertDatabaseHas('timestamps', ['id' => $timestamp->id, 'project_id' => $otherProject->id, 'billable' => $expected]);
    Queue::assertPushed(CalculateWeekBalance::class);
})->with([
    'omitted' => [[], false],
    'still excluded' => [['billable' => false], false],
    'enabled again' => [['billable' => true], true],
]);

it('toggles billing without changing the recorded times', function (bool $billable, bool $running): void {
    $project = Project::create(['name' => 'Client', 'hourly_rate' => 100, 'currency' => 'EUR']);
    $timestamp = createBillingTimestamp($project, ['billable' => ! $billable, 'ended_at' => $running ? null : '2026-03-01 10:00:00']);

    $this->from(route('project.show', $project))->patch(route('timestamp.update.billable', $timestamp), [
        'billable' => $billable,
    ])->assertRedirect(route('project.show', $project));

    $this->assertDatabaseHas('timestamps', [
        'id' => $timestamp->id, 'billable' => $billable,
        'started_at' => '2026-03-01 09:00:00', 'ended_at' => $running ? null : '2026-03-01 10:00:00',
    ]);
})->with([true, false])->with([true, false]);

it('rejects missing and invalid billing values', function (array $payload): void {
    $project = Project::create(['name' => 'Client']);
    $timestamp = createBillingTimestamp($project);

    $this->patch(route('timestamp.update.billable', $timestamp), $payload)->assertSessionHasErrors('billable');

    $this->assertDatabaseHas('timestamps', ['id' => $timestamp->id, 'billable' => true]);
})->with([
    'missing' => [[]],
    'null' => [['billable' => null]],
    'invalid' => [['billable' => 'maybe']],
]);

it('rejects excluding paid timestamps through either update endpoint', function (string $routeName, array $payload): void {
    $project = Project::create(['name' => 'Client', 'hourly_rate' => 100, 'currency' => 'EUR']);
    $timestamp = createBillingTimestamp($project, ['paid' => true]);

    $this->patch(route($routeName, $timestamp), $payload)->assertSessionHasErrors([
        'billable' => __('app.remove the paid mark first.'),
    ]);

    $this->assertDatabaseHas('timestamps', ['id' => $timestamp->id, 'billable' => true, 'paid' => true]);
})->with([
    'direct switch' => ['timestamp.update.billable', ['billable' => false]],
    'edit dialog' => ['timestamp.update', ['billable' => false, 'type' => 'work', 'started_at' => '09:00', 'ended_at' => '10:00']],
]);

it('rejects marking excluded timestamps as paid', function (): void {
    $project = Project::create(['name' => 'Client', 'hourly_rate' => 100, 'currency' => 'EUR']);
    $timestamp = createBillingTimestamp($project, ['billable' => false]);

    $this->patch(route('timestamp.update.paid', $timestamp), ['paid' => true])->assertSessionHasErrors([
        'paid' => __('app.non-billable timestamps cannot be marked as paid.'),
    ]);

    $this->assertDatabaseHas('timestamps', ['id' => $timestamp->id, 'billable' => false, 'paid' => false]);
});

it('allows excluding a timestamp after removing its paid mark', function (): void {
    $project = Project::create(['name' => 'Client', 'hourly_rate' => 100, 'currency' => 'EUR']);
    $timestamp = createBillingTimestamp($project, ['paid' => true]);

    $this->from(route('project.show', $project))->patch(route('timestamp.update.paid', $timestamp), ['paid' => false])
        ->assertRedirect(route('project.show', $project));
    $this->patch(route('timestamp.update.billable', $timestamp), ['billable' => false])->assertSessionDoesntHaveErrors();

    $this->assertDatabaseHas('timestamps', ['id' => $timestamp->id, 'billable' => false, 'paid' => false]);
});

it('excludes amounts while retaining project work time in overview and detail props', function (string $routeName, string $prefix): void {
    $project = Project::create(['name' => 'Client', 'hourly_rate' => 100, 'currency' => 'EUR']);
    createBillingTimestamp($project, ['paid' => true]);
    createBillingTimestamp($project, ['started_at' => '2026-03-01 10:00:00', 'ended_at' => '2026-03-01 11:00:00']);
    createBillingTimestamp($project, ['billable' => false, 'started_at' => '2026-03-01 11:00:00', 'ended_at' => '2026-03-01 13:00:00']);

    $this->get(route($routeName, $routeName === 'project.show' ? $project : []))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where($prefix.'.billable_amount', 200)
            ->where($prefix.'.timestamps.0.billable', false)
            ->where($prefix.'.timestamps.0.billable_amount', 0)
            ->where($prefix.'.timestamps.0.duration', 7200)
            ->where($prefix.'.timestamps.1.billable_amount', 100)
            ->where($prefix.'.timestamps.2.billable_amount', 100)
        );

    expect($project->work_time)->toBe(14400.0);
})->with([
    'overview' => ['project.index', 'projects.0'],
    'detail' => ['project.show', 'modal.props.project'],
]);

it('calculates zero for unassigned work and breaks even with billing enabled', function (): void {
    $project = Project::create(['name' => 'Client', 'hourly_rate' => 100, 'currency' => 'EUR']);
    $unassigned = createBillingTimestamp($project, ['project_id' => null]);
    $break = createBillingTimestamp($project, ['type' => TimestampTypeEnum::BREAK]);

    expect($unassigned->billable_amount)->toBe(0.0);
    expect($break->billable_amount)->toBe(0.0);
});

it('preserves the billing exclusion when continuing a timer after midnight', function (): void {
    Queue::fake([CalculateWeekBalance::class]);
    $this->travelTo(Date::parse('2026-03-02 00:15:00', 'UTC'));
    $settings = resolve(GeneralSettings::class);
    $settings->stopWorkTimeReset = null;
    $settings->save();
    $project = Project::create(['name' => 'Client', 'hourly_rate' => 100, 'currency' => 'EUR']);
    $timestamp = createBillingTimestamp($project, [
        'billable' => false, 'started_at' => '2026-03-01 23:50:00',
        'ended_at' => null, 'last_ping_at' => '2026-03-01 23:59:00',
    ]);

    TimestampService::ping();

    $this->assertDatabaseHas('timestamps', ['id' => $timestamp->id, 'billable' => false, 'ended_at' => '2026-03-01 23:59:59']);
    $this->assertDatabaseHas('timestamps', ['started_at' => '2026-03-02 00:00:00', 'ended_at' => null, 'billable' => false]);
    Queue::assertPushed(CalculateWeekBalance::class);
});

it('exports excluded time with zero amount and its full duration', function (string $extension, string $method): void {
    configureBillingExport();
    $project = Project::create(['name' => 'Client', 'hourly_rate' => 100, 'currency' => 'EUR']);
    createBillingTimestamp($project, ['description' => 'Charged']);
    createBillingTimestamp($project, ['description' => 'Excluded', 'billable' => false, 'started_at' => '2026-03-01 10:00:00', 'ended_at' => '2026-03-01 11:00:00']);
    $path = sys_get_temp_dir().'/'.uniqid('timescribe-billing-', true).'.'.$extension;

    try {
        new ExportService(['work'], '2026-03-01', '2026-03-02')->{$method}($path);
        $rows = SimpleExcelReader::create($path)->getRows()->all();

        expect($rows)->toBe([
            ['Description' => 'Excluded', 'Duration' => '01:00:00', 'Billable Amount' => '0.00'],
            ['Description' => 'Charged', 'Duration' => '01:00:00', 'Billable Amount' => '100.00'],
        ]);
    } finally {
        if (file_exists($path)) {
            unlink($path);
        }
    }
})->with([
    'CSV' => ['csv', 'exportAsCsv'],
    'Excel' => ['xlsx', 'exportAsExcel'],
]);

it('keeps excluded time in the PDF rows and work time summary', function (): void {
    Pdf::fake();
    configureBillingExport();
    $project = Project::create(['name' => 'Client', 'hourly_rate' => 100, 'currency' => 'EUR']);
    createBillingTimestamp($project, ['description' => 'Excluded', 'billable' => false]);

    new ExportService(['work'], '2026-03-01', '2026-03-02')->exportAsPdf('billing.pdf');

    Pdf::assertViewHas('timestamps', [['description' => 'Excluded', 'duration' => '01:00:00', 'billable_amount' => '0.00']]);
    Pdf::assertViewHas('workTime', 3600.0);
    Pdf::assertSaved('billing.pdf');
});
