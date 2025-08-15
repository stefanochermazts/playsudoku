<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Policy per le autorizzazioni sugli utenti
 */
class UserPolicy
{
    /**
     * Determina se l'utente può visualizzare qualsiasi utente
     */
    public function viewAny(User $user): bool
    {
        // Solo admin possono vedere la lista utenti
        return $user->isAdmin();
    }

    /**
     * Determina se l'utente può visualizzare un specifico utente
     */
    public function view(User $user, User $targetUser): bool
    {
        // L'utente può vedere il proprio profilo
        if ($user->id === $targetUser->id) {
            return true;
        }
        
        // Admin possono vedere tutti i profili
        return $user->isAdmin();
    }

    /**
     * Determina se l'utente può creare nuovi utenti
     */
    public function create(User $user): bool
    {
        // Solo super admin possono creare utenti direttamente
        return $user->hasRole('super_admin');
    }

    /**
     * Determina se l'utente può aggiornare un utente
     */
    public function update(User $user, User $targetUser): bool
    {
        // L'utente può aggiornare il proprio profilo
        if ($user->id === $targetUser->id) {
            return true;
        }
        
        // Admin possono aggiornare altri utenti
        if ($user->isAdmin()) {
            // Super admin non possono essere modificati da admin normali
            if ($targetUser->hasRole('super_admin') && !$user->hasRole('super_admin')) {
                return false;
            }
            return true;
        }
        
        return false;
    }

    /**
     * Determina se l'utente può eliminare un utente
     */
    public function delete(User $user, User $targetUser): bool
    {
        // Solo super admin possono eliminare utenti
        if (!$user->hasRole('super_admin')) {
            return false;
        }
        
        // Non si può auto-eliminare
        if ($user->id === $targetUser->id) {
            return false;
        }
        
        return true;
    }

    /**
     * Determina se l'utente può bannare/sbannare altri utenti
     */
    public function ban(User $user, User $targetUser): bool
    {
        // Solo admin possono bannare
        if (!$user->isAdmin()) {
            return false;
        }
        
        // Non si può auto-bannare
        if ($user->id === $targetUser->id) {
            return false;
        }
        
        // Admin normali non possono bannare super admin
        if ($targetUser->hasRole('super_admin') && !$user->hasRole('super_admin')) {
            return false;
        }
        
        return true;
    }

    /**
     * Determina se l'utente può cambiare i ruoli
     */
    public function changeRole(User $user, User $targetUser): bool
    {
        // Solo super admin possono cambiare ruoli
        if (!$user->hasRole('super_admin')) {
            return false;
        }
        
        // Non si può cambiare il proprio ruolo
        if ($user->id === $targetUser->id) {
            return false;
        }
        
        return true;
    }

    /**
     * Determina se l'utente può vedere le statistiche dettagliate
     */
    public function viewStatistics(User $user, User $targetUser): bool
    {
        // L'utente può vedere le proprie statistiche
        if ($user->id === $targetUser->id) {
            return true;
        }
        
        // Admin possono vedere tutte le statistiche
        return $user->isAdmin();
    }

    /**
     * Determina se l'utente può vedere i tentativi di sfida
     */
    public function viewAttempts(User $user, User $targetUser): bool
    {
        // L'utente può vedere i propri tentativi
        if ($user->id === $targetUser->id) {
            return true;
        }
        
        // Admin possono vedere tutti i tentativi per moderazione
        return $user->isAdmin();
    }

    /**
     * Determina se l'utente può inviare notifiche ad altri utenti
     */
    public function notify(User $user, User $targetUser): bool
    {
        // Solo admin possono inviare notifiche dirette
        return $user->isAdmin();
    }

    /**
     * Determina se l'utente può esportare dati utente
     */
    public function export(User $user, User $targetUser): bool
    {
        // L'utente può esportare i propri dati (GDPR)
        if ($user->id === $targetUser->id) {
            return true;
        }
        
        // Admin possono esportare per analisi
        return $user->isAdmin();
    }

    /**
     * Determina se l'utente può ripristinare un utente eliminato
     */
    public function restore(User $user, User $targetUser): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determina se l'utente può eliminare permanentemente un utente
     */
    public function forceDelete(User $user, User $targetUser): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determina se l'utente può accedere al pannello admin
     */
    public function accessAdmin(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determina se l'utente può vedere log di audit
     */
    public function viewAuditLogs(User $user): bool
    {
        return $user->isAdmin();
    }
}