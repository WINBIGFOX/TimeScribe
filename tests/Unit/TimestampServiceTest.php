<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Date;
use App\Services\TimestampService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

afterEach(function (): void {
    Date::setTestNow();
});

it('summarizes timestamp durations and project details without using the database', function (): void {
    Date::setTestNow('2025-01-15 12:00:00');

    $projectTimestamp = (object) [
        'started_at' => Date::parse('2025-01-15 10:00:00'),
        'ended_at' => null,
        'last_ping_at' => Date::parse('2025-01-15 10:45:00'),
        'project_id' => 7,
        'project' => (object) [
            'name' => 'Client Portal',
            'color' => '#1f7a8c',
            'icon' => 'briefcase',
        ],
    ];

    $plainTimestamp = (object) [
        'started_at' => Date::parse('2025-01-15 08:00:00'),
        'ended_at' => Date::parse('2025-01-15 09:30:00'),
        'last_ping_at' => Date::parse('2025-01-15 09:30:00'),
        'project_id' => null,
    ];

    $method = new ReflectionMethod(TimestampService::class, 'summarizeTimeResult');

    $result = $method->invoke(
        null,
        new Collection([$plainTimestamp, $projectTimestamp]),
        Date::parse('2025-01-15 00:00:00'),
        Date::parse('2025-01-15 23:59:59'),
        1800,
        true,
        null,
    );

    expect($result['sum'])->toBe(14400.0)
        ->and($result['projects'])->toHaveKey(7)
        ->and($result['projects'][7]['sum'])->toBe(7200.0)
        ->and($result['projects'][7]['name'])->toBe('Client Portal')
        ->and($result['projects'][7]['color'])->toBe('#1f7a8c')
        ->and($result['projects'][7]['icon'])->toBe('briefcase');
});
