<?php

declare(strict_types=1);

namespace App\Services;

class BackupServiceResolver
{
    public const string DRIVER_LEGACY = 'legacy';

    public const string DRIVER_SNAPSHOT_SQL = 'snapshot_sql';

    public function resolve(): BackupService|BackupSqlSnapshotService
    {
        $driver = config('nativephp.backup_driver', self::DRIVER_LEGACY);

        return match ($driver) {
            self::DRIVER_SNAPSHOT_SQL => new BackupSqlSnapshotService,
            default => new BackupService,
        };
    }
}
