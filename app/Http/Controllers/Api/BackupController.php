<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BackupController extends Controller
{
    private string $backupDir;

    public function __construct()
    {
        $this->backupDir = storage_path('app/backups');
        if (! is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    public function status(): JsonResponse
    {
        return response()->json([
            'backups' => $this->listBackups(),
            'tables' => $this->tableList(),
            'storage' => $this->storageInfo(),
        ]);
    }

    public function create(): JsonResponse
    {
        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");
        $username = config("database.connections.{$connection}.username");
        $password = config("database.connections.{$connection}.password");
        $host = config("database.connections.{$connection}.host");
        $port = config("database.connections.{$connection}.port", 3306);

        $fileName = 'backup-' . $database . '-' . now()->format('Y-m-d-His') . '.sql';
        $filePath = $this->backupDir . '/' . $fileName;

        $cmd = implode(' ', array_filter([
            'mysqldump',
            '--host=' . $host,
            '--port=' . $port,
            '--user=' . $username,
            $password !== '' ? '--password=' . $password : null,
            '--single-transaction',
            '--routines',
            '--triggers',
            $database,
        ]));

        $output = [];
        $returnCode = 0;
        exec($cmd . ' > ' . escapeshellarg($filePath), $output, $returnCode);

        if ($returnCode !== 0 || ! file_exists($filePath) || filesize($filePath) === 0) {
            $fallback = $this->createPhpDump($filePath);
            if (! $fallback) {
                return response()->json(['message' => 'No se pudo generar el backup.'], 500);
            }
        }

        return response()->json([
            'message' => 'Backup generado correctamente.',
            'file' => $fileName,
        ], 201);
    }

    public function download(Request $request, string $file)
    {
        abort_unless($this->isValidName($file), 404, 'Archivo no valido.');
        $path = $this->backupDir . '/' . $file;
        abort_unless(file_exists($path), 404, 'El backup no existe.');

        return response()->download($path, $file, ['Content-Type' => 'application/sql']);
    }

    public function restore(Request $request, string $file): JsonResponse
    {
        abort_unless($this->isValidName($file), 404, 'Archivo no valido.');
        $path = $this->backupDir . '/' . $file;
        if (! file_exists($path)) {
            return response()->json(['message' => 'El backup no existe.'], 404);
        }

        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");
        $username = config("database.connections.{$connection}.username");
        $password = config("database.connections.{$connection}.password");
        $host = config("database.connections.{$connection}.host");
        $port = config("database.connections.{$connection}.port", 3306);

        $cmd = implode(' ', array_filter([
            'mysql',
            '--host=' . $host,
            '--port=' . $port,
            '--user=' . $username,
            $password !== '' ? '--password=' . $password : null,
            $database,
        ]));

        exec($cmd . ' < ' . escapeshellarg($path), $output, $returnCode);

        if ($returnCode !== 0) {
            if (! $this->restorePhp($path)) {
                return response()->json(['message' => 'No se pudo restaurar la base de datos.'], 500);
            }
        }

        return response()->json([
            'message' => "Base de datos restaurada desde '{$file}'.",
            'restored_from' => $file,
        ]);
    }

    public function destroy(string $file): JsonResponse
    {
        abort_unless($this->isValidName($file), 404, 'Archivo no valido.');
        $path = $this->backupDir . '/' . $file;
        if (! file_exists($path)) {
            return response()->json(['message' => 'El backup no existe.'], 404);
        }

        @unlink($path);

        return response()->json(['message' => "Backup '{$file}' eliminado."]);
    }

    private function createPhpDump(string $filePath): bool
    {
        try {
            $tables = $this->tableList();

            $output = "-- SmartZone Backup\nSET FOREIGN_KEY_CHECKS = 0;\n\n";

            foreach ($tables as $tableName) {
                $output .= "DROP TABLE IF EXISTS `{$tableName}`;\n";

                $create = DB::select("SHOW CREATE TABLE `{$tableName}`");
                foreach ($create as $row) {
                    $output .= $row->{'Create Table'} . ";\n\n";
                }

                $rows = DB::table($tableName)->get();
                if ($rows->count() > 0) {
                    foreach ($rows->chunk(500) as $chunk) {
                        $output .= "INSERT INTO `{$tableName}` VALUES\n";
                        $values = [];
                        foreach ($chunk as $row) {
                            $cols = collect((array) $row)->map(function ($value) {
                                if ($value === null) {
                                    return 'NULL';
                                }
                                if (is_numeric($value)) {
                                    return (string) $value;
                                }
                                return "'" . str_replace("'", "''", (string) $value) . "'";
                            })->implode(',');
                            $values[] = '(' . $cols . ')';
                        }
                        $output .= implode(",\n", $values) . ";\n\n";
                    }
                }
            }

            $output .= "SET FOREIGN_KEY_CHECKS = 1;\n";
            file_put_contents($filePath, $output);

            return filesize($filePath) > 0;
        } catch (\Throwable) {
            return false;
        }
    }

    private function restorePhp(string $sqlPath): bool
    {
        try {
            if (! file_exists($sqlPath)) {
                return false;
            }
            $sql = file_get_contents($sqlPath);
            if ($sql === false) {
                return false;
            }
            DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
            DB::unprepared($sql);
            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function listBackups(): array
    {
        $files = glob($this->backupDir . '/*.sql') ?: [];
        rsort($files);

        return collect($files)->map(function ($path) {
            return [
                'filename' => basename($path),
                'size' => $this->humanFilesize(filesize($path)),
                'bytes' => filesize($path),
                'modified' => (new \Carbon\CarbonImmutable())->createFromTimestamp(filemtime($path))->toDateTimeString(),
            ];
        })->values()->all();
    }

    private function tableList(): array
    {
        if (config('database.default') === 'sqlite') {
            return collect(DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'"))
                ->pluck('name')
                ->all();
        }

        $tables = DB::select('SHOW TABLES');
        $tableKey = 'Tables_in_' . config('database.connections.' . config('database.default') . '.database');

        return collect($tables)->pluck($tableKey)->all();
    }

    private function storageInfo(): array
    {
        $totalBytes = 0;
        foreach (glob($this->backupDir . '/*.sql') ?: [] as $file) {
            $totalBytes += filesize($file);
        }

        return [
            'count' => count($this->listBackups()),
            'size' => $this->humanFilesize($totalBytes),
        ];
    }

    private function humanFilesize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    private function isValidName(string $name): bool
    {
        return preg_match('/^backup-[\w-]+\.sql$/', $name) === 1;
    }
}