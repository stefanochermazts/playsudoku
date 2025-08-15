<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\Challenge;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Policy per le autorizzazioni delle sfide Sudoku
 */
class ChallengePolicy
{
    /**
     * Determina se l'utente può visualizzare qualsiasi sfida
     */
    public function viewAny(User $user): bool
    {
        // Tutti gli utenti autenticati possono visualizzare le sfide pubbliche
        return true;
    }

    /**
     * Determina se l'utente può visualizzare una specifica sfida
     */
    public function view(User $user, Challenge $challenge): bool
    {
        // Tutti possono vedere sfide pubbliche attive
        if ($challenge->visibility === 'public' && $challenge->status === 'active') {
            return true;
        }
        
        // Solo admin per sfide private o in draft
        return $user->isAdmin();
    }

    /**
     * Determina se l'utente può creare sfide
     */
    public function create(User $user): bool
    {
        // Solo admin possono creare sfide
        return $user->isAdmin();
    }

    /**
     * Determina se l'utente può creare sfide custom pubbliche
     */
    public function createPublic(User $user): bool
    {
        // Solo super admin possono creare sfide custom pubbliche
        return $user->isAdmin() && $user->hasRole('super_admin');
    }

    /**
     * Determina se l'utente può aggiornare la sfida
     */
    public function update(User $user, Challenge $challenge): bool
    {
        // Solo admin possono modificare sfide
        if (!$user->isAdmin()) {
            return false;
        }
        
        // Non si possono modificare sfide già iniziate (con tentativi)
        if ($challenge->attempts()->exists()) {
            return $user->hasRole('super_admin');
        }
        
        return true;
    }

    /**
     * Determina se l'utente può sospendere/riattivare la sfida
     */
    public function suspend(User $user, Challenge $challenge): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determina se l'utente può estendere la deadline
     */
    public function extend(User $user, Challenge $challenge): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determina se l'utente può eliminare la sfida
     */
    public function delete(User $user, Challenge $challenge): bool
    {
        // Solo super admin possono eliminare sfide
        if (!$user->hasRole('super_admin')) {
            return false;
        }
        
        // Non si possono eliminare sfide con tentativi completati
        if ($challenge->attempts()->whereNotNull('completed_at')->exists()) {
            return false;
        }
        
        return true;
    }

    /**
     * Determina se l'utente può ripristinare la sfida
     */
    public function restore(User $user, Challenge $challenge): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determina se l'utente può eliminare permanentemente la sfida
     */
    public function forceDelete(User $user, Challenge $challenge): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determina se l'utente può vedere le statistiche dettagliate
     */
    public function viewStatistics(User $user, Challenge $challenge): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determina se l'utente può esportare i risultati
     */
    public function export(User $user, Challenge $challenge): bool
    {
        return $user->isAdmin();
    }
}