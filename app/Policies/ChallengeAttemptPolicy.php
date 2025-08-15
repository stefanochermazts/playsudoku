<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\ChallengeAttempt;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Policy per le autorizzazioni sui tentativi di sfida (moderazione)
 */
class ChallengeAttemptPolicy
{
    /**
     * Determina se l'utente può visualizzare qualsiasi tentativo
     */
    public function viewAny(User $user): bool
    {
        // Solo admin possono vedere tutti i tentativi
        return $user->isAdmin();
    }

    /**
     * Determina se l'utente può visualizzare un specifico tentativo
     */
    public function view(User $user, ChallengeAttempt $attempt): bool
    {
        // L'utente può vedere i propri tentativi
        if ($attempt->user_id === $user->id) {
            return true;
        }
        
        // Admin possono vedere tutti i tentativi
        return $user->isAdmin();
    }

    /**
     * Determina se l'utente può creare tentativi
     */
    public function create(User $user): bool
    {
        // Tutti gli utenti autenticati possono creare tentativi
        return true;
    }

    /**
     * Determina se l'utente può aggiornare il tentativo
     */
    public function update(User $user, ChallengeAttempt $attempt): bool
    {
        // L'utente può aggiornare solo i propri tentativi non completati
        if ($attempt->user_id === $user->id && !$attempt->completed_at) {
            return true;
        }
        
        // Admin possono sempre aggiornare per moderazione
        return $user->isAdmin();
    }

    /**
     * Determina se l'utente può eliminare il tentativo
     */
    public function delete(User $user, ChallengeAttempt $attempt): bool
    {
        // Solo super admin possono eliminare tentativi
        return $user->hasRole('super_admin');
    }

    /**
     * Determina se l'utente può moderare (flaggare/approvare) tentativi
     */
    public function moderate(User $user, ChallengeAttempt $attempt): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determina se l'utente può flaggare un tentativo per revisione
     */
    public function flag(User $user, ChallengeAttempt $attempt): bool
    {
        // Admin possono flaggare qualsiasi tentativo
        if ($user->isAdmin()) {
            return true;
        }
        
        // Gli utenti possono segnalare tentativi sospetti di altri utenti
        return $attempt->user_id !== $user->id;
    }

    /**
     * Determina se l'utente può approvare/respingere tentativi flaggati
     */
    public function review(User $user, ChallengeAttempt $attempt): bool
    {
        // Solo admin possono revisionare tentativi flaggati
        if (!$user->isAdmin()) {
            return false;
        }
        
        // Non si può auto-revisionare
        if ($attempt->reviewed_by === $user->id) {
            return false;
        }
        
        return true;
    }

    /**
     * Determina se l'utente può invalidare un tentativo
     */
    public function invalidate(User $user, ChallengeAttempt $attempt): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determina se l'utente può vedere i dettagli di moderazione
     */
    public function viewModerationDetails(User $user, ChallengeAttempt $attempt): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determina se l'utente può vedere le mosse dettagliate
     */
    public function viewMoves(User $user, ChallengeAttempt $attempt): bool
    {
        // L'utente può vedere le proprie mosse
        if ($attempt->user_id === $user->id) {
            return true;
        }
        
        // Admin possono vedere tutte le mosse per moderazione
        return $user->isAdmin();
    }

    /**
     * Determina se l'utente può esportare dati del tentativo
     */
    public function export(User $user, ChallengeAttempt $attempt): bool
    {
        // L'utente può esportare i propri dati
        if ($attempt->user_id === $user->id) {
            return true;
        }
        
        // Admin possono esportare per analisi
        return $user->isAdmin();
    }

    /**
     * Determina se l'utente può ripristinare un tentativo
     */
    public function restore(User $user, ChallengeAttempt $attempt): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determina se l'utente può eliminare permanentemente il tentativo
     */
    public function forceDelete(User $user, ChallengeAttempt $attempt): bool
    {
        return $user->hasRole('super_admin');
    }
}