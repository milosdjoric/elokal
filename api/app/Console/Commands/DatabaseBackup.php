<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DatabaseBackup extends Command
{
    protected $signature = 'db:backup {--type=daily : Tip backupa (daily, weekly, monthly, pre-migration)}';
    protected $description = 'Kreira backup baze podataka';

    public function handle(): int
    {
        $type = $this->option('type');
        $date = now()->format('Y-m-d_His');
        $filename = "backup_{$type}_{$date}.sql";
        $path = "backups/{$type}/{$filename}";

        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if ($connection === 'sqlite') {
            // SQLite: kopiraj fajl
            $dbPath = $config['database'];
            if (! file_exists($dbPath)) {
                $this->error("Baza podataka ne postoji: {$dbPath}");
                return 1;
            }
            Storage::disk('local')->put(
                str_replace('.sql', '.sqlite', $path),
                file_get_contents($dbPath),
            );
            $this->info("SQLite backup kreiran: {$path}");
        } else {
            // MySQL: mysqldump
            $host = $config['host'];
            $port = $config['port'];
            $database = $config['database'];
            $username = $config['username'];
            $password = $config['password'];

            $storagePath = storage_path("app/{$path}");
            $dir = dirname($storagePath);
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $command = sprintf(
                'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s 2>&1',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($storagePath),
            );

            exec($command, $output, $exitCode);

            if ($exitCode !== 0) {
                $this->error('mysqldump greška: ' . implode("\n", $output));
                return 1;
            }

            $this->info("MySQL backup kreiran: {$path}");
        }

        // Rotacija
        $this->rotate($type);

        return 0;
    }

    private function rotate(string $type): void
    {
        $maxFiles = match ($type) {
            'daily' => 7,
            'weekly' => 4,
            'monthly' => 3,
            default => 10,
        };

        $directory = "backups/{$type}";
        $disk = Storage::disk('local');

        if (! $disk->exists($directory)) return;

        $files = collect($disk->files($directory))->sort()->values();

        if ($files->count() > $maxFiles) {
            $toDelete = $files->slice(0, $files->count() - $maxFiles);
            foreach ($toDelete as $file) {
                $disk->delete($file);
                $this->line("Rotacija: obrisan {$file}");
            }
        }
    }
}
