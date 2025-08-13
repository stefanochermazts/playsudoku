<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Challenge;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    public function show(Request $request, $challenge)
	{
		$challenge = Challenge::with('puzzle')->findOrFail((int) $challenge);
		$attempts = $challenge->attempts()
			->where('valid', true)
			->whereNotNull('completed_at')
			->with('user')
			->orderBy('duration_ms')
			->orderBy('errors_count')
			->orderBy('completed_at')
			->paginate(25);

		// Calcola posizione dell'utente corrente se presente
		$userRank = null;
		if (auth()->check()) {
			$userId = auth()->id();
			$all = $challenge->attempts()
				->where('valid', true)
				->whereNotNull('completed_at')
				->orderBy('duration_ms')
				->orderBy('errors_count')
				->orderBy('completed_at')
				->pluck('user_id')
				->toArray();
			$index = array_search($userId, $all, true);
			$userRank = $index === false ? null : ($index + 1);
		}

		return view('leaderboard.show', compact('challenge', 'attempts', 'userRank'));
	}
}


