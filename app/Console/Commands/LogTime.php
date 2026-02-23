<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\TimestampTypeEnum;
use App\Models\Project;
use App\Models\Timestamp;
use App\Services\LocaleService;
use App\Services\NaturalLanguage\TimeLogTextParser;
use App\Services\TimestampService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class LogTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'timescribe:log
        {text : Natural language description (e.g. "yesterday worked on Acme from 09:00 to 11:00")}
        {--project= : Project name (preferred when unsure)}
        {--type= : work|break (overrides detection)}
        {--date= : Date override (YYYY-MM-DD)}
        {--start= : Start time override (e.g. 09:00, 9am)}
        {--end= : End time override (e.g. 11:30)}
        {--duration= : Duration override (e.g. 2h, 90m, 1h30m)}
        {--create-project : Create the project if it does not exist}
        {--source=Codex : Value for timestamps.source}
        {--carve : Allow trimming/splitting existing timestamps if overlapping}
        {--force-overwrite : Overwrite overlaps without asking}
        {--dry-run : Print parsed result without writing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Log a work/break timestamp from natural language';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        new LocaleService;

        $text = (string) $this->argument('text');

        $typeOption = $this->option('type');
        $type = filled($typeOption)
            ? TimestampTypeEnum::tryFrom((string) $typeOption)
            : null;

        if (filled($typeOption) && ! $type instanceof TimestampTypeEnum) {
            $this->error('Invalid --type. Use "work" or "break".');

            return self::FAILURE;
        }

        $parser = new TimeLogTextParser;
        $parsed = $parser->parse(
            text: $text,
            now: now(),
            typeOverride: $type,
            dateOverride: $this->option('date') ? (string) $this->option('date') : null,
            startOverride: $this->option('start') ? (string) $this->option('start') : null,
            endOverride: $this->option('end') ? (string) $this->option('end') : null,
            durationOverride: $this->option('duration') ? (string) $this->option('duration') : null,
        );

        if ($parsed['error']) {
            $this->error($parsed['error']);

            return self::FAILURE;
        }

        /** @var Carbon $startAt */
        $startAt = $parsed['start_at'];
        /** @var Carbon $endAt */
        $endAt = $parsed['end_at'];
        /** @var TimestampTypeEnum $resolvedType */
        $resolvedType = $parsed['type'];

        if ($parsed['guessed_start_at'] && $parsed['duration_seconds']) {
            $dayStart = $parsed['date']->copy()->startOfDay();
            $dayEnd = $parsed['date']->copy()->endOfDay();

            $last = Timestamp::where('started_at', '>=', $dayStart)
                ->where('started_at', '<=', $dayEnd)
                ->orderByRaw('COALESCE(ended_at, last_ping_at, started_at) DESC')
                ->first();

            if ($last) {
                $lastEnd = $last->ended_at ?? $last->last_ping_at ?? $last->started_at;
                $startAt = $lastEnd->copy();
                $endAt = $startAt->copy()->addSeconds((int) $parsed['duration_seconds']);
            }
        }

        if (! $startAt->isSameDay($endAt)) {
            $this->error('Cross-midnight ranges are not supported. Split into two logs.');

            return self::FAILURE;
        }

        if ($startAt->isAfter(now()) || $endAt->isAfter(now())) {
            $this->error('Start and end must be in the past.');

            return self::FAILURE;
        }

        $projectNameOption = $this->option('project') ? (string) $this->option('project') : null;
        $project = $this->resolveProject($projectNameOption, $text);

        if (! $project instanceof Project && filled($projectNameOption) && $this->option('create-project')) {
            $project = Project::create(['name' => $projectNameOption]);
        }

        if (! $project instanceof Project && $this->option('create-project')) {
            $candidate = $parsed['project_candidate'];
            if (filled($candidate)) {
                $project = Project::create(['name' => $candidate]);
            }
        }

        $projectId = $project?->id;
        $source = (string) $this->option('source');

        if ($this->option('dry-run')) {
            $this->line('Parsed:');
            $this->line('  Type: '.$resolvedType->value);
            $this->line('  Project: '.($project?->name ?? '(none)'));
            $this->line('  Start: '.$startAt->toDateTimeString());
            $this->line('  End: '.$endAt->toDateTimeString());
            $this->line('  Source: '.$source);

            return self::SUCCESS;
        }

        $carve = (bool) $this->option('carve');
        $forceOverwrite = (bool) $this->option('force-overwrite');
        $overlappingTimestamps = $this->getOverlappingTimestamps($startAt, $endAt);

        if ($overlappingTimestamps->isNotEmpty()) {
            $this->warn('Overlapping timestamps found:');
            $this->table(
                ['ID', 'Type', 'Project', 'Start', 'End', 'Source', 'Description'],
                $this->buildOverlapRows($overlappingTimestamps)
            );

            if (! $carve && ! $forceOverwrite) {
                if (! $this->input->isInteractive()) {
                    $this->error('Time range overlaps existing timestamps. Re-run with --carve or --force-overwrite.');

                    return self::FAILURE;
                }

                $carve = $this->confirm(
                    'Overwrite by trimming/splitting these existing timestamps?',
                    true
                );
                if (! $carve) {
                    $this->info('Canceled. No changes were made.');

                    return self::FAILURE;
                }
            } else {
                $carve = true;
            }
        }

        if ($carve) {
            TimestampService::create($startAt, $endAt, $resolvedType, $parsed['description'], $projectId);

            Timestamp::where('started_at', $startAt)
                ->where('ended_at', $endAt)
                ->where('type', $resolvedType)
                ->latest('id')
                ->first()?->update(['source' => $source]);
        } else {
            Timestamp::create([
                'type' => $resolvedType,
                'project_id' => $projectId,
                'started_at' => $startAt,
                'ended_at' => $endAt,
                'last_ping_at' => $endAt,
                'description' => $parsed['description'],
                'source' => $source,
            ]);
        }

        $this->info('Logged time successfully.');

        return self::SUCCESS;
    }

    private function resolveProject(?string $projectOption, string $text): ?Project
    {
        if (filled($projectOption)) {
            return Project::withTrashed()->where('name', $projectOption)->first();
        }

        $projects = Project::withTrashed()->get(['id', 'name']);
        $haystack = mb_strtolower($text);

        $best = null;
        foreach ($projects as $project) {
            $needle = mb_strtolower($project->name);
            if ($needle !== '' && str_contains($haystack, $needle)) {
                if (! $best || mb_strlen($project->name) > mb_strlen($best->name)) {
                    $best = $project;
                }
            }
        }

        return $best;
    }

    private function getOverlappingTimestamps(Carbon $startAt, Carbon $endAt): EloquentCollection
    {
        return Timestamp::where('started_at', '<', $endAt)
            ->where(function ($query) use ($startAt): void {
                $query->whereNull('ended_at')
                    ->orWhere('ended_at', '>', $startAt);
            })
            ->with('project:id,name')
            ->orderBy('started_at')
            ->get();
    }

    /**
     * @return array<int, array<int, string>>
     */
    private function buildOverlapRows(EloquentCollection $overlappingTimestamps): array
    {
        return $overlappingTimestamps->map(function (Timestamp $timestamp): array {
            $end = $timestamp->ended_at ?? $timestamp->last_ping_at ?? $timestamp->started_at;
            $description = trim((string) ($timestamp->description ?? ''));
            if (mb_strlen($description) > 50) {
                $description = mb_substr($description, 0, 47).'...';
            }

            return [
                (string) $timestamp->id,
                $timestamp->type->value,
                $timestamp->project?->name ?? '-',
                $timestamp->started_at->format('Y-m-d H:i'),
                $end->format('Y-m-d H:i'),
                $timestamp->source ?? '-',
                $description !== '' ? $description : '-',
            ];
        })->all();
    }
}
