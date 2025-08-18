<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ChallengeService;
use Carbon\Carbon;

class GenerateWeeklyChallenge extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'challenge:generate-weekly {--force : Force generation even if challenge already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera la sfida settimanale ogni lunedì alle 00:00';

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
        $today = Carbon::today();
        
        // Verifica che sia lunedì (o forza con --force)
        if ($today->dayOfWeek !== Carbon::MONDAY && !$this->option('force')) {
            $this->info("📅 Le sfide settimanali si generano solo il lunedì. Oggi è " . $today->locale('it')->dayName);
            return 0;
        }
        
        // Trova l'inizio e la fine della settimana
        $weekStart = $today->startOfWeek(Carbon::MONDAY);
        $weekEnd = $today->copy()->endOfWeek(Carbon::SUNDAY);
        
        // Il controllo dell'esistenza è gestito dal ChallengeService
        if ($this->option('force')) {
            $this->warn("🔄 Force mode attivo - il servizio gestirà eventuali duplicati");
        }

        // Genera seed deterministico basato sulla settimana
        $weekNumber = $weekStart->weekOfYear;
        $year = $weekStart->year;
        $seed = ($year * 100) + $weekNumber; // Es: 202501, 202502, etc.
        
        // Le sfide settimanali sono sempre Expert o Crazy per maggiore difficoltà
        $weekOfMonth = ceil($weekStart->day / 7);
        $difficulty = ($weekOfMonth % 2 === 1) ? 'expert' : 'crazy';

        $this->info("🎯 Generazione sfida settimanale:");
        $this->info("📅 Settimana: {$weekStart->format('d/m')} - {$weekEnd->format('d/m/Y')} (Settimana #{$weekNumber})");
        $this->info("🎲 Seed: {$seed}");
        $this->info("⚡ Difficoltà: {$difficulty}");

        try {
            $this->info("🔄 Generazione sfida settimanale in corso...");
            
            $challenge = $this->challengeService->createWeeklyChallenge($difficulty, $weekStart);
            
            $this->info("✅ Sfida settimanale creata con successo!");
            $this->info("🆔 Challenge ID: {$challenge->id}");
            $this->info("🧩 Puzzle ID: {$challenge->puzzle_id}");
            $this->info("📅 Periodo: {$challenge->starts_at->format('d/m/Y H:i')} - {$challenge->ends_at->format('d/m/Y H:i')}");
            $this->info("⚡ Stato: {$challenge->status}");
            $this->info("📆 Durata totale: 7 giorni");
            
            // Notifica opzionale agli utenti (se attivata)
            if (config('sudoku.notifications.new_challenges', false)) {
                $this->info("📧 Invio notifiche utenti...");
                $this->call('challenge:notify-users', ['challenge' => $challenge->id, '--type' => 'weekly']);
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Errore durante la generazione: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            return 1;
        }
    }
    
    /**
     * Stima durata in minuti basata sulla difficoltà
     */
    private function getEstimatedDuration(string $difficulty): int
    {
        return match($difficulty) {
            'easy' => 8,
            'medium' => 15,
            'hard' => 25,
            'expert' => 40,
            'crazy' => 60,
            default => 15
        };
    }
}
