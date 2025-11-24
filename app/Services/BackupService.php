<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Native\Desktop\Support\Environment;
use Spatie\DbDumper\Databases\Sqlite;
use ZipArchive;

class BackupService
{
    private const string TEMP_BACKUP_PATH = 'backup';

    private string $backupFileName = 'TimeScribe-Backup';

    private const string BACKUP_FILE_EXTENSION = 'bak';

    private const string SQL_FILENAME = 'database.sql';

    private const string MANIFEST_FILENAME = 'manifest.json';

    private const int BACKUP_VERSION = 1;

    private const array STORAGE_FILES_OR_FOLDERS_TO_BACKUP = [
        'app_icons',
        'logs',
    ];

    public function create(string $path): string
    {
        if (is_file($path) && pathinfo($path, PATHINFO_FILENAME)) {
            $this->backupFileName = pathinfo($path, PATHINFO_FILENAME);
        }

        $this->prepareBackup();
        $this->makeDbDump();
        $this->addDropTableStatements();
        $this->createManifest();
        $backupPath = $this->zipBackup($path);
        $this->cleanup();

        return $backupPath;
    }

    public function backupFileExists(string $path): bool
    {
        return file_exists($path.'/'.$this->backupFileName.'.'.self::BACKUP_FILE_EXTENSION);
    }

    private function prepareBackup(): void
    {
        File::ensureDirectoryExists(storage_path(self::TEMP_BACKUP_PATH));
        File::delete(storage_path(self::TEMP_BACKUP_PATH.'/'.self::SQL_FILENAME));
        File::delete(storage_path(self::TEMP_BACKUP_PATH.'/'.self::MANIFEST_FILENAME));
    }

    private function cleanup(): void
    {
        File::deleteDirectory(storage_path(self::TEMP_BACKUP_PATH));
    }

    private function makeDbDump(): void
    {
        $dbPath = DB::connection()->getConfig('database');

        $sqlDumper = Sqlite::create()->setDbName($dbPath);

        if (Environment::isWindows()) {
            $sqlDumper->setDumpBinaryPath(public_path());
        }

        $sqlDumper->dumpToFile(storage_path(self::TEMP_BACKUP_PATH.'/'.self::SQL_FILENAME));
    }

    private function addDropTableStatements(): void
    {
        $inputFile = storage_path(self::TEMP_BACKUP_PATH.'/'.self::SQL_FILENAME);
        $tempFile = storage_path(self::TEMP_BACKUP_PATH.'/temp.sql');

        $in = fopen($inputFile, 'r');
        $out = fopen($tempFile, 'w');

        if (! $in || ! $out) {
            throw new \Exception(__('app.backup could not be created.'));
        }

        $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");

        $dropStatements = '';
        foreach ($tables as $table) {
            $dropStatements .= 'DROP TABLE IF EXISTS "'.$table->name.'";'.PHP_EOL;
        }

        while (($line = fgets($in)) !== false) {
            if (str_starts_with(trim($line), 'INSERT INTO failed_jobs')) {
                continue;
            }

            fwrite($out, $line);

            if (trim($line) === 'BEGIN TRANSACTION;') {
                fwrite($out, $dropStatements);
            }
        }

        fclose($in);
        fclose($out);

        if (! rename($tempFile, $inputFile)) {
            throw new \Exception(__('app.backup could not be created.'));
        }
    }

    private function createManifest(): void
    {
        $manifest = [
            'backup_version' => self::BACKUP_VERSION,
            'app_version' => config('nativephp.version'),
            'created_at' => now()->toIso8601String(),
            'files' => $this->manifestFiles(),
        ];

        File::put(
            storage_path(self::TEMP_BACKUP_PATH.'/'.self::MANIFEST_FILENAME),
            json_encode($manifest, JSON_PRETTY_PRINT)
        );
    }

    private function manifestFiles(): array
    {
        $files = [];

        foreach ($this->filesToBackup() as $relativePath) {
            $absolutePath = storage_path($relativePath);

            if (! File::isFile($absolutePath)) {
                continue;
            }

            $files[$relativePath] = [
                'sha256' => hash_file('sha256', $absolutePath),
                'size' => File::size($absolutePath),
            ];
        }

        return $files;
    }

    private function filesToBackup(): array
    {
        $files = [];

        $sqlPath = self::TEMP_BACKUP_PATH.'/'.self::SQL_FILENAME;

        if (File::isFile(storage_path($sqlPath))) {
            $files[] = $sqlPath;
        }

        foreach (self::STORAGE_FILES_OR_FOLDERS_TO_BACKUP as $fileOrFolder) {
            if (File::isDirectory(storage_path($fileOrFolder))) {
                $storageFiles = File::allFiles(storage_path($fileOrFolder));

                foreach ($storageFiles as $file) {
                    $files[] = $fileOrFolder.'/'.$file->getRelativePathname();
                }
            } elseif (File::isFile(storage_path($fileOrFolder))) {
                $files[] = $fileOrFolder;
            }
        }

        $manifestPath = self::TEMP_BACKUP_PATH.'/'.self::MANIFEST_FILENAME;

        if (File::isFile(storage_path($manifestPath))) {
            $files[] = $manifestPath;
        }

        return $files;
    }

