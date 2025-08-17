<?php

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
        $statistics = app(ConsentService::class)->getConsentStatistics();
        
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
        
        // Get related audit logs
        $auditLogs = \App\Models\AuditLog::where('target_type', UserConsent::class)
            ->where('target_id', $consent->id)
            ->orderBy('created_at', 'desc')
            ->get();
            
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
            
        return view('admin.consents.show', compact('consent', 'auditLogs', 'relatedConsents'));
    }
    
    /**
     * Withdraw a consent.
     */
    public function withdraw(UserConsent $consent): RedirectResponse
    {
        if ($consent->isWithdrawn()) {
            return back()->with('error', 'Il consenso è già stato ritirato.');
        }
        
        $consent->withdraw();
        
        // Log the action
        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'consent_withdrawn_by_admin',
            'model_type' => UserConsent::class,
            'model_id' => $consent->id,
            'old_values' => $consent->getOriginal(),
            'new_values' => $consent->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => [
                'admin_action' => true,
                'reason' => 'admin_withdrawal'
            ],
        ]);
        
        return back()->with('success', 'Consenso ritirato con successo.');
    }
    
    /**
     * Export user data for GDPR requests.
     */
    public function exportUserData(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'format' => 'required|in:json,csv'
        ]);
        
        $user = User::find($request->user_id);
        $consents = app(ConsentService::class)->getConsentHistory($user);
        
        $data = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
            'consents' => $consents->map(function ($consent) {
                return [
                    'type' => $consent->consent_type,
                    'value' => $consent->consent_value,
                    'granted_at' => $consent->granted_at,
                    'withdrawn_at' => $consent->withdrawn_at,
                    'expires_at' => $consent->expires_at,
                    'ip_address' => $consent->ip_address,
                    'user_agent' => $consent->user_agent,
                    'created_at' => $consent->created_at,
                ];
            }),
        ];
        
        if ($request->format === 'json') {
            return response()->json($data)
                ->header('Content-Disposition', 'attachment; filename="user_data_' . $user->id . '.json"');
        }
        
        // CSV format
        $csvData = [];
        $csvData[] = ['Type', 'Data', 'Value', 'Date'];
        $csvData[] = ['User Info', 'Name', $user->name, $user->created_at];
        $csvData[] = ['User Info', 'Email', $user->email, $user->created_at];
        
        foreach ($consents as $consent) {
            $csvData[] = [
                'Consent',
                $consent->consent_type,
                $consent->consent_value ? 'Granted' : 'Denied',
                $consent->created_at
            ];
        }
        
        $filename = 'user_data_' . $user->id . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Bulk export consents.
     */
    public function export(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $request->validate([
            'format' => 'required|in:json,csv',
            'type' => 'nullable|in:essential,analytics,marketing,contact_form,registration,privacy_settings,newsletter',
            'status' => 'nullable|in:active,expired,withdrawn,granted,denied',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);
        
        $query = UserConsent::with('user');
        
        // Apply filters
        if ($request->type) {
            $query->ofType($request->type);
        }
        
        if ($request->status) {
            match ($request->status) {
                'active' => $query->active(),
                'expired' => $query->expired(),
                'withdrawn' => $query->withdrawn(),
                'granted' => $query->granted(),
                'denied' => $query->denied(),
                default => null,
            };
        }
        
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $consents = $query->orderBy('created_at', 'desc')->get();
        
        if ($request->format === 'json') {
            $data = $consents->map(function ($consent) {
                return [
                    'id' => $consent->id,
                    'user_name' => $consent->user?->name,
                    'user_email' => $consent->user?->email,
                    'consent_type' => $consent->consent_type,
                    'consent_value' => $consent->consent_value,
                    'status' => $consent->status_display,
                    'granted_at' => $consent->granted_at,
                    'withdrawn_at' => $consent->withdrawn_at,
                    'expires_at' => $consent->expires_at,
                    'ip_address' => $consent->ip_address,
                    'created_at' => $consent->created_at,
                ];
            });
            
            return response()->json(['consents' => $data])
                ->header('Content-Disposition', 'attachment; filename="consents_export.json"');
        }
        
        // CSV format
        $filename = 'consents_export_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($consents) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, [
                'ID', 'User Name', 'User Email', 'Consent Type', 'Consent Value', 
                'Status', 'Granted At', 'Withdrawn At', 'Expires At', 'IP Address', 'Created At'
            ]);
            
            // Data
            foreach ($consents as $consent) {
                fputcsv($file, [
                    $consent->id,
                    $consent->user?->name ?? 'Guest',
                    $consent->user?->email ?? '',
                    $consent->consent_type,
                    $consent->consent_value ? 'Granted' : 'Denied',
                    $consent->status_display,
                    $consent->granted_at?->format('Y-m-d H:i:s') ?? '',
                    $consent->withdrawn_at?->format('Y-m-d H:i:s') ?? '',
                    $consent->expires_at?->format('Y-m-d H:i:s') ?? '',
                    $consent->ip_address,
                    $consent->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Clean up expired consents.
     */
    public function cleanup(): RedirectResponse
    {
        $cleanedCount = app(ConsentService::class)->cleanupExpiredConsents();
        
        return back()->with('success', "Pulizia completata. {$cleanedCount} consensi scaduti processati.");
    }
    
    /**
     * Get consent statistics for API.
     */
    public function statistics(): JsonResponse
    {
        $stats = app(ConsentService::class)->getConsentStatistics();
        
        // Additional statistics
        $recentConsents = UserConsent::where('created_at', '>=', now()->subDays(30))->count();
        $topCountries = UserConsent::selectRaw('COUNT(*) as count, 
                CASE 
                    WHEN ip_address::text LIKE ? THEN ?
                    WHEN ip_address::text LIKE ? THEN ?
                    ELSE ?
                END as location', ['192.168.%', 'Local', '127.%', 'Localhost', 'External'])
            ->groupBy('location')
            ->orderBy('count', 'desc')
            ->get();
            
        $stats['recent_consents'] = $recentConsents;
        $stats['top_locations'] = $topCountries;
        
        return response()->json($stats);
    }
}