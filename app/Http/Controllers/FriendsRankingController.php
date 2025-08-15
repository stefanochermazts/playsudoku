<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Challenge;
use App\Models\ChallengeAttempt;

class FriendsRankingController extends Controller
{
    public function __construct()
    {
        // Middleware 'auth' già applicato dal gruppo di rotte
    }

    /**
     * Mostra le classifiche degli amici
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $friends = $user->friends()->pluck('id')->toArray();
        $friends[] = $user->id; // Includi anche l'utente corrente
        
        $type = $request->get('type', 'overall'); // overall, monthly, weekly
        $difficulty = $request->get('difficulty', 'all');
        
        $rankings = $this->getFriendsRankings($friends, $type, $difficulty);
        $userPosition = $this->getUserPosition($rankings, $user->id);
        $stats = $this->getFriendsStats($friends);
        
        return view('rankings.friends', compact(
            'rankings', 
            'userPosition', 
            'stats', 
            'type', 
            'difficulty'
        ));
    }

    /**
     * Confronto diretto tra due amici
     */
    public function compare(User $friend): View
    {
        $user = Auth::user();
        
        // Verifica che siano amici
        if (!$user->isFriendWith($friend)) {
            abort(403, __('app.rankings.not_friends'));
        }
        
        $comparison = $this->getDirectComparison($user, $friend);
        
        return view('rankings.compare', compact('user', 'friend', 'comparison'));
    }

    /**
     * Ottiene le classifiche degli amici
     */
    private function getFriendsRankings(array $friendIds, string $type, string $difficulty): array
    {
        $query = ChallengeAttempt::select('user_id')
            ->selectRaw('COUNT(*) as total_challenges')
            ->selectRaw('SUM(CASE WHEN completed_at IS NOT NULL THEN 1 ELSE 0 END) as completed_challenges')
            ->selectRaw('AVG(CASE WHEN completed_at IS NOT NULL THEN duration_ms END) as avg_time')
            ->selectRaw('MIN(CASE WHEN completed_at IS NOT NULL THEN duration_ms END) as best_time')
            ->selectRaw('AVG(errors_count) as avg_errors')
            ->selectRaw('SUM(hints_used) as total_hints')
            ->whereIn('user_id', $friendIds)
            ->where('valid', true)
            ->with('user');

        // Filtro per tipo di periodo
        switch ($type) {
            case 'monthly':
                $query->where('created_at', '>=', now()->startOfMonth());
                break;
            case 'weekly':
                $query->where('created_at', '>=', now()->startOfWeek());
                break;
        }

        // Filtro per difficoltà
        if ($difficulty !== 'all') {
            $query->where('difficulty', $difficulty);
        }

        $results = $query->groupBy('user_id')
            ->orderByDesc('completed_challenges')
            ->orderBy('avg_time')
            ->get();

        return $results->map(function ($item, $index) {
            return [
                'position' => $index + 1,
                'user' => User::find($item->user_id),
                'total_challenges' => $item->total_challenges,
                'completed_challenges' => $item->completed_challenges,
                'completion_rate' => $item->total_challenges > 0 ? 
                    round(($item->completed_challenges / $item->total_challenges) * 100, 1) : 0,
                'avg_time' => $item->avg_time,
                'best_time' => $item->best_time,
                'avg_errors' => round($item->avg_errors, 1),
                'total_hints' => $item->total_hints,
            ];
        })->toArray();
    }

    /**
     * Trova la posizione dell'utente nella classifica
     */
    private function getUserPosition(array $rankings, int $userId): ?array
    {
        foreach ($rankings as $ranking) {
            if ($ranking['user']->id === $userId) {
                return $ranking;
            }
        }
        return null;
    }

    /**
     * Ottiene statistiche generali degli amici
     */
    private function getFriendsStats(array $friendIds): array
    {
        $totalFriends = count($friendIds) - 1; // Escludi l'utente corrente
        
        $activeFriends = ChallengeAttempt::whereIn('user_id', $friendIds)
            ->where('created_at', '>=', now()->subDays(7))
            ->distinct('user_id')
            ->count();

        $totalChallenges = ChallengeAttempt::whereIn('user_id', $friendIds)
            ->where('valid', true)
            ->count();

        $avgCompletionRate = ChallengeAttempt::whereIn('user_id', $friendIds)
            ->selectRaw('AVG(CASE WHEN completed_at IS NOT NULL THEN 1.0 ELSE 0.0 END) * 100 as rate')
            ->value('rate');

        return [
            'total_friends' => $totalFriends,
            'active_friends' => $activeFriends - 1, // Escludi l'utente corrente se attivo
            'total_challenges' => $totalChallenges,
            'avg_completion_rate' => round($avgCompletionRate ?? 0, 1),
        ];
    }

