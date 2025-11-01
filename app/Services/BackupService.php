<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Native\Desktop\Support\Environment;
use Spatie\DbDumper\Databases\Sqlite;

class BackupService
{
    private const string TEMP_BACKUP_PATH = 'backup';

    private string $backupFileName = 'TimeScribe-Backup';

    private const string BACKUP_FILE_EXTENSION = 'bak';

    private const string SQL_FILENAME = 'database.sql';

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

        $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");

        $dropStatements = '';
        foreach ($tables as $table) {
            $dropStatements .= 'DROP TABLE IF EXISTS "'.$table->name.'";'.PHP_EOL;
        }

        if (! $in || ! $out) {
            throw new \Exception(__('app.backup could not be created.'));
        }

        while (($line = fgets($in)) !== false) {
            fwrite($out, $line);

            if (trim($line) === 'BEGIN TRANSACTION;') {
                fwrite($out, $dropStatements);
            }
        }

        fclose($in);
        fclose($out);

        rename($tempFile, $inputFile);
    }

    private function zipBackup(string $path): string
    {
        $zip = new \ZipArchive;
        if ($zip->open($path.'/'.$this->backupFileName.'.'.self::BACKUP_FILE_EXTENSION, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            foreach ([...self::STORAGE_FILES_OR_FOLDERS_TO_BACKUP, self::TEMP_BACKUP_PATH] as $fileOrFolder) {
                $path = $fileOrFolder;
                if (is_dir(storage_path($path))) {
                    $files = File::allFiles(storage_path($path));
                    foreach ($files as $file) {
                        $zip->addFile($file->getRealPath(), $path.'/'.$file->getRelativePathname());
                    }
                } elseif (is_file(storage_path($path))) {
                    $zip->addFile(storage_path($path), $path);
                }
            }
        } else {
            throw new \Exception(__('app.backup could not be created.'));
        }

        $zip->close();

        return $path.'/'.$this->backupFileName.'.'.self::BACKUP_FILE_EXTENSION;
    }

    public function restore(string $path): void
    {
        if (! file_exists($path)) {
            throw new \Exception(__('app.restore failed.'));
        }
        if (pathinfo($path, PATHINFO_EXTENSION) === 'bak') {
            $this->restoreFiles($path);
            $this->restoreDatabase();
            $this->cleanup();
        } else {
            $this->restoreOldBackup($path);
        }
        Cache::flush();
    }

    private function restoreFiles(string $path): void
    {
        $zip = new \ZipArchive;
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

        DB::unprepared(file_get_contents($databaseSqlPath));
        Artisan::call('migrate', ['--force' => true]);
        Artisan::call('native:migrate', ['--force' => true]);
        Artisan::call('db:optimize');
    }

    private function restoreOldBackup(string $path): void
    {
        $zip = new \ZipArchive;

        if ($zip->open($path) === true) {
            $nbFile = $zip->numFiles;
            for ($i = 0; $i < $nbFile; $i++) {
                if ($zip->getNameIndex($i) === 'database.sqlite') {
                    \DB::disconnect();
                    \DB::purge();
                    File::delete(storage_path('../database/database.sqlite-shm'));
                    File::delete(storage_path('../database/database.sqlite-wal'));
                    $zip->extractTo(storage_path('../database/'), ['database.sqlite']);
                    Artisan::call('migrate', ['--force' => true]);
                    Artisan::call('native:migrate', ['--force' => true]);
                    Artisan::call('db:optimize');
                    \DB::reconnect();
                } elseif (str_contains($zip->getNameIndex($i), 'app_icons/') || str_contains($zip->getNameIndex($i), 'logs/')) {
                    $zip->extractTo(storage_path(), [$zip->getNameIndex($i)]);
                }
            }
            $zip->close();
        } else {
            throw new \Exception(__('app.restore failed.'));
        }
    }
}
