<?php
declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AuditService;

class LogBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:log-backup 
                            {--type=manual : Tipo di backup (manual, database, full)} 
                            {--file= : Nome file di backup}
                            {--size= : Dimensione backup in bytes}
                            {--success=true : Se il backup è riuscito}
                            {--manifest= : Path al file manifest JSON}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Registra eventi di backup nell\'audit trail per tracciabilità e compliance';

    public function __construct(
        private readonly AuditService $auditService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = $this->option('type');
        $file = $this->option('file');
        $size = $this->option('size');
        $success = filter_var($this->option('success'), FILTER_VALIDATE_BOOLEAN);
        $manifestPath = $this->option('manifest');

        $metadata = [
            'backup_type' => $type,
            'timestamp' => now()->toISOString(),
        ];

        if ($file) {
            $metadata['backup_file'] = $file;
        }

        if ($size) {
            $metadata['backup_size_bytes'] = (int) $size;
            $metadata['backup_size_mb'] = round((int) $size / 1024 / 1024, 2);
        }

        // Se abbiamo un manifest, leggiamo i dettagli
        if ($manifestPath && file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
            if ($manifest) {
                $metadata['manifest'] = $manifest;
                $metadata['files_included'] = array_keys($manifest['files'] ?? []);
                $metadata['total_size_bytes'] = array_sum($manifest['sizes'] ?? []);
            }
        }

        // Log il backup nell'audit trail
        $this->auditService->logDatabaseBackup($success, $metadata);

        if ($success) {
            $this->info("✅ Backup {$type} registrato con successo nell'audit trail");
            if ($file) {
                $this->line("📁 File: {$file}");
            }
            if ($size) {
                $sizeMB = round((int) $size / 1024 / 1024, 2);
                $this->line("💾 Dimensione: {$sizeMB} MB");
            }
        } else {
            $this->error("❌ Backup {$type} fallito - registrato nell'audit trail");
        }

        return 0;
    }
}