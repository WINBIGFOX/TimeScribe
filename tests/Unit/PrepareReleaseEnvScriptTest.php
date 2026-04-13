<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2).'/.github/scripts/prepare-release-env.php';

it('replaces existing values and appends missing release environment values', function (): void {
    $templatePath = tempnam(sys_get_temp_dir(), 'release-env-template-');
    $outputPath = tempnam(sys_get_temp_dir(), 'release-env-output-');

    expect($templatePath)->not->toBeFalse();
    expect($outputPath)->not->toBeFalse();

    file_put_contents($templatePath, implode("\n", [
        'APP_ENV=local',
        'APP_DEBUG=true',
        'APP_KEY=',
        'EXISTING_VALUE=keep-me',
    ])."\n");

    prepareReleaseEnvironmentFile($templatePath, $outputPath, [
        'APP_KEY' => 'base64:abc123',
        'APP_ENV' => 'local',
        'APP_DEBUG' => 'false',
        'NATIVEPHP_APP_DESCRIPTION' => 'TimeScribe "Desktop" \\ Preview',
    ]);

    $contents = file_get_contents($outputPath);

    expect($contents)->toContain('APP_KEY="base64:abc123"')
        ->toContain('APP_ENV="local"')
        ->toContain('APP_DEBUG="false"')
        ->toContain('EXISTING_VALUE=keep-me')
        ->toContain('NATIVEPHP_APP_DESCRIPTION="TimeScribe \\"Desktop\\" \\\\ Preview"');

    @unlink($templatePath);
    @unlink($outputPath);
});
