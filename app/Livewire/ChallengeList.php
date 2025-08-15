<?php
declare(strict_types=1);

namespace App\Livewire;

use App\Models\Challenge;
use App\Models\ChallengeAttempt;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class ChallengeList extends Component
{
    use WithPagination;

    public string $filter = 'all'; // all, daily, weekly, custom
    public string $status = 'all'; // all, not_started, in_progress, completed
    public Collection $userAttempts;

    public function mount(): void
    {
        $this->loadUserAttempts();
    }

    public function render()
    {
        $challenges = $this->getChallengesQuery();
        
        return view('livewire.challenge-list', [
            'challenges' => $challenges->paginate(12),
        ]);
    }

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
        $this->resetPage();
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
        $this->resetPage();
    }

    public function startChallenge(int $challengeId)
    {
        $challenge = Challenge::findOrFail($challengeId);
        
        // Verifica che la sfida sia attiva
        if ($challenge->status !== 'active' || $challenge->ends_at <= now()) {
            $this->dispatch('challenge-error', message: __('app.challenges.challenge_not_available'));
            return;
        }

        // Verifica se l'utente ha già completato questa sfida
        $existingAttempt = ChallengeAttempt::where('user_id', auth()->id())
            ->where('challenge_id', $challengeId)
            ->first();

        if ($existingAttempt && $existingAttempt->completed_at) {
            $this->dispatch('challenge-error', message: __('app.challenges.already_completed'));
            return;
        }

        // Redirect alla pagina della sfida
        return redirect()->route('challenges.play', ['challenge' => $challengeId]);
    }

    private function getChallengesQuery()
    {
        $query = Challenge::with(['puzzle', 'creator'])
            ->where('visibility', 'public')
            ->orderBy('starts_at', 'desc');

        // Filtro per tipo
        if ($this->filter !== 'all') {
            $query->where('type', $this->filter);
        }

        // Filtro per stato personale
        if ($this->status !== 'all') {
            // La collezione è indicizzata per challenge_id, usiamo direttamente le chiavi
            $userAttemptIds = $this->userAttempts->keys();

            switch ($this->status) {
                case 'not_started':
                    $query->whereNotIn('id', $userAttemptIds->all());
                    break;
                case 'in_progress':
                    $inProgressIds = $this->userAttempts
                        ->filter(fn($attempt) => !$attempt->completed_at)
                        ->keys();
                    $query->whereIn('id', $inProgressIds->all());
                    break;
                case 'completed':
                    $completedIds = $this->userAttempts
                        ->filter(fn($attempt) => $attempt->completed_at)
                        ->keys();
                    $query->whereIn('id', $completedIds->all());
                    break;
            }
        }

        return $query;
    }

    private function loadUserAttempts(): void
    {
        $this->userAttempts = ChallengeAttempt::where('user_id', auth()->id())
            ->get()
            ->keyBy('challenge_id');
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
        $status = $this->getChallengeStatus($challengeId);
        
        return match ($status) {
            'not_started' => __('app.challenges.status_not_started'),
            'in_progress' => __('app.challenges.status_in_progress'),
            'completed' => __('app.challenges.status_completed'),
            default => $status,
        };
    }

    public function getChallengeStatusColor(int $challengeId): string
    {
        $status = $this->getChallengeStatus($challengeId);
        
        return match ($status) {
            'not_started' => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200',
            'in_progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-200',
            'completed' => 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-200',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getFormattedTime(?int $milliseconds): string
    {
        if (!$milliseconds) {
            return __('app.dashboard.never');
        }
        
        $seconds = intval($milliseconds / 1000);
        $minutes = intval($seconds / 60);
        $remainingSeconds = $seconds % 60;
        
        return sprintf('%d:%02d', $minutes, $remainingSeconds);
    }
}