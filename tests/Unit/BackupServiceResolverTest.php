<?php

declare(strict_types=1);

use App\Services\BackupService;
use App\Services\BackupServiceResolver;
use App\Services\BackupSqlSnapshotService;
use Tests\TestCase;

uses(TestCase::class);

it('resolves legacy backup driver', function (): void {
    config()->set('nativephp.backup_driver', BackupServiceResolver::DRIVER_LEGACY);

    $service = (new BackupServiceResolver)->resolve();

    expect($service)->toBeInstanceOf(BackupService::class);
});

it('resolves snapshot sql backup driver', function (): void {
    config()->set('nativephp.backup_driver', BackupServiceResolver::DRIVER_SNAPSHOT_SQL);

    $service = (new BackupServiceResolver)->resolve();

    expect($service)->toBeInstanceOf(BackupSqlSnapshotService::class);
});

it('falls back to legacy backup driver for unknown values', function (): void {
    config()->set('nativephp.backup_driver', 'unknown-driver');

    $service = (new BackupServiceResolver)->resolve();

    expect($service)->toBeInstanceOf(BackupService::class);
});
