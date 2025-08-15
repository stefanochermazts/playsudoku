<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityFeed;

class ActivityFeedController extends Controller
{
    public function __construct()
    {
        // Middleware 'auth' già applicato dal gruppo di rotte
    }

    /**
     * Mostra l'activity feed dell'utente
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $limit = (int) $request->get('limit', 20);
        
        $activities = ActivityFeed::getFeedForUser($user, $limit);
        $stats = $this->getActivityStats($user);
        
        return view('activity.index', compact('activities', 'stats'));
    }

    /**
     * Ottiene statistiche per l'activity feed
     */
    private function getActivityStats($user): array
    {
        $friendIds = $user->friends()->pluck('id')->toArray();
        $friendIds[] = $user->id;

        // Attività totali degli ultimi 7 giorni
        $recentActivities = ActivityFeed::whereIn('user_id', $friendIds)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        // Attività per tipo negli ultimi 30 giorni
        $activityTypes = ActivityFeed::whereIn('user_id', $friendIds)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        // Amici più attivi negli ultimi 7 giorni
        $activeFriends = ActivityFeed::whereIn('user_id', $friendIds)
            ->where('user_id', '!=', $user->id) // Escludi l'utente corrente
            ->where('created_at', '>=', now()->subDays(7))
            ->with('user')
            ->selectRaw('user_id, COUNT(*) as activity_count')
            ->groupBy('user_id')
            ->orderByDesc('activity_count')
            ->limit(5)
            ->get();

        return [
            'recent_activities' => $recentActivities,
            'activity_types' => $activityTypes,
            'active_friends' => $activeFriends,
            'total_friends' => count($friendIds) - 1,
        ];
    }
}
