<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserConsent;
use App\Models\User;
use App\Services\ConsentService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ConsentController extends Controller
{
    public function __construct(private ConsentService $consentService)
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display consent management dashboard.
     */
    public function index(Request $request): View
    {
        $query = UserConsent::query()->with('user');
        
        // Filters
        if ($request->filled('type')) {
            $query->ofType($request->type);
        }
        
        if ($request->filled('status')) {
            match ($request->status) {
                'active' => $query->active(),
                'expired' => $query->expired(),
                'withdrawn' => $query->withdrawn(),
                'granted' => $query->granted(),
                'denied' => $query->denied(),
                default => null,
            };
        }
        
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $consents = $query->orderBy('created_at', 'desc')->paginate(25);
        $statistics = $this->consentService->getConsentStatistics();
        
        // Users for filter dropdown
        $users = User::select('id', 'name', 'email')
            ->whereHas('consents')
            ->orderBy('name')
            ->get();

        return view('admin.consents.index', compact('consents', 'statistics', 'users'));
    }

    /**
     * Show consent details.
     */
    public function show(UserConsent $consent): View
    {
        $consent->load('user');
        
        // Get related consents for same user/session
        $relatedConsents = UserConsent::query()
            ->where(function ($query) use ($consent) {
                if ($consent->user_id) {
                    $query->where('user_id', $consent->user_id);
                } else {
                    $query->where('session_id', $consent->session_id);
                }
            })
            ->where('id', '!=', $consent->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.consents.show', compact('consent', 'relatedConsents'));
    }

    /**
     * Withdraw a consent (admin action).
     */
    public function withdraw(UserConsent $consent): RedirectResponse
    {
        if ($consent->isWithdrawn()) {
            return back()->with('error', 'Il consenso è già stato ritirato.');
        }

        $consent->withdraw();
        
        // Log admin action
        activity()
            ->causedBy(auth()->user())
            ->performedOn($consent)
            ->withProperties([
                'consent_type' => $consent->consent_type,
                'user_id' => $consent->user_id,
                'admin_action' => true,
            ])
            ->log('admin_consent_withdrawn');

        return back()->with('success', 'Consenso ritirato con successo.');
    }

    /**
     * Get consent statistics as JSON.
     */
    public function statistics(): JsonResponse
    {
        $statistics = $this->consentService->getConsentStatistics();
        
        // Additional admin-specific stats
        $recentConsents = UserConsent::where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $statistics['recent_trend'] = $recentConsents;
        
        return response()->json($statistics);
    }

    /**
     * Export consent data (for GDPR data requests).
     */
    public function export(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $consents = $this->consentService->getConsentHistory($user);

        $data = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'consents' => $consents->map(function ($consent) {
                return [
                    'type' => $consent->consent_type,
                    'value' => $consent->consent_value,
                    'granted_at' => $consent->granted_at?->toISOString(),
                    'withdrawn_at' => $consent->withdrawn_at?->toISOString(),
                    'expires_at' => $consent->expires_at?->toISOString(),
                    'ip_address' => $consent->ip_address,
                    'user_agent' => $consent->user_agent,
                    'metadata' => $consent->metadata,
                ];
            }),
            'exported_at' => now()->toISOString(),
            'exported_by' => auth()->user()->name,
        ];

        $filename = "consent_export_{$user->id}_" . now()->format('Y-m-d_H-i-s') . '.json';

        return response()->json($data)
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Cleanup expired consents.
     */
    public function cleanup(): RedirectResponse
    {
        $cleaned = $this->consentService->cleanupExpiredConsents();
        
        activity()
            ->causedBy(auth()->user())
            ->withProperties(['cleaned_count' => $cleaned])
            ->log('admin_consent_cleanup');

        return back()->with('success', "Puliti {$cleaned} consensi scaduti.");
    }

    /**
     * User consent overview.
     */
    public function userConsents(User $user): View
    {
        $currentConsents = $this->consentService->getCurrentConsents($user);
        $consentHistory = $this->consentService->getConsentHistory($user);
        
        return view('admin.consents.user', compact('user', 'currentConsents', 'consentHistory'));
    }
}
