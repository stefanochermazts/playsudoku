<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Servizio centralizzato per l'audit logging
 */
class AuditService
{
    /**
     * Log creazione sfida
     */
    public function logChallengeCreated(Model $challenge, User $admin): void
    {
        AuditLog::logAdminAction(
            'challenge_created',
            "Admin {$admin->name} ha creato la sfida '{$challenge->title}' (ID: {$challenge->id})",
            $challenge,
            [],
            [
                'challenge_type' => $challenge->type,
                'difficulty' => $challenge->difficulty ?? 'unknown',
                'visibility' => $challenge->visibility,
            ]
        );
    }

    /**
     * Log sospensione sfida
     */
    public function logChallengeSuspended(Model $challenge, User $admin, string $reason = ''): void
    {
        AuditLog::logAdminAction(
            'challenge_suspended',
            "Admin {$admin->name} ha sospeso la sfida '{$challenge->title}' (ID: {$challenge->id})" . 
            ($reason ? " - Motivo: {$reason}" : ''),
            $challenge,
            ['status' => ['from' => 'active', 'to' => 'suspended']],
            ['reason' => $reason]
        );
    }

    /**
     * Log estensione deadline sfida
     */
    public function logChallengeExtended(Model $challenge, User $admin, \Carbon\Carbon $newDeadline): void
    {
        AuditLog::logAdminAction(
            'challenge_extended',
            "Admin {$admin->name} ha esteso la deadline della sfida '{$challenge->title}' (ID: {$challenge->id}) fino al {$newDeadline->format('d/m/Y H:i')}",
            $challenge,
            [
                'ends_at' => [
                    'from' => $challenge->getOriginal('ends_at'),
                    'to' => $newDeadline->toISOString()
                ]
            ]
        );
    }

    /**
     * Log moderazione tentativo
     */
    public function logAttemptModerated(Model $attempt, User $admin, string $action, string $reason = ''): void
    {
        $user = $attempt->user;
        $challenge = $attempt->challenge;
        
        AuditLog::logModerationAction(
            'attempt_moderated',
            "Admin {$admin->name} ha {$action} il tentativo dell'utente {$user->name} sulla sfida '{$challenge->title}'" . 
            ($reason ? " - Motivo: {$reason}" : ''),
            $attempt,
            [
                'moderation_action' => $action,
                'valid_status' => ['from' => $attempt->getOriginal('valid'), 'to' => $attempt->valid],
                'flagged_status' => ['from' => $attempt->getOriginal('flagged_for_review'), 'to' => $attempt->flagged_for_review],
            ]
        );
    }

    /**
     * Log flagging automatico per anomalie
     */
    public function logAutomaticFlag(Model $attempt, string $anomalyType, array $details): void
    {
        AuditLog::logSecurityEvent(
            'automatic_flag',
            "Tentativo dell'utente {$attempt->user->name} flaggato automaticamente per {$anomalyType} sulla sfida '{$attempt->challenge->title}'",
            AuditLog::SEVERITY_WARNING,
            [
                'anomaly_type' => $anomalyType,
                'details' => $details,
                'attempt_id' => $attempt->id,
                'user_id' => $attempt->user_id,
                'challenge_id' => $attempt->challenge_id,
            ]
        );
    }

    /**
     * Log cambio ruolo utente
     */
    public function logUserRoleChanged(User $targetUser, User $admin, string $oldRole, string $newRole): void
    {
        AuditLog::logAdminAction(
            'user_role_changed',
            "Admin {$admin->name} ha cambiato il ruolo dell'utente {$targetUser->name} da '{$oldRole}' a '{$newRole}'",
            $targetUser,
            ['role' => ['from' => $oldRole, 'to' => $newRole]]
        );
    }

    /**
     * Log ban utente
     */
    public function logUserBanned(User $targetUser, User $admin, string $reason = '', ?\Carbon\Carbon $until = null): void
    {
        $description = "Admin {$admin->name} ha bannato l'utente {$targetUser->name}";
        if ($until) {
            $description .= " fino al {$until->format('d/m/Y H:i')}";
        }
        if ($reason) {
            $description .= " - Motivo: {$reason}";
        }

        AuditLog::logAdminAction(
            'user_banned',
            $description,
            $targetUser,
            ['banned' => ['from' => false, 'to' => true]],
            ['reason' => $reason, 'until' => $until?->toISOString()]
        );
    }

