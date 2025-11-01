<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\AppCategoryEnum;
use App\Models\ActivityHistory;
use App\Services\LocaleService;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Native\Desktop\Support\Environment;

class ActiveApp extends Command
{
    private const int ACTIVITY_DURATION_SECONDS = 4;

    private const int ICON_CACHE_DAYS = 30;

    private const array EXCLUDED_APPS = [
        'GetActiveWindowTitle.exe',
        'explorer.exe',
    ];

    protected $signature = 'app:active-app';

    protected $description = 'Track active application window';

    public function handle(): void
    {
        new LocaleService;

        if (Environment::isWindows()) {
            $this->detectWindowsApp();
        }

        if (Environment::isMac()) {
            $this->detectMacApp();
        }
    }

    private function detectWindowsApp(): void
    {
        $output = shell_exec(public_path('GetActiveWindowTitle.exe'));

        if (! $output) {
            return;
        }

        $data = json_decode($output, true);

        if (! Arr::has($data, ['Path', 'Icon', 'Name'])) {
            return;
        }

        if (in_array(strtolower(basename((string) $data['Path'])), self::EXCLUDED_APPS)) {
            return;
        }

        $identifier = Str::slug($data['Path']);
        $name = $data['Name'];

        if (str_ends_with((string) $name, '.exe')) {
            $name = $this->camelCaseToString(substr((string) $name, 0, -4));
        }

        $appData = [
            'identifier' => $identifier,
            'name' => $name,
            'category' => null,
            'icon' => $this->saveWindowsIcon($identifier, $data['Icon']),
        ];

        if (! $appData['icon']) {
            return;
        }

        $this->updateActivity($appData);
    }

    private function detectMacApp(): void
    {
        $pid = shell_exec("osascript -e 'tell application \"System Events\" to get unix id of first process whose frontmost is true'");
        $pid = $this->filterString($pid);

        if (! $pid) {
            return;
        }

        $info = shell_exec('lsappinfo info -only LSDisplayName, bundlePath -app '.$pid);
        $appName = preg_match('/"?LSDisplayName"?="([^"]+)"/', $info, $matches) ? $matches[1] : null;
        $appPath = preg_match('/"?LSBundlePath"?="([^"]+)"/', $info, $matches) ? $matches[1] : null;

        $appName = $this->filterString($appName);
        $appPath = $this->filterString($appPath);

        if (! $appName || ! $appPath) {
            return;
        }

        if (in_array(strtolower(basename($appPath)), self::EXCLUDED_APPS)) {
            return;
        }

        $bundleInfo = $this->getMacBundleInfo($appPath);
        if (! $bundleInfo['identifier']) {
            return;
        }

        $iconFile = $this->saveIcon(
            $appPath,
            $bundleInfo['identifier'],
            $bundleInfo['iconFile'],
            $bundleInfo['iconName']
        );

        if (! $iconFile) {
            return;
        }

        $appData = [
            'identifier' => $bundleInfo['identifier'],
            'name' => $appName,
            'category' => $bundleInfo['category'] ?: null,
            'icon' => $iconFile,
        ];

        $this->updateActivity($appData);
    }

    private function getMacBundleInfo(string $appPath): array
    {
        return [
            'iconFile' => $this->filterString(shell_exec('defaults read "'.$appPath.'/Contents/Info.plist" CFBundleIconFile')),
            'iconName' => $this->filterString(shell_exec('defaults read "'.$appPath.'/Contents/Info.plist" CFBundleIconName')),
            'identifier' => $this->filterString(shell_exec('defaults read "'.$appPath.'/Contents/Info.plist" CFBundleIdentifier')),
            'category' => $this->filterString(shell_exec('defaults read "'.$appPath.'/Contents/Info.plist" LSApplicationCategoryType')),
        ];
    }

    private function filterString(?string $value): ?string
    {
        return filter_var($value, FILTER_UNSAFE_RAW,
            FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_EMPTY_STRING_NULL | FILTER_NULL_ON_FAILURE);
    }

    private function updateActivity(array $appData): void
    {
        $activity = ActivityHistory::active()->latest()->first();

        if ($activity && $activity->app_identifier === $appData['identifier']) {
            $nextEndedAt = Date::now()->addSeconds(self::ACTIVITY_DURATION_SECONDS);
            $activity->update([
                'duration' => (int) $activity->started_at->diffInSeconds($nextEndedAt),
                'ended_at' => $nextEndedAt,
            ]);
        } else {
            $this->createNewActivity($activity, $appData);
        }
    }

