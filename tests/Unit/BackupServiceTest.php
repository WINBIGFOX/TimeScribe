<?php

declare(strict_types=1);

use App\Services\BackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('rejects backups aus einer neueren App-Version', function (): void {
    File::ensureDirectoryExists(storage_path('testing'));

    $zip = new \ZipArchive;
    expect(
        $zip->open(storage_path('testing/newer-version.bak'), \ZipArchive::CREATE | \ZipArchive::OVERWRITE)
    )->toBeTrue();

    $manifest = [
        'backup_version' => 1,
        'app_version' => '9.9.9',
        'created_at' => now()->toIso8601String(),
        'files' => [],
    ];

    expect(
        $zip->addFromString('backup/manifest.json', json_encode($manifest))
    )->toBeTrue();
    $zip->close();

    config(['nativephp.version' => '1.0.0']);

    (new BackupService)->restore(storage_path('testing/newer-version.bak'));
})->throws(Exception::class);

it('entfernt Tabellen, die nach dem Backup hinzugekommen sind', function (): void {
    DB::shouldReceive('statement')->with('PRAGMA foreign_keys = OFF;')->once()->andReturn(true);
    DB::shouldReceive('select')
        ->once()
        ->andReturn([(object) ['name' => 'extra_table', 'type' => 'table']]);
    DB::shouldReceive('statement')->with('DROP TABLE IF EXISTS "extra_table";')->once()->andReturn(true);
    DB::shouldReceive('statement')->with('PRAGMA foreign_keys = ON;')->once()->andReturn(true);
    DB::shouldReceive('getSchemaBuilder')->andReturnSelf();
    DB::shouldReceive('hasTable')->with('failed_jobs')->andReturn(true);
    DB::shouldReceive('table')->with('failed_jobs')->andReturnSelf();
    DB::shouldReceive('truncate')->once()->andReturn(true);

    $sql = 'CREATE TABLE "old_table" ("id" integer);';

    File::ensureDirectoryExists(storage_path('backup'));
    File::put(storage_path('backup/database.sql'), $sql);

    $manifest = [
        'backup_version' => 1,
        'app_version' => '1.0.0',
        'created_at' => now()->toIso8601String(),
        'files' => [
            'backup/database.sql' => [
                'sha256' => hash_file('sha256', storage_path('backup/database.sql')),
                'size' => File::size(storage_path('backup/database.sql')),
            ],
        ],
    ];

    File::put(
        storage_path('backup/manifest.json'),
        json_encode($manifest, JSON_PRETTY_PRINT)
    );

    $zip = new \ZipArchive;
    $backupPath = storage_path('testing/dump-drop.bak');
    File::ensureDirectoryExists(storage_path('testing'));
    expect($zip->open($backupPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE))->toBeTrue();
    expect($zip->addFile(storage_path('backup/database.sql'), 'backup/database.sql'))->toBeTrue();
    expect($zip->addFile(storage_path('backup/manifest.json'), 'backup/manifest.json'))->toBeTrue();
    expect($zip->close())->toBeTrue();

    config(['nativephp.version' => '1.0.0']);

    DB::shouldReceive('unprepared')
        ->once()
        ->with(\Mockery::on(fn (string $content): bool => str_contains($content, 'old_table')))
        ->andReturn(true);
    Artisan::shouldReceive('call')->with('migrate', ['--force' => true])->once()->andReturn(0);
    Artisan::shouldReceive('call')->with('native:migrate', ['--force' => true])->once()->andReturn(0);
    Artisan::shouldReceive('call')->with('db:optimize')->once()->andReturn(0);
    Cache::shouldReceive('flush')->once();

    (new BackupService)->restore($backupPath);
});

it('akzeptiert legacy bak ohne Manifest', function (): void {
    DB::shouldReceive('statement')->with('PRAGMA foreign_keys = OFF;')->once()->andReturn(true);
    DB::shouldReceive('select')
        ->once()
        ->andReturn([(object) ['name' => 'legacy_table', 'type' => 'table']]);
    DB::shouldReceive('statement')->with('DROP TABLE IF EXISTS "legacy_table";')->once()->andReturn(true);
    DB::shouldReceive('statement')->with('PRAGMA foreign_keys = ON;')->once()->andReturn(true);
    DB::shouldReceive('getSchemaBuilder')->andReturnSelf();
    DB::shouldReceive('hasTable')->with('failed_jobs')->andReturn(true);
    DB::shouldReceive('table')->with('failed_jobs')->andReturnSelf();
    DB::shouldReceive('truncate')->once()->andReturn(true);

    $sql = implode(PHP_EOL, [
        'CREATE TABLE legacy_table ("id" integer);',
        'INSERT INTO failed_jobs values (1);',
    ]);

    File::ensureDirectoryExists(storage_path('backup'));
    File::put(storage_path('backup/database.sql'), $sql);

    $zip = new \ZipArchive;
    $backupPath = storage_path('testing/legacy-without-manifest.bak');
    File::ensureDirectoryExists(storage_path('testing'));
    expect($zip->open($backupPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE))->toBeTrue();
    expect($zip->addFile(storage_path('backup/database.sql'), 'backup/database.sql'))->toBeTrue();
    expect($zip->close())->toBeTrue();

    config(['nativephp.version' => '1.0.0']);

    DB::shouldReceive('unprepared')
        ->once()
        ->with(\Mockery::on(fn (string $content): bool => str_contains($content, 'legacy_table')))
        ->andReturn(true);
    Artisan::shouldReceive('call')->with('migrate', ['--force' => true])->once()->andReturn(0);
    Artisan::shouldReceive('call')->with('native:migrate', ['--force' => true])->once()->andReturn(0);
    Artisan::shouldReceive('call')->with('db:optimize')->once()->andReturn(0);
    Cache::shouldReceive('flush')->once();

    (new BackupService)->restore($backupPath);
});
