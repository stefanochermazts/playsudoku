<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ChallengeAttempt;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserProfileController extends Controller
{
    /**
     * Mostra il profilo pubblico di un utente
     */
    public function show(User $user): View
    {
        $viewer = Auth::user();
        
        // Se l'utente sta visualizzando il proprio profilo, redirect al profilo completo
        if ($viewer && $viewer->id === $user->id) {
            return redirect()->route('localized.profile', ['locale' => app()->getLocale()]);
        }

        // Verifica se il profilo è visibile
        if (!$user->isProfileVisibleTo($viewer)) {
            abort(403, __('app.privacy.profile_not_visible', ['name' => $user->name]));
        }
        
        // Statistiche solo se visibili
        if ($user->areStatsVisibleTo($viewer)) {
            $stats = $this->getUserPublicStats($user);
        } else {
            $stats = [
                'member_since' => $user->created_at->format('F Y'),
                'friends_count' => $user->friends_count,
            ];
        }
        
        // Verifica se sono amici (se l'utente è autenticato)
        $areFriends = $viewer ? $user->isFriendWith($viewer) : false;
        
        // Amici in comune (se l'utente è autenticato)
        $mutualFriends = $viewer ? $this->getMutualFriends($viewer, $user) : collect();

        // Stato amicizia e possibilità di inviare richiesta
        $friendshipStatus = null;
        $canSendFriendRequest = false;
        
        if ($viewer) {
            if ($areFriends) {
                $friendshipStatus = 'friends';
            } elseif ($user->hasPendingFriendRequestWith($viewer)) {
                $friendshipStatus = 'pending';
            } elseif ($user->canReceiveFriendRequests()) {
                $canSendFriendRequest = true;
            }
        }

        return view('profiles.public', compact(
            'user', 
            'stats', 
            'areFriends', 
            'mutualFriends', 
            'friendshipStatus', 
            'canSendFriendRequest'
        ));
    }

    /**
     * Ottiene le statistiche pubbliche dell'utente
     */
    private function getUserPublicStats(User $user): array
    {
        // Sfide completate con successo
        $completedChallenges = ChallengeAttempt::where('user_id', $user->id)
            ->where('completed_at', '!=', null)
            ->where('valid', true)
            ->count();

        // Miglior tempo overall (tutti i livelli)
        $bestTimeAttempt = ChallengeAttempt::where('user_id', $user->id)
            ->where('completed_at', '!=', null)
            ->where('valid', true)
            ->where('move_validation_passed', true)
            ->orderBy('duration_ms')
            ->first();

        $bestTime = $bestTimeAttempt ? $this->formatDuration($bestTimeAttempt->duration_ms) : null;

        // Statistiche per difficoltà (join con challenges -> puzzles per ricavare la difficulty)
        $statsByDifficulty = ChallengeAttempt::join('challenges', 'challenge_attempts.challenge_id', '=', 'challenges.id')
            ->join('puzzles', 'challenges.puzzle_id', '=', 'puzzles.id')
            ->select('puzzles.difficulty')
            ->selectRaw('COUNT(*) as total_attempts')
            ->selectRaw('COUNT(CASE WHEN completed_at IS NOT NULL AND valid = true THEN 1 END) as completed')
            ->selectRaw('MIN(CASE WHEN completed_at IS NOT NULL AND valid = true AND move_validation_passed = true THEN challenge_attempts.duration_ms END) as best_time_ms')
            ->selectRaw('AVG(CASE WHEN completed_at IS NOT NULL AND valid = true AND move_validation_passed = true THEN challenge_attempts.duration_ms END) as avg_time_ms')
            ->where('challenge_attempts.user_id', $user->id)
            ->groupBy('puzzles.difficulty')
            ->get()
            ->mapWithKeys(function ($stat) {
                return [$stat->difficulty => [
                    'total_attempts' => $stat->total_attempts,
                    'completed' => $stat->completed,
                    'completion_rate' => $stat->total_attempts > 0 ? round(($stat->completed / $stat->total_attempts) * 100) : 0,
                    'best_time' => $stat->best_time_ms ? $this->formatDuration($stat->best_time_ms) : null,
                    'avg_time' => $stat->avg_time_ms ? $this->formatDuration(round($stat->avg_time_ms)) : null,
                ]];
            });

        // Streak corrente (giorni consecutivi con almeno una sfida completata)
        $currentStreak = $this->calculateCurrentStreak($user);

        // Data di registrazione
        $memberSince = $user->created_at->format('F Y');

        return [
            'completed_challenges' => $completedChallenges,
            'best_time' => $bestTime,
            'current_streak' => $currentStreak,
            'member_since' => $memberSince,
            'by_difficulty' => $statsByDifficulty,
            'friends_count' => $user->friends_count,
        ];
    }

    /**
     * Ottiene gli amici in comune tra due utenti
     */
    private function getMutualFriends(User $currentUser, User $targetUser)
    {
        $currentUserFriends = $currentUser->friends()->pluck('id');
        $targetUserFriends = $targetUser->friends()->pluck('id');
        
        $mutualFriendIds = $currentUserFriends->intersect($targetUserFriends);
        
        return User::whereIn('id', $mutualFriendIds)->get();
    }

    /**
     * Calcola lo streak corrente di giorni consecutivi
     */
    private function calculateCurrentStreak(User $user): int
    {
        // Query per ottenere i giorni unici con sfide completate, ordinati per data
        $completionDays = ChallengeAttempt::where('user_id', $user->id)
            ->where('completed_at', '!=', null)
            ->where('valid', true)
            ->selectRaw('DATE(completed_at) as completion_date')
            ->distinct()
            ->orderBy('completion_date', 'desc')
            ->pluck('completion_date')
            ->map(fn($date) => \Carbon\Carbon::parse($date));

        if ($completionDays->isEmpty()) {
            return 0;
        }

        $streak = 0;
        $currentDate = now()->startOfDay();
        
        foreach ($completionDays as $completionDate) {
            if ($completionDate->equalTo($currentDate) || $completionDate->equalTo($currentDate->copy()->subDay())) {
                $streak++;
                $currentDate = $completionDate->copy()->subDay();
            } else {
                break;
            }
        }

        return $streak;
    }

    /**
     * Formatta la durata in millisecondi in formato leggibile
     */
    private function formatDuration(int $milliseconds): string
    {
        $totalSeconds = round($milliseconds / 1000);
        $minutes = floor($totalSeconds / 60);
        $seconds = $totalSeconds % 60;

        if ($minutes > 0) {
            return sprintf('%d:%02d', $minutes, $seconds);
        }

        return sprintf('%ds', $seconds);
    }
}
