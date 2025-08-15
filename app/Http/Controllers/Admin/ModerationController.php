<?php
declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use App\Models\ChallengeAttempt;
use App\Services\AnomalyDetectionService;
use App\Jobs\AnalyzeTimingAnomaliesJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Controller per moderazione anti-cheat e gestione tentativi sospetti
 */
class ModerationController extends Controller
{

    /**
     * Dashboard moderazione con statistiche anomalie
     */
    public function dashboard(Request $request)
    {
        $stats = [
            'total_flagged' => ChallengeAttempt::where('flagged_for_review', true)->count(),
            'pending_review' => ChallengeAttempt::where('flagged_for_review', true)
                ->whereNull('reviewed_at')->count(),
            'reviewed_today' => ChallengeAttempt::where('flagged_for_review', true)
                ->whereDate('reviewed_at', today())->count(),
            'invalid_attempts' => ChallengeAttempt::where('valid', false)->count(),
            'move_validation_failed' => ChallengeAttempt::where('move_validation_passed', false)->count(),
        ];

        // Ultimi tentativi sospetti
        $recentSuspicious = ChallengeAttempt::with(['user', 'challenge'])
            ->where('flagged_for_review', true)
            ->whereNull('reviewed_at')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Statistiche per periodo
        $periodicStats = ChallengeAttempt::selectRaw('
                DATE(created_at) as date,
                COUNT(*) as total_attempts,
                SUM(CASE WHEN flagged_for_review = true THEN 1 ELSE 0 END) as flagged_count,
                SUM(CASE WHEN valid = false THEN 1 ELSE 0 END) as invalid_count
            ')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        return view('admin.moderation.dashboard', compact('stats', 'recentSuspicious', 'periodicStats'));
    }

    /**
     * Lista tentativi flaggati per revisione
     */
    public function flaggedAttempts(Request $request)
    {
        $query = ChallengeAttempt::with(['user', 'challenge.puzzle'])
            ->where('flagged_for_review', true);

        // Filtri
        if ($request->filled('status')) {
            if ($request->status === 'pending') {
                $query->whereNull('reviewed_at');
            } elseif ($request->status === 'reviewed') {
                $query->whereNotNull('reviewed_at');
            }
        }

        if ($request->filled('anomaly_type')) {
            $query->where('validation_notes', 'like', '%' . $request->anomaly_type . '%');
        }

        if ($request->filled('challenge_id')) {
            $query->where('challenge_id', $request->challenge_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $attempts = $query->orderBy('created_at', 'desc')->paginate(20);

        // Challenge list per filtro
        $challenges = Challenge::select('id', 'type')
            ->whereHas('attempts', function($q) {
                $q->where('flagged_for_review', true);
            })
            ->get();

        return view('admin.moderation.flagged-attempts', compact('attempts', 'challenges'));
    }

    /**
     * Dettaglio tentativo sospetto con analisi completa
     */
    public function showAttempt(ChallengeAttempt $attempt, AnomalyDetectionService $anomalyService)
    {
        $attempt->load(['user', 'challenge.puzzle', 'moves']);

        // Analisi anomalie
        $analysis = $anomalyService->analyzeAttemptTiming($attempt);
        
        // Statistiche sfida per confronto
        $challengeStats = $anomalyService->getChallengeStatistics($attempt->challenge);

        // Altri tentativi dello stesso utente sulla stessa sfida
        $userAttempts = ChallengeAttempt::where('challenge_id', $attempt->challenge_id)
            ->where('user_id', $attempt->user_id)
            ->where('id', '!=', $attempt->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Pattern comportamentali dell'utente
        $userStats = [
            'total_attempts' => ChallengeAttempt::where('user_id', $attempt->user_id)->count(),
            'valid_attempts' => ChallengeAttempt::where('user_id', $attempt->user_id)->where('valid', true)->count(),
            'flagged_attempts' => ChallengeAttempt::where('user_id', $attempt->user_id)->where('flagged_for_review', true)->count(),
            'avg_duration' => ChallengeAttempt::where('user_id', $attempt->user_id)
                ->whereNotNull('duration_ms')
                ->avg('duration_ms'),
        ];

        return view('admin.moderation.attempt-detail', compact(
            'attempt', 'analysis', 'challengeStats', 'userAttempts', 'userStats'
        ));
    }

    /**
     * Approva un tentativo (rimuove flag di revisione)
     */
    public function approveAttempt(Request $request, ChallengeAttempt $attempt)
    {
        $request->validate([
            'notes' => 'nullable|string|max:500'
        ]);

        DB::transaction(function () use ($request, $attempt) {
            $attempt->update([
                'flagged_for_review' => false,
                'reviewed_at' => now(),
                'reviewed_by' => auth()->id(),
                'admin_notes' => $request->notes ?? 'Approvato dopo revisione manuale',
                'valid' => true, // Rende il tentativo valido
            ]);

            Log::info('Attempt approved by admin', [
                'attempt_id' => $attempt->id,
                'user_id' => $attempt->user_id,
                'challenge_id' => $attempt->challenge_id,
                'admin_id' => auth()->id(),
                'notes' => $request->notes,
            ]);
        });

        return redirect()->back()->with('success', 'Tentativo approvato con successo.');
    }

    /**
     * Rifiuta/invalida un tentativo
     */
    public function rejectAttempt(Request $request, ChallengeAttempt $attempt)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        DB::transaction(function () use ($request, $attempt) {
            $attempt->update([
                'flagged_for_review' => false,
                'reviewed_at' => now(),
                'reviewed_by' => auth()->id(),
                'admin_notes' => 'RESPINTO: ' . $request->reason,
                'valid' => false, // Rende il tentativo non valido
            ]);

            Log::warning('Attempt rejected by admin', [
                'attempt_id' => $attempt->id,
                'user_id' => $attempt->user_id,
                'challenge_id' => $attempt->challenge_id,
                'admin_id' => auth()->id(),
                'reason' => $request->reason,
            ]);
        });

        return redirect()->back()->with('success', 'Tentativo rifiutato e invalidato.');
    }

    /**
     * Riapre una sfida estendendo la deadline
     */
    public function reopenChallenge(Request $request, Challenge $challenge)
    {
        $request->validate([
            'new_end_time' => 'required|date|after:now',
            'reason' => 'required|string|max:500'
        ]);

        $oldEndTime = $challenge->ends_at;
        
        $challenge->update([
            'ends_at' => $request->new_end_time,
            'status' => 'active',
        ]);

        // Log dell'azione
        Log::info('Challenge reopened by admin', [
            'challenge_id' => $challenge->id,
            'old_end_time' => $oldEndTime,
            'new_end_time' => $request->new_end_time,
            'reason' => $request->reason,
            'admin_id' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Sfida riaperta fino al ' . 
            Carbon::parse($request->new_end_time)->format('d/m/Y H:i'));
    }

    /**
     * Analizza una sfida specifica per anomalie
     */
    public function analyzeChallengeAnomalies(Challenge $challenge)
    {
        // Scatena job di analisi
        AnalyzeTimingAnomaliesJob::dispatch($challenge);

        return redirect()->back()->with('success', 
            'Analisi anomalie avviata in background per la sfida #' . $challenge->id);
    }

    /**
     * Scarica report CSV dei tentativi sospetti
     */
    public function exportSuspiciousAttempts(Request $request)
    {
        $attempts = ChallengeAttempt::with(['user', 'challenge'])
            ->where('flagged_for_review', true)
            ->when($request->filled('date_from'), function($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->date_to);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'suspicious_attempts_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($attempts) {
            $file = fopen('php://output', 'w');
            
            // Header CSV
            fputcsv($file, [
                'ID Tentativo', 'Utente', 'Email', 'Sfida ID', 'Tipo Sfida',
                'Durata (ms)', 'Errori', 'Hint Usati', 'Data Completamento',
                'Flag Motivo', 'Stato Revisione', 'Note Admin'
            ]);

            foreach ($attempts as $attempt) {
                fputcsv($file, [
                    $attempt->id,
                    $attempt->user->name,
                    $attempt->user->email,
                    $attempt->challenge_id,
                    $attempt->challenge->type,
                    $attempt->duration_ms,
                    $attempt->errors_count,
                    $attempt->hints_used,
                    $attempt->completed_at?->format('Y-m-d H:i:s'),
                    $attempt->validation_notes,
                    $attempt->reviewed_at ? 'Revisionato' : 'In Attesa',
                    $attempt->admin_notes,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
