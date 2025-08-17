<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\UserConsent;
use App\Models\AuditLog;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ConsentService
{
    /**
     * Record consent for a user or guest.
     */
    public function recordConsent(
        array $consents,
        ?User $user = null,
        ?string $sessionId = null,
        ?Request $request = null
    ): Collection {
        $request = $request ?? request();
        $recordedConsents = collect();

        foreach ($consents as $type => $value) {
            if (!in_array($type, UserConsent::TYPES)) {
                continue;
            }

            $consent = $this->createConsentRecord(
                type: $type,
                value: $value,
                user: $user,
                sessionId: $sessionId,
                request: $request
            );

            $recordedConsents->push($consent);

            // Log the consent action
            $this->logConsentAction($consent, 'granted', $request);
        }

        return $recordedConsents;
    }

    /**
     * Get current consents for a user or session.
     */
    public function getCurrentConsents(?User $user = null, ?string $sessionId = null): Collection
    {
        $query = UserConsent::query()->active();

        if ($user) {
            $query->where('user_id', $user->id);
        } elseif ($sessionId) {
            $query->where('session_id', $sessionId);
        } else {
            return collect();
        }

        return $query->get()->groupBy('consent_type');
    }

    /**
     * Check if a specific consent is granted for user/session.
     */
    public function hasActiveConsent(string $type, ?User $user = null, ?string $sessionId = null): bool
    {
        $query = UserConsent::query()
            ->ofType($type)
            ->granted()
            ->active();

        if ($user) {
            $query->where('user_id', $user->id);
        } elseif ($sessionId) {
            $query->where('session_id', $sessionId);
        } else {
            return false;
        }

        return $query->exists();
    }

    /**
     * Withdraw consent for a specific type.
     */
    public function withdrawConsent(string $type, ?User $user = null, ?string $sessionId = null): bool
    {
        $query = UserConsent::query()
            ->ofType($type)
            ->active();

        if ($user) {
            $query->where('user_id', $user->id);
        } elseif ($sessionId) {
            $query->where('session_id', $sessionId);
        } else {
            return false;
        }

        $consents = $query->get();
        
        foreach ($consents as $consent) {
            $consent->withdraw();
            $this->logConsentAction($consent, 'withdrawn', request());
        }

        return $consents->isNotEmpty();
    }

    /**
     * Update consent preferences.
     */
    public function updateConsents(
        array $consents,
        ?User $user = null,
        ?string $sessionId = null,
        ?Request $request = null
    ): Collection {
        $request = $request ?? request();
        $updatedConsents = collect();

        foreach ($consents as $type => $value) {
            if (!in_array($type, UserConsent::TYPES)) {
                continue;
            }

            // Withdraw existing consent
            $this->withdrawConsent($type, $user, $sessionId);

            // Record new consent
            $consent = $this->createConsentRecord(
                type: $type,
                value: $value,
                user: $user,
                sessionId: $sessionId,
                request: $request
            );

            $updatedConsents->push($consent);
            $this->logConsentAction($consent, 'updated', $request);
        }

        return $updatedConsents;
    }

    /**
     * Get consent history for a user.
     */
    public function getConsentHistory(?User $user = null, ?string $sessionId = null): Collection
    {
        $query = UserConsent::query()->with('user');

        if ($user) {
            $query->where('user_id', $user->id);
        } elseif ($sessionId) {
            $query->where('session_id', $sessionId);
        } else {
            return collect();
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Clean up expired consents (for scheduled task).
     */
    public function cleanupExpiredConsents(): int
    {
        $expired = UserConsent::expired()->get();
        $count = $expired->count();

        foreach ($expired as $consent) {
            $this->logConsentAction($consent, 'expired', null);
        }

        // Don't delete, just mark as processed
        UserConsent::expired()->update(['metadata->processed' => true]);

        return $count;
    }

    /**
     * Get consent statistics for admin dashboard.
     */
    public function getConsentStatistics(): array
    {
        $total = UserConsent::count();
        $active = UserConsent::active()->count();
        $withdrawn = UserConsent::withdrawn()->count();
        $expired = UserConsent::expired()->count();

        $byType = UserConsent::selectRaw('consent_type, consent_value, COUNT(*) as count')
            ->groupBy('consent_type', 'consent_value')
            ->get()
            ->groupBy('consent_type')
            ->map(function ($group) {
                return $group->pluck('count', 'consent_value');
            });

        return [
            'total' => $total,
            'active' => $active,
            'withdrawn' => $withdrawn,
            'expired' => $expired,
            'by_type' => $byType,
            'compliance_rate' => $total > 0 ? round(($active / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Create a consent record.
     */
    private function createConsentRecord(
        string $type,
        bool $value,
        ?User $user = null,
        ?string $sessionId = null,
        ?Request $request = null
    ): UserConsent {
        $expiresAt = null;
        
        // Set expiration for analytics cookies (13 months as per GDPR recommendation)
        if ($type === UserConsent::TYPE_ANALYTICS && $value) {
            $expiresAt = now()->addMonths(13);
        }

        return UserConsent::create([
            'user_id' => $user?->id,
            'consent_type' => $type,
            'consent_value' => $value,
            'consent_version' => '1.0', // Can be made configurable
            'ip_address' => $request?->ip() ?? '127.0.0.1',
            'user_agent' => $request?->userAgent(),
            'session_id' => $sessionId ?? session()->getId(),
            'granted_at' => $value ? now() : null,
            'expires_at' => $expiresAt,
            'metadata' => [
                'source' => 'website',
                'url' => $request?->fullUrl(),
                'referer' => $request?->header('referer'),
            ],
        ]);
    }

    /**
     * Log consent action for audit trail.
     */
    private function logConsentAction(UserConsent $consent, string $action, ?Request $request = null): void
    {
        if (!class_exists(AuditLog::class)) {
            return;
        }

        $description = match($action) {
            'granted' => "Consenso {$consent->consent_type} concesso",
            'withdrawn' => "Consenso {$consent->consent_type} ritirato",
            'expired' => "Consenso {$consent->consent_type} scaduto",
            default => "Azione consenso: {$action}",
        };

        AuditLog::logConsentAction(
            action: "consent_{$action}",
            description: $description,
            target: $consent,
            user: $consent->user,
            metadata: [
                'consent_type' => $consent->consent_type,
                'consent_value' => $consent->consent_value,
                'action' => $action,
                'ip_address' => $request?->ip() ?? '127.0.0.1',
                'user_agent' => $request?->userAgent(),
            ]
        );
    }
}
