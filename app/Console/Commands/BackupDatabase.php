<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class BackupDatabase extends Command
{
    protected $signature = 'app:backup-database {--path= : Custom output directory}';

    protected $description = 'Create a MySQL dump backup (requires mysqldump on PATH)';

    public function handle(): int
    {
        if (config('database.default') !== 'mysql') {
            $this->warn('Backup command only supports MySQL.');

            return self::FAILURE;
        }

        $dir = $this->option('path') ?: storage_path('backups');
        File::ensureDirectoryExists($dir);

        $filename = 'db-'.now()->format('Y-m-d-His').'.sql';
        $path = $dir.DIRECTORY_SEPARATOR.$filename;

        $connection = config('database.connections.mysql');
        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s %s > %s',
            escapeshellarg($connection['host']),
            escapeshellarg((string) $connection['port']),
            escapeshellarg($connection['username']),
            escapeshellarg($connection['database']),
            escapeshellarg($path)
        );

        $env = [];
        if (! empty($connection['password'])) {
            $env['MYSQL_PWD'] = $connection['password'];
        }

        $result = Process::env($env)->timeout(300)->run($command);

        if (! $result->successful()) {
            $this->error('Backup failed. Ensure mysqldump is installed and credentials are correct.');
            $this->line($result->errorOutput());

            return self::FAILURE;
        }

        $this->info("Database backup saved to {$path}");

        return self::SUCCESS;
    }
}
