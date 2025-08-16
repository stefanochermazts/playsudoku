<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Services\LeaderboardService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DailyBoardController extends Controller
{
    public function __construct(
        private readonly LeaderboardService $leaderboardService
    ) {}

    /**
     * Mostra la board daily corrente
     */
    public function index(Request $request)
    {
        $today = now();
        $dailyChallenge = Challenge::where('type', 'daily')
            ->whereDate('starts_at', $today->toDateString())
            ->with('puzzle')
            ->first();

        $leaderboard = null;
        if ($dailyChallenge) {
            // Allinea il limite a show/weekly per evitare chiavi cache diverse (50 vs 100)
            $leaderboard = $this->leaderboardService->getChallengeLeaderboard($dailyChallenge, 100);
        }

        return view('daily-board.index', compact('dailyChallenge', 'leaderboard', 'today'));
    }

    /**
     * Mostra archivio board daily
     */
    public function archive(Request $request)
    {
        $currentDate = $request->get('date') ? Carbon::parse($request->get('date')) : now();
        
        // Ottieni sfide daily degli ultimi 30 giorni
        $challenges = Challenge::where('type', 'daily')
            ->whereDate('starts_at', '<=', $currentDate->toDateString())
            ->whereDate('starts_at', '>=', $currentDate->copy()->subDays(30)->toDateString())
            ->with(['puzzle', 'attempts' => function($query) {
                $query->where('valid', true)
                    ->whereNotNull('completed_at')
                    ->orderByRaw('(duration_ms + (errors_count * 3000))')
                    ->orderBy('hints_used')
                    ->orderBy('completed_at')
                    ->limit(3);
            }])
            ->orderByDesc('starts_at')
            ->paginate(10);

        return view('daily-board.archive', compact('challenges', 'currentDate'));
    }

    /**
     * Mostra dettaglio board daily specifica
     */
    public function show(Request $request, string $date)
    {
        $targetDate = Carbon::parse($date);
        
        $challenge = Challenge::where('type', 'daily')
            ->whereDate('starts_at', $targetDate->toDateString())
            ->with('puzzle')
            ->firstOrFail();

        $leaderboard = $this->leaderboardService->getChallengeLeaderboard($challenge, 100);
        
        // Statistiche della giornata
        $stats = [
            'total_participants' => $challenge->attempts()->where('valid', true)->distinct('user_id')->count(),
            'completion_rate' => $challenge->attempts()->where('valid', true)->whereNotNull('completed_at')->count(),
            'average_time' => $challenge->attempts()->where('valid', true)->whereNotNull('completed_at')->avg('duration_ms'),
            'fastest_time' => $challenge->attempts()->where('valid', true)->whereNotNull('completed_at')->min('duration_ms'),
        ];

        return view('daily-board.show', compact('challenge', 'leaderboard', 'targetDate', 'stats'));
    }
}
