<?php
declare(strict_types=1);

namespace App\Livewire;

use App\Models\Challenge;
use App\Models\ChallengeAttempt;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Livewire\Component;

class Dashboard extends Component
{
    public Collection $activeChallenges;
    public Collection $userAttempts;
    public array $userStats = [];

    public function mount(): void
    {
        // Inizializza collezioni vuote
        $this->activeChallenges = collect();
        $this->userAttempts = collect();
        
        $this->loadDashboardData();
    }

    public function render()
    {
        return view('livewire.dashboard');
    }

    private function loadDashboardData(): void
    {
        $user = auth()->user();

        try {
            // Carica sfide attive (daily/weekly/custom in corso)
            $this->activeChallenges = Challenge::with('puzzle')
                ->where('status', 'active')
                ->where('ends_at', '>', now())
                ->orderBy('type')
                ->orderBy('starts_at', 'desc')
                ->limit(10)
                ->get();

            // Carica i tentativi dell'utente per le sfide attive
            $challengeIds = $this->activeChallenges->pluck('id');
            if ($challengeIds->isNotEmpty()) {
                $this->userAttempts = ChallengeAttempt::where('user_id', $user->id)
                    ->whereIn('challenge_id', $challengeIds)
                    ->get()
                    ->keyBy('challenge_id');
            } else {
                $this->userAttempts = collect();
            }

            // Calcola statistiche utente
            $this->calculateUserStats();
        } catch (\Exception $e) {
            // Se le tabelle non esistono ancora, inizializza con dati vuoti
            $this->activeChallenges = collect();
            $this->userAttempts = collect();
            $this->userStats = [
                'puzzles_solved' => 0,
                'best_time' => null,
                'average_time' => null,
                'current_streak' => 0,
                'total_errors' => 0,
                'hints_used' => 0,
            ];
        }
    }

    private function calculateUserStats(): void
    {
        $user = auth()->user();

        try {
            $completedAttempts = ChallengeAttempt::where('user_id', $user->id)
                ->where('valid', true)
                ->whereNotNull('completed_at');

            $this->userStats = [
                'puzzles_solved' => $completedAttempts->count(),
                'best_time' => $completedAttempts->min('duration_ms'),
                'average_time' => $completedAttempts->avg('duration_ms'),
                'current_streak' => $this->calculateCurrentStreak(),
                'total_errors' => ChallengeAttempt::where('user_id', $user->id)->sum('errors_count'),
                'hints_used' => ChallengeAttempt::where('user_id', $user->id)->sum('hints_used'),
            ];
        } catch (\Exception $e) {
            // Fallback se le tabelle non esistono
            $this->userStats = [
                'puzzles_solved' => 0,
                'best_time' => null,
                'average_time' => null,
                'current_streak' => 0,
                'total_errors' => 0,
                'hints_used' => 0,
            ];
        }
    }

    private function calculateCurrentStreak(): int
    {
        try {
            $user = auth()->user();
            
            // Conta i giorni consecutivi con almeno un puzzle completato
            $streak = 0;
            $currentDate = now()->startOfDay();
            
            while ($currentDate->greaterThanOrEqualTo(now()->subDays(365))) {
                $hasCompletedPuzzle = ChallengeAttempt::where('user_id', $user->id)
                    ->where('valid', true)
                    ->whereNotNull('completed_at')
                    ->whereDate('completed_at', $currentDate)
                    ->exists();
                    
                if ($hasCompletedPuzzle) {
                    $streak++;
                    $currentDate->subDay();
                } else {
                    break;
                }
            }
            
            return $streak;
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function startChallenge(int $challengeId)
    {
        try {
            $challenge = Challenge::findOrFail($challengeId);
            
            // Verifica che la sfida sia attiva
            if ($challenge->status !== 'active' || $challenge->ends_at <= now()) {
                $this->dispatch('challenge-error', message: 'Questa sfida non è più disponibile.');
                return;
            }

            // Verifica se l'utente ha già un tentativo in corso
            $existingAttempt = ChallengeAttempt::where('user_id', auth()->id())
                ->where('challenge_id', $challengeId)
                ->first();

            if ($existingAttempt && $existingAttempt->completed_at) {
                $this->dispatch('challenge-error', message: 'Hai già completato questa sfida.');
                return;
            }

            // Redirect alla pagina della sfida
            return redirect()->route('challenges.play', ['challenge' => $challengeId]);
        } catch (\Exception $e) {
            $this->dispatch('challenge-error', message: 'Errore nel caricamento della sfida. Le sfide saranno disponibili presto!');
            return;
        }
    }

    public function refreshData(): void
    {
        $this->loadDashboardData();
        $this->dispatch('data-refreshed');
    }

    public function getChallengeStatus(int $challengeId): string
    {
        $attempt = $this->userAttempts->get($challengeId);
        
        if (!$attempt) {
            return 'not_started';
        }
        
        if ($attempt->completed_at) {
            return 'completed';
        }
        
        return 'in_progress';
    }

    public function getChallengeStatusLabel(int $challengeId): string
    {
        return match ($this->getChallengeStatus($challengeId)) {
            'not_started' => 'Non iniziata',
            'in_progress' => 'In corso',
            'completed' => 'Completata',
            default => 'Sconosciuto'
        };
    }

    public function getChallengeStatusColor(int $challengeId): string
    {
        return match ($this->getChallengeStatus($challengeId)) {
            'not_started' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
            'in_progress' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300',
            'completed' => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
            default => 'bg-gray-100 text-gray-700'
        };
    }

    public function getFormattedTime(?int $milliseconds): string
    {
        if (!$milliseconds) return '--:--';
        
        $seconds = intval($milliseconds / 1000);
        $minutes = intval($seconds / 60);
        $seconds = $seconds % 60;
        
        return sprintf('%02d:%02d', $minutes, $seconds);
    }
}
