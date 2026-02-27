<?php

declare(strict_types=1);

use App\Services\BackupSqlSnapshotService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

uses(TestCase::class, DatabaseMigrations::class);

it('creates a sql backup from a sqlite snapshot', function (): void {
    DB::table('users')->insert([
        'name' => 'Backup Snapshot User',
        'email' => 'snapshot@example.com',
        'password' => 'secret',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('failed_jobs')->insert([
        'uuid' => (string) Str::uuid(),
        'connection' => 'sync',
        'queue' => 'default',
        'payload' => '{"job":"Test"}',
        'exception' => 'Failed test job',
        'failed_at' => now(),
    ]);

    File::ensureDirectoryExists(storage_path('testing'));

    $backupPath = (new BackupSqlSnapshotService)->create(storage_path('testing'));

    expect(File::isFile($backupPath))->toBeTrue();

    $zip = new ZipArchive;
    expect($zip->open($backupPath))->toBeTrue();

    $dumpContent = $zip->getFromName('backup/database.sql');
    $manifestContent = $zip->getFromName('backup/manifest.json');

    $zip->close();

    expect($dumpContent)->toBeString()
        ->and($dumpContent)->toContain('DROP TABLE IF EXISTS "users";')
        ->and($dumpContent)->toContain('INSERT INTO "users"')
        ->and($dumpContent)->not->toContain('INSERT INTO "failed_jobs"')
        ->and($manifestContent)->toBeString();
});