    private function zipBackup(string $destination): string
    {
        $zip = new ZipArchive;
        $backupFilePath = $destination.'/'.$this->backupFileName.'.'.self::BACKUP_FILE_EXTENSION;

        if ($zip->open($backupFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception(__('app.backup could not be created.'));
        }

        foreach ($this->filesToBackup() as $relativePath) {
            $absolutePath = storage_path($relativePath);

            if (! $zip->addFile($absolutePath, $relativePath)) {
                $zip->close();

                throw new \Exception(__('app.backup could not be created.'));
            }
        }

        if (! $zip->close()) {
            throw new \Exception(__('app.backup could not be created.'));
        }

        return $backupFilePath;
    }

    public function restore(string $path): void
    {
        if (! is_file($path) || pathinfo($path, PATHINFO_EXTENSION) !== self::BACKUP_FILE_EXTENSION) {
            throw new \Exception(__('app.restore failed.'));
        }

        $manifest = $this->readManifest($path);

        if ($manifest !== null) {
            $this->assertAppVersionIsCompatible($manifest);
        }

        $this->restoreFiles($path);
        if ($manifest !== null) {
            $this->assertChecksums($manifest);
        }
        if ($manifest === null) {
            $this->sanitizeLegacySqlFile();
        }
        $this->restoreDatabase();
        $this->cleanup();
        Cache::flush();
    }

    private function restoreFiles(string $path): void
    {
        $zip = new ZipArchive;
        if ($zip->open($path) === true) {
            $zip->extractTo(storage_path());
            $zip->close();
        } else {
            throw new \Exception(__('app.restore failed.'));
        }
    }

    private function restoreDatabase(): void
    {
        $databaseSqlPath = storage_path(self::TEMP_BACKUP_PATH.'/'.self::SQL_FILENAME);

        if (! file_exists($databaseSqlPath)) {
            throw new \Exception(__('app.restore failed.'));
        }

        static::dropExistingTablesAndViews();
        $this->executeSqlDump($databaseSqlPath);
        $this->truncateFailedJobs();
        Artisan::call('migrate', ['--force' => true]);
        Artisan::call('native:migrate', ['--force' => true]);
        Artisan::call('db:optimize');
    }

    public static function dropExistingTablesAndViews(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF;');

        $structures = DB::select("SELECT name, type FROM sqlite_master WHERE type IN ('table','view') AND name NOT LIKE 'sqlite_%'");

        foreach ($structures as $structure) {
            $dropStatement = $structure->type === 'view'
                ? 'DROP VIEW IF EXISTS "'.$structure->name.'";'
                : 'DROP TABLE IF EXISTS "'.$structure->name.'";';

            DB::statement($dropStatement);
        }

        DB::statement('PRAGMA foreign_keys = ON;');
    }

    private function truncateFailedJobs(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('failed_jobs')) {
            return;
        }

        DB::table('failed_jobs')->truncate();
    }

    private function sanitizeLegacySqlFile(): void
    {
        $inputFile = storage_path(self::TEMP_BACKUP_PATH.'/'.self::SQL_FILENAME);
        $tempFile = storage_path(self::TEMP_BACKUP_PATH.'/temp.sql');

        $in = fopen($inputFile, 'r');
        $out = fopen($tempFile, 'w');

        if (! $in || ! $out) {
            throw new \Exception(__('app.restore failed.'));
        }

        while (($line = fgets($in)) !== false) {
            if (str_starts_with(trim($line), 'INSERT INTO "failed_jobs"')) {
                continue;
            }

            fwrite($out, $line);
        }

        fclose($in);
        fclose($out);

        if (! rename($tempFile, $inputFile)) {
            throw new \Exception(__('app.restore failed.'));
        }
    }

    private function executeSqlDump(string $path): void
    {
        $handle = fopen($path, 'r');

        if (! $handle) {
            throw new \Exception(__('app.restore failed.'));
        }

        $buffer = '';

        while (($line = fgets($handle)) !== false) {
            if (str_starts_with(trim($line), 'INSERT INTO failed_jobs')) {
                continue;
            }

            $buffer .= $line;

            while (($position = strpos($buffer, ';')) !== false) {
                $statement = trim(substr($buffer, 0, $position + 1));
                $buffer = substr($buffer, $position + 1);

                if ($statement === '') {
                    continue;
                }

                try {
                    DB::unprepared($statement);
                } catch (\Exception) {
                    //
                }
            }
        }

        fclose($handle);
    }

    private function readManifest(string $path): ?array
    {
        $zip = new ZipArchive;

        if ($zip->open($path) !== true) {
            throw new \Exception(__('app.restore failed.'));
        }

        $manifestContent = $zip->getFromName(self::TEMP_BACKUP_PATH.'/'.self::MANIFEST_FILENAME);
        $zip->close();

        if ($manifestContent === false) {
            return null;
        }

        $manifest = json_decode($manifestContent, true);

        if (! is_array($manifest)) {
            throw new \Exception(__('app.restore failed.'));
        }

        if (! array_key_exists('app_version', $manifest)) {
            return null;
        }

        return $manifest;
    }

    private function assertAppVersionIsCompatible(array $manifest): void
    {
        $backupAppVersion = $manifest['app_version'] ?? null;
        $currentAppVersion = (string) config('nativephp.version');

        if (! is_string($backupAppVersion)) {
            throw new \Exception(__('app.restore failed.'));
        }

        if (version_compare($backupAppVersion, $currentAppVersion, '>')) {
            throw new \Exception(__('app.restore failed.'));
        }
    }

    private function assertChecksums(array $manifest): void
    {
        if (! isset($manifest['files']) || ! is_array($manifest['files'])) {
            return;
        }

        foreach ($manifest['files'] as $relativePath => $metadata) {
            $absolutePath = storage_path($relativePath);

            if (! File::isFile($absolutePath)) {
                throw new \Exception(__('app.restore failed.'));
            }

            if (! isset($metadata['sha256'])) {
                continue;
            }

            $hash = hash_file('sha256', $absolutePath);

            if ($hash !== $metadata['sha256']) {
                throw new \Exception(__('app.restore failed.'));
            }
        }
    }
}
