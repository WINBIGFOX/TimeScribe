<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PDO;
use ZipArchive;

class BackupSqlSnapshotService
{
    private const string TEMP_BACKUP_PATH = 'backup';

    private string $backupFileName = 'TimeScribe-Backup';

    private const string BACKUP_FILE_EXTENSION = 'bak';

    private const string SQL_FILENAME = 'database.sql';

    private const string SNAPSHOT_FILENAME = 'database.snapshot.sqlite';

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
        $this->createManifest();
        $backupPath = $this->zipBackup($path);
        $this->cleanup();

        return $backupPath;
    }

    public function backupFileExists(string $path): bool
    {
        return file_exists($path.'/'.$this->backupFileName.'.'.self::BACKUP_FILE_EXTENSION);
    }

    public function restore(string $path): void
    {
        (new BackupService)->restore($path);
    }

    private function prepareBackup(): void
    {
        File::ensureDirectoryExists(storage_path(self::TEMP_BACKUP_PATH));
        File::delete(storage_path(self::TEMP_BACKUP_PATH.'/'.self::SQL_FILENAME));
        File::delete(storage_path(self::TEMP_BACKUP_PATH.'/'.self::SNAPSHOT_FILENAME));
        File::delete(storage_path(self::TEMP_BACKUP_PATH.'/'.self::MANIFEST_FILENAME));
    }

    private function cleanup(): void
    {
        File::deleteDirectory(storage_path(self::TEMP_BACKUP_PATH));
    }

    private function makeDbDump(): void
    {
        $snapshotPath = storage_path(self::TEMP_BACKUP_PATH.'/'.self::SNAPSHOT_FILENAME);
        $dumpPath = storage_path(self::TEMP_BACKUP_PATH.'/'.self::SQL_FILENAME);

        $this->createSnapshot($snapshotPath);

        try {
            $this->writeSqlDumpFromSnapshot($snapshotPath, $dumpPath);
        } finally {
            File::delete($snapshotPath);
        }
    }

    private function createSnapshot(string $snapshotPath): void
    {
        try {
            DB::statement('PRAGMA wal_checkpoint(FULL);');
        } catch (\Throwable) {
        }

        $escapedPath = str_replace("'", "''", $snapshotPath);

        try {
            DB::statement("VACUUM INTO '{$escapedPath}'");
        } catch (\Throwable) {
            throw new \Exception(__('app.backup could not be created.'));
        }

        if (! File::isFile($snapshotPath)) {
            throw new \Exception(__('app.backup could not be created.'));
        }
    }

    private function writeSqlDumpFromSnapshot(string $snapshotPath, string $dumpPath): void
    {
        $handle = fopen($dumpPath, 'wb');

        if (! $handle) {
            throw new \Exception(__('app.backup could not be created.'));
        }

        $pdo = $this->openSqliteConnection($snapshotPath);

        try {
            fwrite($handle, "PRAGMA foreign_keys=OFF;\n");
            fwrite($handle, "BEGIN TRANSACTION;\n");

            $tables = $this->schemaItems($pdo, 'table', "name NOT LIKE 'sqlite_%'");
            foreach ($tables as $table) {
                $tableName = (string) ($table['name'] ?? '');
                $tableSql = is_string($table['sql'] ?? null) ? $table['sql'] : '';

                if ($tableName === '' || $tableSql === '') {
                    continue;
                }

                fwrite($handle, 'DROP TABLE IF EXISTS '.$this->quoteIdentifier($tableName).";\n");
                fwrite($handle, $tableSql.";\n");
                $this->writeTableRows($pdo, $handle, $tableName);
            }

            $this->writeSchemaStatements($pdo, $handle, 'index', "name NOT LIKE 'sqlite_%' AND sql IS NOT NULL");
            $this->writeSchemaStatements($pdo, $handle, 'trigger', 'sql IS NOT NULL');
            $this->writeSchemaStatements($pdo, $handle, 'view', 'sql IS NOT NULL');

            fwrite($handle, "COMMIT;\n");
        } finally {
            fclose($handle);
        }
    }

    private function writeTableRows(PDO $pdo, mixed $handle, string $tableName): void
    {
        if ($tableName === 'failed_jobs') {
            return;
        }

        $columns = $this->tableColumns($pdo, $tableName);
        if ($columns === []) {
            return;
        }

        $quotedTable = $this->quoteIdentifier($tableName);
        $columnNames = array_keys($columns);
        $quotedColumns = implode(', ', array_map($this->quoteIdentifier(...), $columnNames));
        $rowQuery = 'SELECT '.$quotedColumns.' FROM '.$quotedTable;

        $rows = $pdo->query($rowQuery);
        if ($rows === false) {
            return;
        }

        while (($row = $rows->fetch(PDO::FETCH_ASSOC)) !== false) {
            $values = [];
            foreach ($columnNames as $columnName) {
                $values[] = $this->formatSqlValue(
                    $row[$columnName] ?? null,
                    $columns[$columnName]
                );
            }

            fwrite(
                $handle,
                'INSERT INTO '.$quotedTable.' ('.$quotedColumns.') VALUES ('.implode(', ', $values).");\n"
            );
        }
    }

    /**
     * @return array<string, string>
     */
    private function tableColumns(PDO $pdo, string $tableName): array
    {
        $query = 'PRAGMA table_info('.$this->quoteIdentifier($tableName).')';
        $result = $pdo->query($query);

        if ($result === false) {
            return [];
        }

        $columns = [];

        while (($column = $result->fetch(PDO::FETCH_ASSOC)) !== false) {
            $name = (string) ($column['name'] ?? '');

            if ($name === '') {
                continue;
            }

            $columns[$name] = strtoupper((string) ($column['type'] ?? ''));
        }

        return $columns;
    }

    private function formatSqlValue(mixed $value, string $columnType): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if ($this->isBlobColumn($columnType)) {
            return "X'".bin2hex((string) $value)."'";
        }

        if ($this->isNumericColumn($columnType) && is_numeric($value)) {
            return (string) $value;
        }

        if (is_int($value)) {
            return (string) $value;
        }

        if (is_float($value)) {
            if (is_infinite($value) || is_nan($value)) {
                return 'NULL';
            }

            return (string) $value;
        }

        $escapedValue = str_replace("'", "''", (string) $value);

        return "'{$escapedValue}'";
    }

    private function isBlobColumn(string $columnType): bool
    {
        return str_contains($columnType, 'BLOB');
    }

    private function isNumericColumn(string $columnType): bool
    {
        return str_contains($columnType, 'INT')
            || str_contains($columnType, 'REAL')
            || str_contains($columnType, 'FLOA')
            || str_contains($columnType, 'DOUB')
            || str_contains($columnType, 'NUM');
    }

    private function writeSchemaStatements(PDO $pdo, mixed $handle, string $type, string $where): void
    {
        $items = $this->schemaItems($pdo, $type, $where);

        foreach ($items as $item) {
            $sql = is_string($item['sql'] ?? null) ? $item['sql'] : '';

            if ($sql === '') {
                continue;
            }

            fwrite($handle, $sql.";\n");
        }
    }

    /**
     * @return array<int, array{name: string, sql: string|null}>
     */
    private function schemaItems(PDO $pdo, string $type, string $where): array
    {
        $query = 'SELECT name, sql FROM sqlite_master WHERE type = :type AND '.$where.' ORDER BY name';

        $statement = $pdo->prepare($query);
        if (! $statement) {
            return [];
        }

        $statement->bindValue(':type', $type);
        $statement->execute();

        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        return is_array($result) ? $result : [];
    }

    private function openSqliteConnection(string $databasePath): PDO
    {
        return new PDO(
            'sqlite:'.$databasePath,
            null,
            null,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }

    private function quoteIdentifier(string $value): string
    {
        return '"'.str_replace('"', '""', $value).'"';
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

    /**
     * @return array<string, array{sha256: string, size: int}>
     */
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

    /**
     * @return array<int, string>
     */
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
}
