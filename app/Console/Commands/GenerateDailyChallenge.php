<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ChallengeService;
use App\Models\Challenge;
use Carbon\Carbon;

class GenerateDailyChallenge extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'challenge:generate-daily {--force : Force generation even if challenge already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera la sfida giornaliera alle 00:00';

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
        
        // Se --force, elimina sfida esistente
        if ($this->option('force')) {
            $existingChallenge = Challenge::where('type', 'daily')
                ->whereDate('starts_at', $today)
                ->first();
                
            if ($existingChallenge) {
                $this->warn("🔄 Sovrascrivo sfida esistente (--force attivo)");
                $existingChallenge->delete();
            }
        }

        // Difficoltà ciclica: Easy (lunedì), Normal (martedì-mercoledì), Hard (giovedì-venerdì), Expert (sabato-domenica)
        $dayOfWeek = $today->dayOfWeek; // 0=domenica, 1=lunedì, ...
        $difficulty = match($dayOfWeek) {
            1 => 'easy',        // Lunedì
            2, 3 => 'normal',   // Martedì-Mercoledì  
            4, 5 => 'hard',     // Giovedì-Venerdì
            6, 0 => 'expert',   // Sabato-Domenica
            default => 'normal'
        };

        $this->info("🎯 Generazione sfida giornaliera:");
        $this->info("📅 Data: {$today->format('d/m/Y')} (" . $today->locale('it')->dayName . ")");
        $this->info("⚡ Difficoltà: {$difficulty}");

        try {
            // Usa il ChallengeService per creare la sfida
            $challenge = $this->challengeService->createDailyChallenge($difficulty, $today);
            
            $this->info("✅ Sfida giornaliera creata con successo!");
            $this->info("🆔 Challenge ID: {$challenge->id}");
            $this->info("🧩 Puzzle ID: {$challenge->puzzle_id}");
            $this->info("⏱️ Periodo: {$challenge->starts_at->format('d/m/Y H:i')} - {$challenge->ends_at->format('d/m/Y H:i')}");
            
            // Notifica opzionale agli utenti (se attivata)
            if (config('sudoku.notifications.new_challenges', false)) {
                $this->info("📧 Invio notifiche utenti...");
                $this->call('challenge:notify-users', ['challenge' => $challenge->id]);
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Errore durante la generazione: " . $e->getMessage());
            return 1;
        }
    }
}
