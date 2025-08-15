<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Challenge;
use App\Models\Puzzle;
use App\Domain\Sudoku\Contracts\GeneratorInterface;
use App\Domain\Sudoku\Contracts\ValidatorInterface;
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
        
        // Controlla se esiste già una sfida per questa settimana
        $existingChallenge = Challenge::where('type', 'weekly')
            ->whereBetween('starts_at', [$weekStart, $weekEnd])
            ->first();
            
        if ($existingChallenge && !$this->option('force')) {
            $this->info("📅 Sfida settimanale per la settimana {$weekStart->format('d/m')} - {$weekEnd->format('d/m/Y')} già esistente (ID: {$existingChallenge->id})");
            return 0;
        }
        
        if ($existingChallenge && $this->option('force')) {
            $this->warn("🔄 Sovrascrivo sfida esistente (--force attivo)");
            $existingChallenge->delete();
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
            // Genera puzzle con i parametri determinati
            $generator = app(GeneratorInterface::class);
            $validator = app(ValidatorInterface::class);
            
            $this->info("🔄 Generazione puzzle in corso...");
            
            // 1. Genera griglia completa (soluzione)
            $solution = $generator->generateCompleteGrid($seed);
            
            // 2. Genera puzzle con celle rimosse (givens)
            $puzzle = $generator->generatePuzzleWithDifficulty($seed, $difficulty);
            
            // Valida il puzzle
            if (!$validator->isValid($puzzle) || !$validator->isSolvable($puzzle)) {
                throw new \Exception("Puzzle generato non valido o non risolvibile");
            }
            
            // Crea record puzzle
            $puzzleModel = Puzzle::create([
                'givens' => $puzzle->toArray(),
                'solution' => $solution->toArray(),
                'difficulty' => $difficulty,
                'seed' => $seed,
                'estimated_duration' => $this->getEstimatedDuration($difficulty)
            ]);
            
            // Crea la sfida
            $challenge = Challenge::create([
                'type' => 'weekly',
                'title' => "Sfida Settimanale - Settimana " . $weekStart->locale('it')->isoFormat('W [del] YYYY'),
                'description' => "La sfida della settimana ({$difficulty}). Hai 7 giorni per completare questo Sudoku impegnativo!",
                'puzzle_id' => $puzzleModel->id,
                'difficulty' => $difficulty,
                'starts_at' => $weekStart->startOfDay(),
                'ends_at' => $weekEnd->endOfDay(),
                'is_active' => true
            ]);
            
            $this->info("✅ Sfida settimanale creata con successo!");
            $this->info("🆔 Challenge ID: {$challenge->id}");
            $this->info("🧩 Puzzle ID: {$puzzleModel->id}");
            $this->info("⏱️ Durata stimata: {$this->getEstimatedDuration($difficulty)} minuti");
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
