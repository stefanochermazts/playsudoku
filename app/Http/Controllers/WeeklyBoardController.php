<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Services\LeaderboardService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WeeklyBoardController extends Controller
{
    public function __construct(
        private readonly LeaderboardService $leaderboardService
    ) {}

    /**
     * Mostra la board weekly corrente
     */
    public function index(Request $request)
    {
        $thisWeek = now()->startOfWeek();
        $weeklyChallenge = Challenge::where('type', 'weekly')
            ->whereBetween('starts_at', [$thisWeek, $thisWeek->copy()->endOfWeek()])
            ->with('puzzle')
            ->first();

        $leaderboard = null;
        if ($weeklyChallenge) {
            $leaderboard = $this->leaderboardService->getChallengeLeaderboard($weeklyChallenge, 100);
        }

        return view('weekly-board.index', compact('weeklyChallenge', 'leaderboard', 'thisWeek'));
    }

    /**
     * Mostra archivio board weekly
     */
    public function archive(Request $request)
    {
        $currentWeek = $request->get('week') 
            ? Carbon::parse($request->get('week'))->startOfWeek()
            : now()->startOfWeek();
        
        // Ottieni sfide weekly delle ultime 12 settimane
        $challenges = Challenge::where('type', 'weekly')
            ->whereDate('starts_at', '<=', $currentWeek->toDateString())
            ->whereDate('starts_at', '>=', $currentWeek->copy()->subWeeks(12)->toDateString())
            ->with(['puzzle', 'attempts' => function($query) {
                $query->where('valid', true)
                    ->whereNotNull('completed_at')
                    ->orderByRaw('(duration_ms + (errors_count * 3000))')
                    ->orderBy('hints_used')
                    ->orderBy('completed_at')
                    ->limit(5);
            }])
            ->orderByDesc('starts_at')
            ->paginate(8);

        \Log::info('🔍 DEBUG Weekly Archive - START', ['request_params' => $request->all()]);
        \Log::info('🔍 DEBUG Weekly Archive - Current Week', ['current_week' => $currentWeek]);
        \Log::info('🔍 DEBUG Weekly Archive - Challenges Query Done', [
            'challenges_count' => $challenges->count(),
            'total_challenges' => $challenges->total()
        ]);

        // Dettagli del primo challenge per debug
        if ($challenges->count() > 0) {
            $firstChallenge = $challenges->first();
            \Log::info('🔍 DEBUG Weekly Archive - First Challenge Details', [
                'id' => $firstChallenge->id,
                'type' => $firstChallenge->type,
                'starts_at' => $firstChallenge->starts_at,
                'ends_at' => $firstChallenge->ends_at,
                'starts_at_type' => gettype($firstChallenge->starts_at),
                'ends_at_type' => gettype($firstChallenge->ends_at),
                'puzzle_exists' => $firstChallenge->puzzle ? 'YES' : 'NO',
                'puzzle_data' => $firstChallenge->puzzle ? [
                    'difficulty' => $firstChallenge->puzzle->difficulty,
                    'seed' => $firstChallenge->puzzle->seed,
                    'difficulty_type' => gettype($firstChallenge->puzzle->difficulty),
                    'seed_type' => gettype($firstChallenge->puzzle->seed)
                ] : null,
                'attempts_count' => $firstChallenge->attempts->count()
            ]);

            if ($firstChallenge->attempts->count() > 0) {
                $firstAttempt = $firstChallenge->attempts->first();
                \Log::info('🔍 DEBUG Weekly Archive - First Attempt Details', [
                    'attempt_id' => $firstAttempt->id,
                    'user_exists' => $firstAttempt->user ? 'YES' : 'NO',
                    'user_name' => $firstAttempt->user?->name,
                    'user_name_type' => $firstAttempt->user ? gettype($firstAttempt->user->name) : 'NULL',
                    'duration_ms' => $firstAttempt->duration_ms,
                    'duration_ms_type' => gettype($firstAttempt->duration_ms),
                    'valid' => $firstAttempt->valid,
                    'valid_type' => gettype($firstAttempt->valid)
                ]);
            }
        }

        \Log::info('🔍 DEBUG Weekly Archive - About to render view');

        return view('weekly-board.archive', compact('challenges', 'currentWeek'));
    }

    /**
     * Mostra dettaglio board weekly specifica
     */
    public function show(Request $request, string $week)
    {
        $targetWeek = Carbon::parse($week)->startOfWeek();
        
        $challenge = Challenge::where('type', 'weekly')
            ->whereBetween('starts_at', [$targetWeek, $targetWeek->copy()->endOfWeek()])
            ->with('puzzle')
            ->firstOrFail();

        $leaderboard = $this->leaderboardService->getChallengeLeaderboard($challenge, 100);
        
        // Statistiche della settimana
        $stats = [
            'total_participants' => $challenge->attempts()->where('valid', true)->distinct('user_id')->count(),
            'completion_rate' => $challenge->attempts()->where('valid', true)->whereNotNull('completed_at')->count(),
            'average_time' => $challenge->attempts()->where('valid', true)->whereNotNull('completed_at')->avg('duration_ms'),
            'fastest_time' => $challenge->attempts()->where('valid', true)->whereNotNull('completed_at')->min('duration_ms'),
            'daily_breakdown' => $this->getDailyBreakdown($challenge),
        ];

        return view('weekly-board.show', compact('challenge', 'leaderboard', 'targetWeek', 'stats'));
    }

    /**
     * Calcola breakdown giornaliero per sfida weekly
     */
    private function getDailyBreakdown(Challenge $challenge): array
    {
        $attempts = $challenge->attempts()
            ->where('valid', true)
            ->whereNotNull('completed_at')
            ->get()
            ->groupBy(function($attempt) {
                return $attempt->completed_at->format('Y-m-d');
            });

        $breakdown = [];
        $start = $challenge->starts_at;
        for ($i = 0; $i < 7; $i++) {
            $date = $start->copy()->addDays($i)->format('Y-m-d');
            $dayAttempts = $attempts->get($date, collect());
            
            $breakdown[] = [
                'date' => $date,
                'day_name' => Carbon::parse($date)->format('l'),
                'completions' => $dayAttempts->count(),
                'avg_time' => $dayAttempts->avg('duration_ms'),
            ];
        }

        return $breakdown;
    }
}
