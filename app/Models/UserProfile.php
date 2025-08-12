<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modello per i profili utente estesi
 * 
 * @property int $id
 * @property int $user_id
 * @property string|null $country
 * @property array|null $preferences_json
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'country',
        'preferences_json',
    ];

    protected $casts = [
        'preferences_json' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relazione: un profilo appartiene a un utente
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Ottiene una preferenza specifica
     */
    public function getPreference(string $key, mixed $default = null): mixed
    {
        return data_get($this->preferences_json, $key, $default);
    }

    /**
     * Imposta una preferenza specifica
     */
    public function setPreference(string $key, mixed $value): void
    {
        $preferences = $this->preferences_json ?? [];
        data_set($preferences, $key, $value);
        $this->preferences_json = $preferences;
    }

    /**
     * Ottiene il tema preferito (light/dark)
     */
    public function getTheme(): string
    {
        return $this->getPreference('theme', 'light');
    }

    /**
     * Ottiene la lingua preferita
     */
    public function getLanguage(): string
    {
        return $this->getPreference('language', 'it');
    }

    /**
     * Ottiene le preferenze di notifica
     */
    public function getNotificationPreferences(): array
    {
        return $this->getPreference('notifications', [
            'email_challenges' => true,
            'email_results' => true,
            'browser_notifications' => false,
        ]);
    }

    /**
     * Scope: filtra per paese
     */
    public function scopeByCountry($query, string $country)
    {
        return $query->where('country', $country);
    }

    /**
     * Scope: utenti con tema scuro
     */
    public function scopeDarkTheme($query)
    {
        return $query->whereJsonContains('preferences_json->theme', 'dark');
    }
}
