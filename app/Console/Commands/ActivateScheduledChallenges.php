<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ChallengeService;

class ActivateScheduledChallenges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'challenge:activate-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Attiva le sfide programmate che dovrebbero essere ora attive';

    public function __construct(
        private readonly ChallengeService $challengeService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("🔄 Attivazione sfide programmate...");

        try {
            // Attiva le sfide scheduled
            $activatedCount = $this->challengeService->activateScheduledChallenges();
            
            // Completa le sfide scadute
            $completedCount = $this->challengeService->completeExpiredChallenges();
            
            // Invalida cache
            $this->challengeService->clearActiveChallengesCache();
            
            if ($activatedCount > 0) {
                $this->info("✅ {$activatedCount} sfide attivate con successo");
            }
            
            if ($completedCount > 0) {
                $this->info("🏁 {$completedCount} sfide scadute completate");
            }
            
            if ($activatedCount === 0 && $completedCount === 0) {
                $this->info("📋 Nessuna sfida da aggiornare");
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Errore durante l'attivazione: " . $e->getMessage());
            return 1;
        }
    }
}
