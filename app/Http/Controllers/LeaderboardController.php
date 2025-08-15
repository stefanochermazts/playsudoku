<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Challenge;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class LeaderboardController extends Controller
{
    public function show(Request $request, $challengeId)
    {
        $challenge = Challenge::find((int) $challengeId);

        if (!$challenge) {
            throw new ModelNotFoundException("Challenge with ID {$challengeId} not found.");
        }

        $challenge->load('puzzle');

        $attempts = $challenge->attempts()
			->where('valid', true)
			->whereNotNull('completed_at')
			->with('user')
			->orderByRaw('(duration_ms + (errors_count * 3000))')
			->orderBy('hints_used')
			->orderBy('completed_at')
			->paginate(25);

		// Calcola posizione dell'utente corrente se presente
		$userRank = null;
		if (auth()->check()) {
			$userId = auth()->id();
			$all = $challenge->attempts()
				->where('valid', true)
				->whereNotNull('completed_at')
				->orderByRaw('(duration_ms + (errors_count * 3000))')
				->orderBy('hints_used')
				->orderBy('completed_at')
				->pluck('user_id')
				->toArray();
			$index = array_search($userId, $all, true);
			$userRank = $index === false ? null : ($index + 1);
		}

        // Configura SEO meta tags per questa leaderboard
        $metaService = app(\App\Services\MetaService::class);
        $metaService->setLeaderboard($challenge);

        return view('leaderboard.show', compact('challenge', 'attempts', 'userRank', 'metaService'));
    }

    /**
     * Esporta la classifica in formato CSV
     */
    public function exportCsv(Request $request, $challengeId)
    {
        $challenge = Challenge::find((int) $challengeId);

        if (!$challenge) {
            abort(404, "Challenge with ID {$challengeId} not found.");
        }

        $challenge->load('puzzle');

        $attempts = $challenge->attempts()
            ->where('valid', true)
            ->whereNotNull('completed_at')
            ->with('user')
            ->orderByRaw('(duration_ms + (errors_count * 3000))')
            ->orderBy('hints_used')
            ->orderBy('completed_at')
            ->get();

        $filename = 'leaderboard_challenge_' . $challenge->id . '_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ];

        $callback = function() use ($challenge, $attempts) {
            $file = fopen('php://output', 'w');
            
            // Header del CSV
            fputcsv($file, [
                'Challenge ID',
                'Challenge Type',
                'Difficulty',
                'Puzzle Seed',
                'Date',
                'Rank',
                'Player',
                'Time (ms)',
                'Time (formatted)',
                'Errors',
                'Hints Used',
                'Completed At'
            ]);

            // Aggiungi riga con info challenge
            fputcsv($file, [
                $challenge->id,
                $challenge->type,
                $challenge->puzzle->difficulty,
                $challenge->puzzle->seed,
                $challenge->starts_at->format('Y-m-d'),
                '',
                '',
                '',
                '',
                '',
                '',
                ''
            ]);

            // Riga vuota
            fputcsv($file, []);

            // Dati leaderboard
            foreach ($attempts as $index => $attempt) {
                $ms = (int) ($attempt->duration_ms ?? 0);
                $s = intdiv($ms, 1000);
                $cs = intdiv($ms % 1000, 10);
                $timeFormatted = sprintf('%02d:%02d.%02d', intdiv($s,60), $s%60, $cs);

                fputcsv($file, [
                    $challenge->id,
                    $challenge->type,
                    $challenge->puzzle->difficulty,
                    $challenge->puzzle->seed,
                    $challenge->starts_at->format('Y-m-d'),
                    $index + 1,
                    $attempt->user?->name ?? 'Unknown',
                    $attempt->duration_ms,
                    $timeFormatted,
                    $attempt->errors_count,
                    $attempt->hints_used,
                    $attempt->completed_at?->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}


