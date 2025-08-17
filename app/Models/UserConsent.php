<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class UserConsent extends Model
{
    protected $fillable = [
        'user_id',
        'consent_type',
        'consent_value',
        'consent_version',
        'ip_address',
        'user_agent',
        'session_id',
        'granted_at',
        'withdrawn_at',
        'expires_at',
        'metadata',
    ];

    protected $casts = [
        'consent_value' => 'boolean',
        'granted_at' => 'datetime',
        'withdrawn_at' => 'datetime',
        'expires_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Consent types as constants
    public const TYPE_ESSENTIAL = 'essential';
    public const TYPE_ANALYTICS = 'analytics';
    public const TYPE_MARKETING = 'marketing';
    public const TYPE_CONTACT_FORM = 'contact_form';
    public const TYPE_REGISTRATION = 'registration';
    public const TYPE_PRIVACY_SETTINGS = 'privacy_settings';
    public const TYPE_NEWSLETTER = 'newsletter';

    public const TYPES = [
        self::TYPE_ESSENTIAL,
        self::TYPE_ANALYTICS,
        self::TYPE_MARKETING,
        self::TYPE_CONTACT_FORM,
        self::TYPE_REGISTRATION,
        self::TYPE_PRIVACY_SETTINGS,
        self::TYPE_NEWSLETTER,
    ];

    /**
     * Get the user that owns the consent.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter by consent type.
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('consent_type', $type);
    }

    /**
     * Scope to filter by granted consents.
     */
    public function scopeGranted(Builder $query): Builder
    {
        return $query->where('consent_value', true);
    }

    /**
     * Scope to filter by denied consents.
     */
    public function scopeDenied(Builder $query): Builder
    {
        return $query->where('consent_value', false);
    }

    /**
     * Scope to filter by active (non-expired) consents.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        })->whereNull('withdrawn_at');
    }

    /**
     * Scope to filter by expired consents.
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Scope to filter by withdrawn consents.
     */
    public function scopeWithdrawn(Builder $query): Builder
    {
        return $query->whereNotNull('withdrawn_at');
    }

    /**
     * Check if the consent is currently active.
     */
    public function isActive(): bool
    {
        return $this->consent_value 
            && is_null($this->withdrawn_at)
            && (is_null($this->expires_at) || $this->expires_at->isFuture());
    }

    /**
     * Check if the consent has expired.
     */
    public function isExpired(): bool
    {
        return !is_null($this->expires_at) && $this->expires_at->isPast();
    }

    /**
     * Check if the consent has been withdrawn.
     */
    public function isWithdrawn(): bool
    {
        return !is_null($this->withdrawn_at);
    }

    /**
     * Withdraw the consent.
     */
    public function withdraw(): bool
    {
        $this->withdrawn_at = now();
        return $this->save();
    }

    /**
     * Get consent display name.
     */
    public function getDisplayNameAttribute(): string
    {
        return match ($this->consent_type) {
            self::TYPE_ESSENTIAL => 'Cookie Essenziali',
            self::TYPE_ANALYTICS => 'Cookie Analytics',
            self::TYPE_MARKETING => 'Cookie Marketing',
            self::TYPE_CONTACT_FORM => 'Form di Contatto',
            self::TYPE_REGISTRATION => 'Registrazione Account',
            self::TYPE_PRIVACY_SETTINGS => 'Impostazioni Privacy',
            self::TYPE_NEWSLETTER => 'Newsletter',
            default => ucfirst(str_replace('_', ' ', $this->consent_type)),
        };
    }

    /**
     * Get consent status for display.
     */
    public function getStatusDisplayAttribute(): string
    {
        if ($this->isWithdrawn()) {
            return 'Ritirato';
        }
        
        if ($this->isExpired()) {
            return 'Scaduto';
        }
        
        if ($this->consent_value) {
            return $this->isActive() ? 'Attivo' : 'Inattivo';
        }
        
        return 'Negato';
    }
}
