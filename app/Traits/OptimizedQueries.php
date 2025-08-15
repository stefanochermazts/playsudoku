<?php
declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait per query ottimizzate con eager loading e performance best practices
 */
trait OptimizedQueries
{
    /**
     * Scope: Challenge attempts ottimizzato con eager loading
     */
    public function scopeWithOptimizedRelations(Builder $query): Builder
    {
        return $query->with([
            'user:id,name,email',
            'challenge:id,type,puzzle_id,starts_at,ends_at',
            'challenge.puzzle:id,difficulty,seed'
        ]);
    }

    /**
     * Scope: Solo tentativi validi e completati per leaderboard
     */
    public function scopeForLeaderboard(Builder $query): Builder
    {
        return $query->whereNotNull('completed_at')
            ->where('valid', true)
            ->where(function($q) {
                $q->whereNull('move_validation_passed')
                  ->orWhere('move_validation_passed', true);
            })
            ->where('flagged_for_review', false);
    }

    /**
     * Scope: Ordinamento ottimizzato per ranking
     */
    public function scopeOrderByPerformance(Builder $query): Builder
    {
        return $query->orderByRaw('(duration_ms + (errors_count * 3000))')
            ->orderBy('hints_used', 'asc')
            ->orderBy('completed_at', 'asc');
    }

    /**
     * Scope: Tentativi di oggi per statistiche veloci
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope: Tentativi della settimana corrente
     */
    public function scopeThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Scope: Tentativi del mese corrente  
     */
    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    /**
     * Scope: Utenti con statistiche precaricate
     */
    public function scopeWithUserStats(Builder $query): Builder
    {
        return $query->with([
            'user:id,name,email,created_at',
            'user.profile:user_id,country,preferences_json'
        ]);
    }

    /**
     * Scope: Sfide con puzzle precaricato
     */
    public function scopeWithPuzzleData(Builder $query): Builder
    {
        return $query->with([
            'puzzle:id,givens,solution,difficulty,seed,created_at'
        ]);
    }

    /**
     * Helper: Query ottimizzata per conteggi
     */
    public static function fastCount(array $conditions = []): int
    {
        $query = static::query();
        
        foreach ($conditions as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }
        
        // Usa COUNT(*) ottimizzato invece di Collection::count()
        return $query->count();
    }

    /**
     * Helper: Query batch per aggiornamenti di massa
     */
    public static function batchUpdate(array $ids, array $data): int
    {
        if (empty($ids) || empty($data)) {
            return 0;
        }
        
        return static::whereIn('id', $ids)->update($data);
    }

    /**
     * Helper: Query ottimizzata per esistenza
     */
    public static function fastExists(array $conditions): bool
    {
        $query = static::query();
        
        foreach ($conditions as $field => $value) {
            $query->where($field, $value);
        }
        
        return $query->exists();
    }

    /**
     * Helper: Caricamento lazy con throttling
     */
    public function loadOptimized(array $relations): self
    {
        // Carica solo le relazioni non già caricate
        $toLoad = array_filter($relations, function($relation) {
            return !$this->relationLoaded($relation);
        });
        
        if (!empty($toLoad)) {
            $this->load($toLoad);
        }
        
        return $this;
    }
}
