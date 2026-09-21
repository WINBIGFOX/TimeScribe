<?php

declare(strict_types=1);

use App\Enums\AppCategoryEnum;
use App\Enums\TimestampTypeEnum;
use App\Http\Resources\TimestampResource;
use App\Models\ActivityHistory;
use App\Models\Timestamp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;

it('groups overlapping app activity by program and clips it to the timestamp', function (): void {
    $timestamp = Timestamp::create([
        'type' => TimestampTypeEnum::WORK,
        'started_at' => Date::parse('2025-01-01 10:00:00'),
        'ended_at' => Date::parse('2025-01-01 11:00:00'),
    ]);

    ActivityHistory::create([
        'app_identifier' => 'com.google.chrome',
        'app_name' => 'Google Chrome',
        'app_icon' => 'chrome.png',
        'app_category' => AppCategoryEnum::Productivity,
        'started_at' => Date::parse('2025-01-01 09:50:00'),
        'ended_at' => Date::parse('2025-01-01 10:10:00'),
        'duration' => 1200,
    ]);
    ActivityHistory::create([
        'app_identifier' => 'com.microsoft.vscode',
        'app_name' => 'Visual Studio Code',
        'app_icon' => 'vscode.png',
        'app_category' => AppCategoryEnum::DeveloperTools,
        'started_at' => Date::parse('2025-01-01 10:15:00'),
        'ended_at' => Date::parse('2025-01-01 10:30:00'),
        'duration' => 900,
    ]);
    ActivityHistory::create([
        'app_identifier' => 'com.microsoft.vscode',
        'app_name' => 'Visual Studio Code',
        'app_icon' => 'vscode.png',
        'app_category' => AppCategoryEnum::DeveloperTools,
        'started_at' => Date::parse('2025-01-01 10:35:00'),
        'ended_at' => Date::parse('2025-01-01 11:10:00'),
        'duration' => 2100,
    ]);
    ActivityHistory::create([
        'app_identifier' => 'com.tinyspeck.slackmacgap',
        'app_name' => 'Slack',
        'started_at' => Date::parse('2025-01-01 09:00:00'),
        'ended_at' => Date::parse('2025-01-01 09:30:00'),
        'duration' => 1800,
    ]);
    ActivityHistory::create([
        'app_identifier' => 'com.apple.terminal',
        'app_name' => 'Terminal',
        'app_icon' => 'terminal.png',
        'started_at' => Date::parse('2025-01-01 10:45:00'),
        'ended_at' => Date::parse('2025-01-01 10:50:00'),
        'duration' => 300,
    ]);

    expect($timestamp->app_usage->all())->toBe([
        [
            'app_name' => 'Visual Studio Code',
            'app_identifier' => 'com.microsoft.vscode',
            'app_icon' => route('app-icon.show', ['appIconName' => 'vscode.png']),
            'app_category' => AppCategoryEnum::DeveloperTools->value,
            'duration' => 2400,
        ],
        [
            'app_name' => 'Google Chrome',
            'app_identifier' => 'com.google.chrome',
            'app_icon' => route('app-icon.show', ['appIconName' => 'chrome.png']),
            'app_category' => AppCategoryEnum::Productivity->value,
            'duration' => 600,
        ],
        [
            'app_name' => 'Terminal',
            'app_identifier' => 'com.apple.terminal',
            'app_icon' => route('app-icon.show', ['appIconName' => 'terminal.png']),
            'duration' => 300,
        ],
    ]);

    expect(TimestampResource::make($timestamp)->resolve(new Request))
        ->not->toHaveKey('app_usage');

    expect(TimestampResource::make($timestamp->append('app_usage'))->resolve(new Request)['app_usage'])
        ->toEqual($timestamp->app_usage);
});