    /**
     * Log login sospetto
     */
    public function logSuspiciousLogin(User $user, array $details): void
    {
        AuditLog::logSecurityEvent(
            'suspicious_login',
            "Login sospetto rilevato per l'utente {$user->name}",
            AuditLog::SEVERITY_WARNING,
            array_merge($details, [
                'user_id' => $user->id,
                'user_email' => $user->email,
            ])
        );
    }

    /**
     * Log tentativi di accesso non autorizzato
     */
    public function logUnauthorizedAccess(string $resource, ?User $user = null): void
    {
        $userInfo = $user ? "utente {$user->name} (ID: {$user->id})" : 'utente non autenticato';
        
        AuditLog::logSecurityEvent(
            'unauthorized_access',
            "Tentativo di accesso non autorizzato alla risorsa '{$resource}' da parte di {$userInfo}",
            AuditLog::SEVERITY_CRITICAL,
            [
                'resource' => $resource,
                'user_id' => $user?->id,
                'user_role' => $user?->role,
            ]
        );
    }

    /**
     * Log export dati
     */
    public function logDataExport(string $dataType, User $user, array $metadata = []): void
    {
        AuditLog::logAdminAction(
            'data_export',
            "Utente {$user->name} ha esportato dati di tipo '{$dataType}'",
            null,
            [],
            array_merge($metadata, [
                'data_type' => $dataType,
                'export_timestamp' => now()->toISOString(),
            ])
        );
    }

    /**
     * Log cleanup dati
     */
    public function logDataCleanup(string $cleanupType, int $recordsAffected, array $metadata = []): void
    {
        AuditLog::createLog(
            AuditLog::EVENT_SYSTEM,
            'data_cleanup',
            "Cleanup automatico di tipo '{$cleanupType}' ha processato {$recordsAffected} record",
            null,
            null,
            [],
            array_merge($metadata, [
                'cleanup_type' => $cleanupType,
                'records_affected' => $recordsAffected,
            ]),
            AuditLog::SEVERITY_INFO
        );
    }

    /**
     * Log backup database
     */
    public function logDatabaseBackup(bool $success, array $details = []): void
    {
        AuditLog::createLog(
            AuditLog::EVENT_SYSTEM,
            'database_backup',
            $success ? 'Backup database completato con successo' : 'Backup database fallito',
            null,
            null,
            [],
            $details,
            $success ? AuditLog::SEVERITY_INFO : AuditLog::SEVERITY_CRITICAL
        );
    }

    /**
     * Ottiene statistiche audit per dashboard admin
     */
    public function getAuditStatistics(int $days = 30): array
    {
        $baseQuery = AuditLog::where('created_at', '>=', now()->subDays($days));

        return [
            'total_events' => $baseQuery->count(),
            'admin_actions' => $baseQuery->eventType(AuditLog::EVENT_ADMIN_ACTION)->count(),
            'security_events' => $baseQuery->eventType(AuditLog::EVENT_SECURITY)->count(),
            'moderation_actions' => $baseQuery->eventType(AuditLog::EVENT_MODERATION)->count(),
            'critical_events' => $baseQuery->severity(AuditLog::SEVERITY_CRITICAL)->count(),
            'warning_events' => $baseQuery->severity(AuditLog::SEVERITY_WARNING)->count(),
            'unique_users' => $baseQuery->distinct('user_id')->count(),
            'events_by_day' => $this->getEventsGroupedByDay($days),
        ];
    }

    /**
     * Eventi raggruppati per giorno
     */
    private function getEventsGroupedByDay(int $days): array
    {
        return AuditLog::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();
    }

    /**
     * Cleanup automatico audit log basato su retention policy
     */
    public function cleanupExpiredLogs(): int
    {
        $deletedCount = AuditLog::where('retention_until', '<', now())->delete();
        
        if ($deletedCount > 0) {
            $this->logDataCleanup('audit_logs', $deletedCount, [
                'retention_policy' => 'automatic',
                'criteria' => 'retention_until < now()',
            ]);
        }

        return $deletedCount;
    }
}