    /**
     * Confronto diretto tra due utenti
     */
    private function getDirectComparison(User $user1, User $user2): array
    {
        $comparison = [];
        
        // Statistiche generali
        $comparison['general'] = [
            'user1' => $this->getUserGeneralStats($user1),
            'user2' => $this->getUserGeneralStats($user2),
        ];

        // Confronto per difficoltà
        $difficulties = ['easy', 'normal', 'hard', 'expert', 'crazy'];
        $comparison['by_difficulty'] = [];
        
        foreach ($difficulties as $difficulty) {
            $comparison['by_difficulty'][$difficulty] = [
                'user1' => $this->getUserStatsByDifficulty($user1, $difficulty),
                'user2' => $this->getUserStatsByDifficulty($user2, $difficulty),
            ];
        }

        // Sfide head-to-head (stesse sfide completate da entrambi)
        $comparison['head_to_head'] = $this->getHeadToHeadStats($user1, $user2);

        return $comparison;
    }

    /**
     * Statistiche generali di un utente
     */
    private function getUserGeneralStats(User $user): array
    {
        $attempts = ChallengeAttempt::where('user_id', $user->id)
            ->where('valid', true);

        $completed = $attempts->whereNotNull('completed_at');

        return [
            'total_attempts' => $attempts->count(),
            'completed' => $completed->count(),
            'completion_rate' => $attempts->count() > 0 ? 
                round(($completed->count() / $attempts->count()) * 100, 1) : 0,
            'best_time' => $completed->min('duration_ms'),
            'avg_time' => $completed->avg('duration_ms'),
            'total_errors' => $attempts->sum('errors_count'),
            'total_hints' => $attempts->sum('hints_used'),
        ];
    }

    /**
     * Statistiche per difficoltà
     */
    private function getUserStatsByDifficulty(User $user, string $difficulty): array
    {
        $attempts = ChallengeAttempt::where('user_id', $user->id)
            ->where('difficulty', $difficulty)
            ->where('valid', true);

        $completed = $attempts->whereNotNull('completed_at');

        return [
            'completed' => $completed->count(),
            'best_time' => $completed->min('duration_ms'),
            'avg_time' => $completed->avg('duration_ms'),
        ];
    }

    /**
     * Statistiche head-to-head
     */
    private function getHeadToHeadStats(User $user1, User $user2): array
    {
        // Trova le sfide completate da entrambi
        $user1Challenges = ChallengeAttempt::where('user_id', $user1->id)
            ->whereNotNull('completed_at')
            ->where('valid', true)
            ->pluck('challenge_id');

        $user2Challenges = ChallengeAttempt::where('user_id', $user2->id)
            ->whereNotNull('completed_at')
            ->where('valid', true)
            ->pluck('challenge_id');

        $commonChallenges = $user1Challenges->intersect($user2Challenges);

        if ($commonChallenges->isEmpty()) {
            return [
                'total_common' => 0,
                'user1_wins' => 0,
                'user2_wins' => 0,
                'ties' => 0,
                'details' => [],
            ];
        }

        $user1Wins = 0;
        $user2Wins = 0;
        $ties = 0;
        $details = [];

        foreach ($commonChallenges as $challengeId) {
            $user1Time = ChallengeAttempt::where('user_id', $user1->id)
                ->where('challenge_id', $challengeId)
                ->whereNotNull('completed_at')
                ->where('valid', true)
                ->min('duration_ms');

            $user2Time = ChallengeAttempt::where('user_id', $user2->id)
                ->where('challenge_id', $challengeId)
                ->whereNotNull('completed_at')
                ->where('valid', true)
                ->min('duration_ms');

            if ($user1Time < $user2Time) {
                $user1Wins++;
                $winner = $user1->id;
            } elseif ($user2Time < $user1Time) {
                $user2Wins++;
                $winner = $user2->id;
            } else {
                $ties++;
                $winner = null;
            }

            $details[] = [
                'challenge_id' => $challengeId,
                'user1_time' => $user1Time,
                'user2_time' => $user2Time,
                'winner' => $winner,
            ];
        }

        return [
            'total_common' => $commonChallenges->count(),
            'user1_wins' => $user1Wins,
            'user2_wins' => $user2Wins,
            'ties' => $ties,
            'details' => array_slice($details, 0, 10), // Mostra solo le ultime 10
        ];
    }
}
