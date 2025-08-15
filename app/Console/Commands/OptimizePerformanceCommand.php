<?php

namespace App\Console\Commands;

use App\Models\Challenge;
use App\Models\ChallengeAttempt;
use App\Models\Puzzle;
use App\Models\User;
use App\Services\CacheService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

class OptimizePerformanceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'performance:optimize 
                            {--cache-only : Solo ottimizzazioni cache}
                            {--db-only : Solo ottimizzazioni database}
                            {--force : Forza ottimizzazioni anche in produzione}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ottimizza performance del sistema PlaySudoku (cache, DB, indici)';

    public function __construct(
        private CacheService $cacheService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Avvio ottimizzazioni performance PlaySudoku');
        
        $cacheOnly = $this->option('cache-only');
        $dbOnly = $this->option('db-only');
        
        if (!$cacheOnly) {
            $this->optimizeDatabase();
        }
        
        if (!$dbOnly) {
            $this->optimizeCache();
        }
        
        $this->optimizeLaravel();
        $this->displayStats();
        
        $this->info('✅ Ottimizzazioni completate!');
        return 0;
    }

    /**
     * Ottimizzazioni database
     */
    private function optimizeDatabase(): void
    {
        $this->info('🗄️ Ottimizzazione database...');
        
        // Verifica e crea indici mancanti
        $this->createMissingIndexes();
        
        // Analizza e ottimizza tabelle PostgreSQL
        $this->analyzeDatabase();
        
        // Pulizia dati obsoleti
        $this->cleanupOldData();
        
        $this->line('   ✓ Database ottimizzato');
    }

    /**
     * Crea indici mancanti per performance
     */
    private function createMissingIndexes(): void
    {
        $indexes = [
            // Indici per leaderboard veloci
            [
                'table' => 'challenge_attempts',
                'name' => 'idx_leaderboard_optimized',
                'columns' => ['challenge_id', 'valid', 'completed_at', 'duration_ms', 'errors_count'],
                'where' => 'WHERE valid = true AND completed_at IS NOT NULL'
            ],
            
            // Indice per statistiche utente
            [
                'table' => 'challenge_attempts',
                'name' => 'idx_user_performance',
                'columns' => ['user_id', 'completed_at', 'valid', 'duration_ms'],
                'where' => 'WHERE completed_at IS NOT NULL'
            ],
            
            // Indice per analisi anomalie
            [
                'table' => 'challenge_attempts',
                'name' => 'idx_anomaly_detection',
                'columns' => ['challenge_id', 'flagged_for_review', 'move_validation_passed'],
                'where' => null
            ],
            
            // Indice per sfide attive
            [
                'table' => 'challenges',
                'name' => 'idx_active_challenges',
                'columns' => ['status', 'type', 'starts_at', 'ends_at'],
                'where' => "WHERE status = 'active'"
            ]
        ];
        
        foreach ($indexes as $index) {
            $this->createIndexIfNotExists($index);
        }
    }

    /**
     * Crea indice se non esiste
     */
    private function createIndexIfNotExists(array $indexConfig): void
    {
        try {
            $table = $indexConfig['table'];
            $name = $indexConfig['name'];
            $columns = implode(', ', $indexConfig['columns']);
            $where = $indexConfig['where'] ?? '';
            
            // Verifica se indice esiste già
            $exists = DB::select("
                SELECT indexname FROM pg_indexes 
                WHERE tablename = ? AND indexname = ?
            ", [$table, $name]);
            
            if (empty($exists)) {
                $sql = "CREATE INDEX CONCURRENTLY {$name} ON {$table} ({$columns}) {$where}";
                DB::statement($sql);
                $this->line("   ✓ Creato indice: {$name}");
            } else {
                $this->line("   - Indice già presente: {$name}");
            }
            
        } catch (\Exception $e) {
            $this->warn("   ⚠️ Errore creazione indice {$indexConfig['name']}: " . $e->getMessage());
        }
    }

    /**
     * Analizza database PostgreSQL
     */
    private function analyzeDatabase(): void
    {
        try {
            // ANALYZE per aggiornare statistiche query planner
            $tables = ['challenge_attempts', 'challenges', 'puzzles', 'users', 'user_profiles'];
            
            foreach ($tables as $table) {
                DB::statement("ANALYZE {$table}");
            }
            
            $this->line('   ✓ Statistiche database aggiornate');
            
        } catch (\Exception $e) {
            $this->warn('   ⚠️ Errore analisi database: ' . $e->getMessage());
        }
    }

    /**
     * Pulizia dati obsoleti
     */
    private function cleanupOldData(): void
    {
        try {
            // Rimuovi attempt moves vecchi di più di 3 mesi
            $deletedMoves = DB::table('attempt_moves')
                ->join('challenge_attempts', 'attempt_moves.attempt_id', '=', 'challenge_attempts.id')
                ->where('challenge_attempts.created_at', '<', now()->subMonths(3))
                ->delete();
            
            if ($deletedMoves > 0) {
                $this->line("   ✓ Rimossi {$deletedMoves} movimenti obsoleti");
            }
            
            // Vacuum per PostgreSQL se necessario
            if ($this->option('force')) {
                DB::statement('VACUUM ANALYZE challenge_attempts');
                $this->line('   ✓ VACUUM eseguito');
            }
            
        } catch (\Exception $e) {
            $this->warn('   ⚠️ Errore pulizia: ' . $e->getMessage());
        }
    }

    /**
     * Ottimizzazioni cache
     */
    private function optimizeCache(): void
    {
        $this->info('🗃️ Ottimizzazione cache...');
        
        // Cleanup cache esistente
        $this->cacheService->cleanup();
        
        // Pre-warm cache per dati frequenti
        $this->prewarmCache();
        
        $this->line('   ✓ Cache ottimizzata');
    }

    /**
     * Pre-carica cache per query frequenti
     */
    private function prewarmCache(): void
    {
        try {
            // Pre-load leaderboard globali
            $this->cacheService->getGlobalLeaderboard('daily', 50);
            $this->cacheService->getGlobalLeaderboard('weekly', 50);
            $this->cacheService->getGlobalLeaderboard('all', 100);
            
            // Pre-load statistiche giornaliere
            $this->cacheService->getDailySystemStats();
            
            $this->line('   ✓ Cache pre-caricata');
            
        } catch (\Exception $e) {
            $this->warn('   ⚠️ Errore pre-caricamento cache: ' . $e->getMessage());
        }
    }

    /**
     * Ottimizzazioni Laravel
     */
    private function optimizeLaravel(): void
    {
        $this->info('⚡ Ottimizzazione Laravel...');
        
        // Config cache
        Artisan::call('config:cache');
        $this->line('   ✓ Config cached');
        
        // Route cache
        Artisan::call('route:cache');
        $this->line('   ✓ Routes cached');
        
        // View cache
        Artisan::call('view:cache');
        $this->line('   ✓ Views cached');
        
        // Event cache
        Artisan::call('event:cache');
        $this->line('   ✓ Events cached');
    }

    /**
     * Mostra statistiche performance
     */
    private function displayStats(): void
    {
        $this->info('📊 Statistiche performance:');
        
        try {
            // Stats cache
            $cacheStats = $this->cacheService->getCacheStats();
            $this->line("   Cache driver: {$cacheStats['driver']}");
            
            if (isset($cacheStats['keys_count'])) {
                $this->line("   Chiavi cache: {$cacheStats['keys_count']}");
                $this->line("   Memoria usata: {$cacheStats['memory_used']}");
            }
            
            // Stats database (safe for all DB types)
            $tableStats = [
                ['Table', 'Records'],
                ['challenge_attempts', ChallengeAttempt::count()],
                ['challenges', Challenge::count()],
                ['puzzles', Puzzle::count()],
                ['users', User::count()],
            ];
            
            $this->table(['Metric', 'Value'], $tableStats);
            
        } catch (\Exception $e) {
            $this->warn('Errore recupero statistiche: ' . $e->getMessage());
        }
    }
}
