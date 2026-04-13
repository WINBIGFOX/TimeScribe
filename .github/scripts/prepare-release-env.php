<?php

declare(strict_types=1);

/**
 * @param  array<string, string>  $variables
 */
function prepareReleaseEnvironmentFile(
    string $templatePath,
    string $outputPath,
    array $variables,
): void {
    $contents = file_get_contents($templatePath);

    if ($contents === false) {
        throw new RuntimeException(sprintf('Unable to read template file [%s].', $templatePath));
    }

    $lineEnding = detectLineEnding($contents);

    foreach ($variables as $key => $value) {
        $escapedValue = str_replace(['\\', '"'], ['\\\\', '\\"'], $value);
        $line = sprintf('%s="%s"', $key, $escapedValue);
        $pattern = '/^'.preg_quote($key, '/').'=.*$/m';

        if (preg_match($pattern, $contents) === 1) {
            $contents = (string) preg_replace($pattern, $line, $contents, 1);

            continue;
        }

        if ($contents !== '' && ! str_ends_with($contents, $lineEnding)) {
            $contents .= $lineEnding;
        }

        $contents .= $line.$lineEnding;
    }

    file_put_contents($outputPath, $contents);
}

function detectLineEnding(string $contents): string
{
    if (str_contains($contents, "\r\n")) {
        return "\r\n";
    }

    if (str_contains($contents, "\n")) {
        return "\n";
    }

    if (str_contains($contents, "\r")) {
        return "\r";
    }

    return PHP_EOL;
}

/**
 * @param  list<string>  $keys
 * @return array<string, string>
 */
function releaseEnvironmentValues(array $keys): array
{
    $values = [];

    foreach ($keys as $key) {
        $value = getenv($key);
        $values[$key] = $value === false ? '' : $value;
    }

    return $values;
}

function runPrepareReleaseEnvironmentFile(): void
{
    prepareReleaseEnvironmentFile(
        templatePath: '.env.example',
        outputPath: '.env',
        variables: releaseEnvironmentValues([
            'APP_KEY',
            'APP_ENV',
            'APP_DEBUG',
            'NATIVEPHP_APP_VERSION',
            'NATIVEPHP_APP_ID',
            'NATIVEPHP_APP_AUTHOR',
            'NATIVEPHP_APP_DESCRIPTION',
            'NATIVEPHP_APP_COPYRIGHT',
            'NATIVEPHP_APP_WEBSITE',
            'NATIVEPHP_UPDATER_PROVIDER',
            'NATIVEPHP_UPDATER_ENABLED',
            'GITHUB_OWNER',
            'GITHUB_REPO',
            'GITHUB_TOKEN',
            'GOOGLE_ANALYTICS_ID',
            'VITE_APP_SENTRY_VUE_DSN',
            'SENTRY_LARAVEL_DSN',
            'SENTRY_RELEASE',
        ]),
    );
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    runPrepareReleaseEnvironmentFile();
}
