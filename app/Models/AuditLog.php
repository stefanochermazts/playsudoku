<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Request;

/**
 * Modello per gli audit log di sicurezza
 * 
 * @property int $id
 * @property string $event_type
 * @property string $action
 * @property int|null $user_id
 * @property string|null $user_email
 * @property string|null $user_role
 * @property string|null $target_type
 * @property int|null $target_id
 * @property array|null $target_data
 * @property array|null $changes
 * @property array|null $metadata
 * @property string $description
 * @property string $severity
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property array|null $session_data
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $retention_until
 */
class AuditLog extends Model
{
    use HasFactory;

    // Disable updated_at since audit logs are immutable
    public $timestamps = false;
    
    protected $fillable = [
        'event_type',
        'action',
        'user_id',
        'user_email',
        'user_role',
        'target_type',
        'target_id',
        'target_data',
        'changes',
        'metadata',
        'description',
        'severity',
        'ip_address',
        'user_agent',
        'session_data',
        'created_at',
        'retention_until',
    ];

    protected $casts = [
        'target_data' => 'array',
        'changes' => 'array',
        'metadata' => 'array',
        'session_data' => 'array',
        'created_at' => 'datetime',
        'retention_until' => 'date',
    ];

    /**
     * Event types constants
     */
    public const EVENT_ADMIN_ACTION = 'admin_action';
    public const EVENT_SECURITY = 'security_event';
    public const EVENT_AUTH = 'auth_event';
    public const EVENT_MODERATION = 'moderation';
    public const EVENT_SYSTEM = 'system_event';
    public const EVENT_CONSENT = 'consent_event';

    /**
     * Severity levels constants
     */
    public const SEVERITY_INFO = 'info';
    public const SEVERITY_WARNING = 'warning';
    public const SEVERITY_CRITICAL = 'critical';

    /**
     * Relazione: un audit log appartiene a un utente
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Crea un nuovo audit log
     */
    public static function createLog(
        string $eventType,
        string $action,
        string $description,
        ?User $user = null,
        ?Model $target = null,
        array $changes = [],
        array $metadata = [],
        string $severity = self::SEVERITY_INFO
    ): self {
        
        $user = $user ?? auth()->user();
        
        return self::create([
            'event_type' => $eventType,
            'action' => $action,
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'user_role' => $user?->role,
            'target_type' => $target ? get_class($target) : null,
            'target_id' => $target?->id ?? $target?->getKey(),
            'target_data' => $target ? $target->toArray() : null,
            'changes' => $changes,
            'metadata' => array_merge($metadata, [
                'request_id' => Request::header('X-Request-ID'),
                'route' => Request::route()?->getName(),
                'method' => Request::method(),
            ]),
            'description' => $description,
            'severity' => $severity,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'session_data' => self::getRelevantSessionData(),
            'created_at' => now(),
            'retention_until' => self::calculateRetentionDate($severity),
        ]);
    }

    /**
     * Log azione admin
     */
    public static function logAdminAction(
        string $action,
        string $description,
        ?Model $target = null,
        array $changes = [],
        array $metadata = []
    ): self {
        return self::createLog(
            self::EVENT_ADMIN_ACTION,
            $action,
            $description,
            auth()->user(),
            $target,
            $changes,
            $metadata,
            self::SEVERITY_INFO
        );
    }

    /**
     * Log evento di sicurezza
     */
    public static function logSecurityEvent(
        string $action,
        string $description,
        string $severity = self::SEVERITY_WARNING,
        array $metadata = []
    ): self {
        return self::createLog(
            self::EVENT_SECURITY,
            $action,
            $description,
            auth()->user(),
            null,
            [],
            $metadata,
            $severity
        );
    }

    /**
     * Log evento di moderazione
     */
    public static function logModerationAction(
        string $action,
        string $description,
        ?Model $target = null,
        array $changes = []
    ): self {
        return self::createLog(
            self::EVENT_MODERATION,
            $action,
            $description,
            auth()->user(),
            $target,
            $changes,
            [],
            self::SEVERITY_WARNING
        );
    }

    /**
     * Log evento di consenso
     */
    public static function logConsentAction(
        string $action,
        string $description,
        ?Model $target = null,
        ?User $user = null,
        array $metadata = []
    ): self {
        return self::createLog(
            self::EVENT_CONSENT,
            $action,
            $description,
            $user,
            $target,
            [],
            $metadata,
            self::SEVERITY_INFO
        );
    }

    /**
     * Ottiene dati di sessione rilevanti per l'audit
     */
    private static function getRelevantSessionData(): array
    {
        if (!session()->isStarted()) {
            return [];
        }

        return [
            'session_id' => session()->getId(),
            'csrf_token' => session()->token(),
            'locale' => app()->getLocale(),
            'started_at' => session('login_time'),
        ];
    }

    /**
     * Calcola data di retention basata sulla severità
     */
    private static function calculateRetentionDate(string $severity): \Carbon\Carbon
    {
        return match ($severity) {
            self::SEVERITY_CRITICAL => now()->addYears(7),  // 7 anni per eventi critici
            self::SEVERITY_WARNING => now()->addYears(3),   // 3 anni per warning
            self::SEVERITY_INFO => now()->addYear(),        // 1 anno per info
            default => now()->addYear(),
        };
    }

    /**
     * Scope: filtra per tipo evento
     */
    public function scopeEventType($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Scope: filtra per severità
     */
    public function scopeSeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope: eventi recenti
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope: eventi critici
     */
    public function scopeCritical($query)
    {
        return $query->where('severity', self::SEVERITY_CRITICAL);
    }

    /**
     * Scope: azioni admin
     */
    public function scopeAdminActions($query)
    {
        return $query->where('event_type', self::EVENT_ADMIN_ACTION);
    }

    /**
     * Scope: eventi di sicurezza
     */
    public function scopeSecurityEvents($query)
    {
        return $query->where('event_type', self::EVENT_SECURITY);
    }

    /**
     * Scope: per utente specifico
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: ordinati dal più recente
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}