<?php

declare(strict_types=1);

use App\Settings\ShortcutSettings;

it('saves shortcut settings', function (): void {
    $response = $this->patch(route('settings.shortcuts.update'), [
        'startShortcut' => 'Cmd+Shift+S',
        'stopShortcut' => 'Cmd+Shift+X',
        'pauseShortcut' => 'Cmd+Shift+P',
        'overviewShortcut' => 'Cmd+Shift+O',
    ]);

    $response->assertRedirect(route('settings.shortcuts.edit'));

    $settings = app(ShortcutSettings::class);

    expect($settings->startShortcut)->toBe('Cmd+Shift+S')
        ->and($settings->stopShortcut)->toBe('Cmd+Shift+X')
        ->and($settings->pauseShortcut)->toBe('Cmd+Shift+P')
        ->and($settings->overviewShortcut)->toBe('Cmd+Shift+O');
});

it('rejects duplicate shortcuts', function (): void {
    $response = $this->from(route('settings.shortcuts.edit'))->patch(route('settings.shortcuts.update'), [
        'startShortcut' => 'Cmd+Shift+S',
        'stopShortcut' => 'Cmd+Shift+S',
        'pauseShortcut' => 'Cmd+Shift+P',
        'overviewShortcut' => null,
    ]);

    $response->assertSessionHasErrors(['startShortcut', 'stopShortcut']);
});
