<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ActivityHistory;
use App\Services\LocaleService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ActiveApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:active-app';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        new LocaleService;
        $this->detectingActiveApp();
    }

    private function detectingActiveApp(): void
    {
        $pid = shell_exec("osascript -e 'tell application \"System Events\" to get unix id of first process whose frontmost is true'");
        $pid = filter_var($pid, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_EMPTY_STRING_NULL | FILTER_NULL_ON_FAILURE);

        if (! $pid) {
            return;
        }

        $info = shell_exec('lsappinfo info -only LSDisplayName, bundlePath -app '.$pid);

        $appName = preg_match('/"?LSDisplayName"?="([^"]+)"/', $info, $matches) ? $matches[1] : null;
        $appPath = preg_match('/"?LSBundlePath"?="([^"]+)"/', $info, $matches) ? $matches[1] : null;

        $appName = filter_var($appName, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_EMPTY_STRING_NULL | FILTER_NULL_ON_FAILURE);
        $appPath = filter_var($appPath, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_EMPTY_STRING_NULL | FILTER_NULL_ON_FAILURE);

        if (! $appName || ! $appPath) {
            return;
        }

        $bundleIconInfo = shell_exec('defaults read "'.$appPath.'/Contents/Info.plist" CFBundleIconFile');
        $bundleIconName = shell_exec('defaults read "'.$appPath.'/Contents/Info.plist" CFBundleIconName');
        $bundleIdentifier = shell_exec('defaults read "'.$appPath.'/Contents/Info.plist" CFBundleIdentifier');
        $bundleAppCategoryType = shell_exec('defaults read "'.$appPath.'/Contents/Info.plist" LSApplicationCategoryType');

        $iconFile = filter_var($bundleIconInfo, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_EMPTY_STRING_NULL | FILTER_NULL_ON_FAILURE);
        $iconName = filter_var($bundleIconName, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_EMPTY_STRING_NULL | FILTER_NULL_ON_FAILURE);
        $bundleIdentifier = filter_var($bundleIdentifier, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_EMPTY_STRING_NULL | FILTER_NULL_ON_FAILURE);
        $bundleAppCategoryType = filter_var($bundleAppCategoryType, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_EMPTY_STRING_NULL | FILTER_NULL_ON_FAILURE);

        if (! $bundleIdentifier) {
            return;
        }

        $iconImageFile = $this->saveIcon($appPath, $bundleIdentifier, $iconFile, $iconName);

        if (! $iconImageFile) {
            return;
        }

        if ($bundleAppCategoryType === '') {
            $bundleAppCategoryType = null;
        }

        $activity = ActivityHistory::active()->latest()->first();

        if ($activity && $activity->app_identifier === $bundleIdentifier) {
            $nextEndedAt = Carbon::now()->addSeconds(4);
            $activity->update([
                'duration' => (int) $activity->started_at->diffInSeconds($nextEndedAt),
                'ended_at' => $nextEndedAt,
            ]);
        } else {
            $endedAt = Carbon::now()->subSecond();
            $activity?->update([
                'duration' => (int) $activity->started_at->diffInSeconds($endedAt),
                'ended_at' => $endedAt,
            ]);
            ActivityHistory::create([
                'app_name' => $appName,
                'app_identifier' => $bundleIdentifier,
                'app_icon' => $iconImageFile,
                'app_category' => $bundleAppCategoryType,
                'started_at' => Carbon::now(),
                'duration' => 4,
                'ended_at' => Carbon::now()->addSeconds(4),
            ]);
        }
    }

    private function saveIcon(string $appPath, string $bundleIdentifier, ?string $iconFile = null, ?string $iconName = null): ?string
    {
        $bundleIdentifierKey = strtolower(str_replace('.', '_', $bundleIdentifier));

        if (File::exists(storage_path('app_icons/'.$bundleIdentifierKey.'.png'))) {
            $timestamp = filemtime(storage_path('app_icons/'.$bundleIdentifierKey.'.png'));
            if ($timestamp && Carbon::createFromTimestamp($timestamp)->diffInDays() < 30) {
                return $bundleIdentifierKey.'.png';
            }
        }

        if ($iconFile === '') {
            $iconFile = null;
        }

        if ($iconFile && ! str_ends_with($iconFile, '.icns')) {
            $iconFile .= '.icns';
        }

        if ($iconName === '') {
            $iconName = null;
        }

        $fullIconPath = null;
        $assetCar = false;
        if ($iconFile) {
            $fullIconPath = $appPath.'/Contents/Resources/'.$iconFile;
        }

        if (! $fullIconPath && $iconName) {
            if (! file_exists($appPath.'/Contents/Resources/Assets.car')) {
                return null;
            }

            if (! File::isDirectory(storage_path('app_icns'))) {
                File::makeDirectory(storage_path('app_icns'));
            }

            shell_exec('iconutil -c icns "'.$appPath.'/Contents/Resources/Assets.car" '.$iconName.' -o "'.storage_path('app_icns/'.$bundleIdentifierKey.'.icns').'"');

            if (File::exists(storage_path('app_icns/'.$bundleIdentifierKey.'.icns'))) {
                $fullIconPath = storage_path('app_icns/'.$bundleIdentifierKey.'.icns');
                $assetCar = true;
            }
        }

        if (! $fullIconPath) {
            return null;
        }

        if (! File::isDirectory(storage_path('app_icons'))) {
            File::makeDirectory(storage_path('app_icons'));
        }

        shell_exec('sips -z 128 128 -s format png "'.$fullIconPath.'" -o "'.storage_path('app_icons/'.$bundleIdentifierKey.'.png').'"');

        if ($assetCar) {
            File::delete($fullIconPath);
        }

        return $bundleIdentifierKey.'.png';
    }
}
