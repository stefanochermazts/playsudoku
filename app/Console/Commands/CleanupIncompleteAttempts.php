<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ChallengeAttempt;
use Carbon\Carbon;

class CleanupIncompleteAttempts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'challenge:cleanup {--days=7 : Numero di giorni dopo i quali rimuovere tentativi incompleti} {--dry-run : Mostra solo cosa verrebbe rimosso senza effettuare le operazioni}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rimuove tentativi incompleti oltre la soglia e compatta i log delle mosse';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');
        $cutoffDate = Carbon::now()->subDays($days);
        
        $this->info("🧹 Avvio cleanup tentativi incompleti");
        $this->info("📅 Soglia: tentativi incompleti da più di {$days} giorni (prima del {$cutoffDate->format('d/m/Y H:i')})");
        
        if ($dryRun) {
            $this->warn("🔍 MODALITÀ DRY-RUN: nessuna operazione verrà effettuata");
        }

        // 1. Cleanup tentativi incompleti (non completati e vecchi)
        $incompleteQuery = ChallengeAttempt::whereNull('completed_at')
            ->where('started_at', '<', $cutoffDate);
            
        $incompleteCount = $incompleteQuery->count();
        
        if ($incompleteCount > 0) {
            $this->info("🗑️ Trovati {$incompleteCount} tentativi incompleti da rimuovere");
            
            if (!$dryRun) {
                $deleted = $incompleteQuery->delete();
                $this->info("✅ Rimossi {$deleted} tentativi incompleti");
            } else {
                $this->info("🔍 Verrebbero rimossi {$incompleteCount} tentativi incompleti");
            }
        } else {
            $this->info("✨ Nessun tentativo incompleto da rimuovere");
        }

        // 2. Compatta log mosse per tentativi molto vecchi (> 30 giorni)
        $oldCompletedQuery = ChallengeAttempt::whereNotNull('completed_at')
            ->where('completed_at', '<', Carbon::now()->subDays(30))
            ->whereHas('moves');
            
        $oldCompletedCount = $oldCompletedQuery->count();
        
        if ($oldCompletedCount > 0) {
            $this->info("📦 Trovati {$oldCompletedCount} tentativi completati con log mosse da compattare");
            
            $totalMovesDeleted = 0;
            $totalMovesKept = 0;
            
            if (!$dryRun) {
                $oldCompleted = $oldCompletedQuery->with('moves')->get();
                
                foreach ($oldCompleted as $attempt) {
                    $allMoves = $attempt->moves()->orderBy('move_index')->get();
                    $movesCount = $allMoves->count();
                    
                    if ($movesCount > 10) {
                        // Mantieni solo le prime 5 e le ultime 5 mosse per il debug
                        $firstMoves = $allMoves->take(5);
                        $lastMoves = $allMoves->slice(-5);
                        $toKeep = $firstMoves->merge($lastMoves);
                        
                        // Elimina le mosse intermedie
                        $toDeleteIds = $allMoves->reject(function ($move) use ($toKeep) {
                            return $toKeep->contains('id', $move->id);
                        })->pluck('id');
                        
                        $deletedCount = $attempt->moves()->whereIn('id', $toDeleteIds)->delete();
                        $totalMovesDeleted += $deletedCount;
                        $totalMovesKept += $toKeep->count();
                    }
                }
                
                $this->info("✅ Compattati log mosse per {$oldCompletedCount} tentativi");
                $this->info("🗑️ Mosse eliminate: {$totalMovesDeleted}");
                $this->info("💾 Mosse conservate: {$totalMovesKept}");
            } else {
                $this->info("🔍 Verrebbero compattati log mosse per {$oldCompletedCount} tentativi");
            }
        } else {
            $this->info("✨ Nessun log mosse da compattare");
        }

        // 3. Rimuovi tentativi flagged molto vecchi se già revisionati (> 90 giorni)
        $oldFlaggedQuery = ChallengeAttempt::where('flagged_for_review', true)
            ->whereNotNull('reviewed_at')
            ->where('reviewed_at', '<', Carbon::now()->subDays(90));
            
        $oldFlaggedCount = $oldFlaggedQuery->count();
        
        if ($oldFlaggedCount > 0) {
            $this->info("🚩 Trovati {$oldFlaggedCount} tentativi flagged revisionati da più di 90 giorni");
            
            if (!$dryRun) {
                $deleted = $oldFlaggedQuery->delete();
                $this->info("✅ Rimossi {$deleted} tentativi flagged obsoleti");
            } else {
                $this->info("🔍 Verrebbero rimossi {$oldFlaggedCount} tentativi flagged obsoleti");
            }
        } else {
            $this->info("✨ Nessun tentativo flagged obsoleto da rimuovere");
        }

        // 4. Statistiche finali
        $this->info("");
        $this->info("📊 Statistiche attuali database:");
        $this->info("🎯 Tentativi totali: " . ChallengeAttempt::count());
        $this->info("✅ Tentativi completati: " . ChallengeAttempt::whereNotNull('completed_at')->count());
        $this->info("⏳ Tentativi in corso: " . ChallengeAttempt::whereNull('completed_at')->count());
        $this->info("🚩 Tentativi flagged: " . ChallengeAttempt::where('flagged_for_review', true)->count());
        
        if ($dryRun) {
            $this->warn("");
            $this->warn("🔍 Per eseguire effettivamente il cleanup, riesegui il comando senza --dry-run");
        }
        
        return 0;
    }
}