    private function createNewActivity(?ActivityHistory $previousActivity, array $appData): void
    {
        if ($previousActivity instanceof ActivityHistory) {
            $endedAt = Date::now()->subSecond();
            $previousActivity->update([
                'duration' => (int) $previousActivity->started_at->diffInSeconds($endedAt),
                'ended_at' => $endedAt,
            ]);
        }

        if ($appData['category'] && ! AppCategoryEnum::tryFrom($appData['category'])) {
            $appData['category'] = null;
        }

        ActivityHistory::create([
            'app_name' => $appData['name'],
            'app_identifier' => $appData['identifier'],
            'app_icon' => $appData['icon'],
            'app_category' => $appData['category'],
            'started_at' => Date::now(),
            'duration' => self::ACTIVITY_DURATION_SECONDS,
            'ended_at' => Date::now()->addSeconds(self::ACTIVITY_DURATION_SECONDS),
        ]);
    }

    private function saveWindowsIcon(string $bundleIdentifierKey, ?string $base64 = null): ?string
    {
        if ($this->isIconCacheValid('app_icons/'.$bundleIdentifierKey.'.png')) {
            return $bundleIdentifierKey.'.png';
        }

        if (! $base64) {
            return null;
        }

        $this->ensureDirectoryExists('app_icons');
        File::put(storage_path('app_icons/'.$bundleIdentifierKey.'.png'), base64_decode($base64));

        return $bundleIdentifierKey.'.png';
    }

    private function saveIcon(string $appPath, string $bundleIdentifier, ?string $iconFile = null, ?string $iconName = null): ?string
    {
        $bundleIdentifierKey = strtolower(str_replace('.', '_', $bundleIdentifier));

        if ($this->isIconCacheValid('app_icons/'.$bundleIdentifierKey.'.png')) {
            return $bundleIdentifierKey.'.png';
        }

        $iconFile = $iconFile === '' ? null : $iconFile;
        if ($iconFile && ! str_ends_with($iconFile, '.icns')) {
            $iconFile .= '.icns';
        }

        $iconName = $iconName === '' ? null : $iconName;
        $fullIconPath = null;
        $assetCar = false;

        if ($iconFile) {
            $fullIconPath = $appPath.'/Contents/Resources/'.$iconFile;
        }

        if (! $fullIconPath && $iconName) {
            $fullIconPath = $this->extractIconFromAssets($appPath, $bundleIdentifierKey, $iconName);
            $assetCar = (bool) $fullIconPath;
        }

        if (! $fullIconPath) {
            return null;
        }

        $this->ensureDirectoryExists('app_icons');
        shell_exec('sips -z 128 128 -s format png "'.$fullIconPath.'" -o "'.
            storage_path('app_icons/'.$bundleIdentifierKey.'.png').'"');

        if ($assetCar) {
            File::delete($fullIconPath);
        }

        return $bundleIdentifierKey.'.png';
    }

    private function extractIconFromAssets(string $appPath, string $bundleIdentifierKey, string $iconName): ?string
    {
        if (! file_exists($appPath.'/Contents/Resources/Assets.car')) {
            return null;
        }

        $this->ensureDirectoryExists('app_icns');
        $icnsPath = storage_path('app_icns/'.$bundleIdentifierKey.'.icns');

        shell_exec('iconutil -c icns "'.$appPath.'/Contents/Resources/Assets.car" '.
            $iconName.' -o "'.$icnsPath.'"');

        return File::exists($icnsPath) ? $icnsPath : null;
    }

    private function isIconCacheValid(string $relativePath): bool
    {
        $fullPath = storage_path($relativePath);
        if (! File::exists($fullPath)) {
            return false;
        }

        $timestamp = filemtime($fullPath);

        return $timestamp && Date::createFromTimestamp($timestamp)->diffInDays() < self::ICON_CACHE_DAYS;
    }

    private function ensureDirectoryExists(string $directory): void
    {
        $path = storage_path($directory);
        if (! File::isDirectory($path)) {
            File::makeDirectory($path);
        }
    }

    private function camelCaseToString(string $string): string
    {
        $pieces = preg_split('/(?=[A-Z])/', $string);
        $word = implode(' ', $pieces);

        return ucwords($word);
    }
}
