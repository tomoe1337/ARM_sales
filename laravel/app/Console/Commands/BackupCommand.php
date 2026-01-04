<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BackupCommand extends Command
{
    protected $signature = 'backup:create';
    
    protected $description = 'Создание резервной копии БД и файлов';

    public function handle(): int
    {
        $this->info('Создание резервной копии...');
        
        $backupDir = storage_path('app/backups');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupName = "backup_{$timestamp}";
        
        try {
            // 1. Бэкап БД
            $this->info('Бэкап базы данных...');
            $dbFile = "{$backupDir}/{$backupName}_db.sql";
            $this->backupDatabase($dbFile);
            $this->info('  ✓ База данных сохранена');
            
            // 2. Бэкап файлов
            $this->info('Бэкап файлов...');
            $filesArchive = "{$backupDir}/{$backupName}_files.tar.gz";
            $this->backupFiles($filesArchive);
            $this->info('  ✓ Файлы сохранены');
            
            // 3. Очистка старых бэкапов (старше 7 дней)
            $deletedCount = $this->cleanOldBackups($backupDir, 7);
            if ($deletedCount > 0) {
                $this->info("  ✓ Удалено старых бэкапов: {$deletedCount}");
            }
            
            $this->info("✓ Бэкап создан: {$backupName}");
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Ошибка создания бэкапа: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
    
    private function backupDatabase(string $outputFile): void
    {
        $db = config('database.connections.pgsql');
        
        $command = sprintf(
            'PGPASSWORD=%s pg_dump -h %s -p %s -U %s -d %s --no-owner --no-acl 2>&1',
            escapeshellarg($db['password']),
            escapeshellarg($db['host']),
            escapeshellarg($db['port']),
            escapeshellarg($db['username']),
            escapeshellarg($db['database'])
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            $error = implode("\n", $output);
            throw new \RuntimeException('Database backup failed: ' . $error);
        }
        
        // Записываем вывод в файл
        file_put_contents($outputFile, implode("\n", $output));
    }
    
    private function backupFiles(string $outputFile): void
    {
        $storagePath = storage_path('app');
        
        $command = sprintf(
            'tar -czf %s -C %s private public 2>&1',
            escapeshellarg($outputFile),
            escapeshellarg($storagePath)
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \RuntimeException('Files backup failed: ' . implode("\n", $output));
        }
    }
    
    private function cleanOldBackups(string $backupDir, int $days): int
    {
        $files = glob("{$backupDir}/backup_*");
        $cutoff = now()->subDays($days)->timestamp;
        $deletedCount = 0;
        
        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $cutoff) {
                unlink($file);
                $deletedCount++;
            }
        }
        
        return $deletedCount;
    }
}

