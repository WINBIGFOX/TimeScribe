<?php

declare(strict_types=1);

use App\Services\ShortcutService;
use App\Settings\ShortcutSettings;

use function Pest\Laravel\mock;

beforeEach(function (): void {
    mock(ShortcutService::class)
        ->shouldReceive('refresh')
        ->andReturnNull();
});

it('saves shortcut settings', function (): void {
    $response = $this->patch(route('settings.shortcuts.update'), [
        'startShortcut' => 'Cmd+Shift+S',
        'stopShortcut' => 'Cmd+Shift+X',
        'pauseShortcut' => 'Cmd+Shift+P',
        'overviewShortcut' => 'Cmd+Shift+O',
        'projectPickerShortcut' => 'Cmd+Shift+L',
    ]);

    $response->assertRedirect(route('settings.shortcuts.edit'));

    $settings = resolve(ShortcutSettings::class);

    expect($settings->startShortcut)->toBe('Cmd+Shift+S')
        ->and($settings->stopShortcut)->toBe('Cmd+Shift+X')
        ->and($settings->pauseShortcut)->toBe('Cmd+Shift+P')
        ->and($settings->overviewShortcut)->toBe('Cmd+Shift+O')
        ->and($settings->projectPickerShortcut)->toBe('Cmd+Shift+L');
});

it('rejects duplicate shortcuts', function (): void {
    $response = $this->from(route('settings.shortcuts.edit'))->patch(route('settings.shortcuts.update'), [
        'startShortcut' => 'Cmd+Shift+S',
        'stopShortcut' => 'Cmd+Shift+S',
        'pauseShortcut' => 'Cmd+Shift+P',
        'overviewShortcut' => null,
        'projectPickerShortcut' => null,
    ]);

    $response->assertSessionHasErrors(['startShortcut', 'stopShortcut']);
});

it('rejects duplicate project picker shortcuts', function (): void {
    $response = $this->from(route('settings.shortcuts.edit'))->patch(route('settings.shortcuts.update'), [
        'startShortcut' => 'Cmd+Shift+S',
        'stopShortcut' => 'Cmd+Shift+X',
        'pauseShortcut' => 'Cmd+Shift+P',
        'overviewShortcut' => 'Cmd+Shift+O',
        'projectPickerShortcut' => 'Cmd+Shift+S',
    ]);

    $response->assertSessionHasErrors(['startShortcut', 'projectPickerShortcut']);
});
