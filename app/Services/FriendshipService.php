<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Friendship;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Servizio per gestire le amicizie tra utenti
 */
class FriendshipService
{
    /**
     * Invia una richiesta di amicizia
     */
    public function sendFriendRequest(User $user, User $friend, ?string $message = null): Friendship
    {
        // Verifica che non sia una richiesta a se stesso
        if ($user->id === $friend->id) {
            throw new \InvalidArgumentException('Un utente non può inviare una richiesta di amicizia a se stesso');
        }

        // Verifica che non esista già una relazione
        if ($user->hasPendingFriendRequestWith($friend) || $user->isFriendWith($friend)) {
            throw new \InvalidArgumentException('Esiste già una relazione tra questi utenti');
        }

        // Verifica che l'utente non sia bloccato
        if ($user->isBlockedBy($friend) || $friend->hasBlocked($user)) {
            throw new \InvalidArgumentException('Non è possibile inviare richieste a questo utente');
        }

        $friendship = Friendship::create([
            'user_id' => $user->id,
            'friend_id' => $friend->id,
            'status' => Friendship::STATUS_PENDING,
            'message' => $message,
        ]);

        Log::info('Friend request sent', [
            'from_user' => $user->id,
            'to_user' => $friend->id,
            'friendship_id' => $friendship->id,
        ]);

        return $friendship;
    }

    /**
     * Accetta una richiesta di amicizia
     */
    public function acceptFriendRequest(Friendship $friendship): bool
    {
        if (!$friendship->isPending()) {
            throw new \InvalidArgumentException('La richiesta non è in stato pending');
        }

        $result = $friendship->accept();

        if ($result) {
            Log::info('Friend request accepted', [
                'friendship_id' => $friendship->id,
                'user_id' => $friendship->user_id,
                'friend_id' => $friendship->friend_id,
            ]);
        }

        return $result;
    }

    /**
     * Rifiuta una richiesta di amicizia
     */
    public function declineFriendRequest(Friendship $friendship): bool
    {
        if (!$friendship->isPending()) {
            throw new \InvalidArgumentException('La richiesta non è in stato pending');
        }

        $result = $friendship->decline();

        if ($result) {
            Log::info('Friend request declined', [
                'friendship_id' => $friendship->id,
                'user_id' => $friendship->user_id,
                'friend_id' => $friendship->friend_id,
            ]);
        }

        return $result;
    }

    /**
     * Blocca un utente
     */
    public function blockUser(User $user, User $userToBlock): Friendship
    {
        // Cerca una relazione esistente
        $friendship = Friendship::where(function ($query) use ($user, $userToBlock) {
            $query->where('user_id', $user->id)
                  ->where('friend_id', $userToBlock->id);
        })->orWhere(function ($query) use ($user, $userToBlock) {
            $query->where('user_id', $userToBlock->id)
                  ->where('friend_id', $user->id);
        })->first();

        if ($friendship) {
            // Aggiorna la relazione esistente
            $friendship->update(['status' => Friendship::STATUS_BLOCKED]);
        } else {
            // Crea una nuova relazione di blocco
            $friendship = Friendship::create([
                'user_id' => $user->id,
                'friend_id' => $userToBlock->id,
                'status' => Friendship::STATUS_BLOCKED,
            ]);
        }

        Log::info('User blocked', [
            'blocker_id' => $user->id,
            'blocked_id' => $userToBlock->id,
            'friendship_id' => $friendship->id,
        ]);

        return $friendship;
    }

    /**
     * Rimuove un'amicizia (unfriend)
     */
    public function removeFriendship(User $user, User $friend): bool
    {
        $friendship = Friendship::where(function ($query) use ($user, $friend) {
            $query->where('user_id', $user->id)
                  ->where('friend_id', $friend->id);
        })->orWhere(function ($query) use ($user, $friend) {
            $query->where('user_id', $friend->id)
                  ->where('friend_id', $user->id);
        })->first();

        if (!$friendship) {
            throw new \InvalidArgumentException('Nessuna amicizia trovata tra questi utenti');
        }

        $result = $friendship->delete();

        if ($result) {
            Log::info('Friendship removed', [
                'user1_id' => $user->id,
                'user2_id' => $friend->id,
                'friendship_id' => $friendship->id,
            ]);
        }

        return $result;
    }

    /**
     * Ottiene gli amici comuni tra due utenti
     */
    public function getMutualFriends(User $user1, User $user2): Collection
    {
        $user1Friends = $user1->friends()->pluck('id');
        $user2Friends = $user2->friends()->pluck('id');

        $mutualFriendIds = $user1Friends->intersect($user2Friends);

        return User::whereIn('id', $mutualFriendIds)->get();
    }

    /**
     * Suggerisce amici basandosi su utenti non ancora connessi
     */
    public function getFriendSuggestions(User $user, int $limit = 10): Collection
    {
        // STEP 1: Ottieni tutti gli ID degli utenti con cui ha qualsiasi tipo di relazione
        $allRelatedUserIds = Friendship::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->orWhere('friend_id', $user->id);
        })
        ->get()
        ->map(function ($friendship) use ($user) {
            // Restituisci l'ID dell'altro utente (non se stesso)
            return $friendship->user_id === $user->id ? $friendship->friend_id : $friendship->user_id;
        })
        ->unique()
        ->toArray();

        // STEP 2: Lista di esclusione completa (se stesso + qualsiasi relazione esistente)
        $excludeIds = array_merge([$user->id], $allRelatedUserIds);
        $excludeIds = array_unique($excludeIds);

        // STEP 3: Suggerisci utenti che non hanno NESSUNA relazione con l'utente corrente
        // e che accettano richieste di amicizia
        return User::whereNotIn('id', $excludeIds)
            // 🔐 PRIVACY: Solo utenti che accettano richieste di amicizia
            ->where('friend_requests_enabled', true)
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    /**
     * Cerca utenti per username/email (escludendo amici e bloccati)
     */
    public function searchUsers(User $currentUser, string $search, int $limit = 20): Collection
    {
        // Ottieni gli ID da escludere
        $excludeIds = [$currentUser->id];
        
        // Escludi amici esistenti
        $friendIds = $currentUser->friends()->pluck('id')->toArray();
        $excludeIds = array_merge($excludeIds, $friendIds);

        // Escludi utenti con richieste pendenti (in entrambe le direzioni)
        $pendingRequestsIds = Friendship::where(function ($query) use ($currentUser) {
            $query->where('user_id', $currentUser->id)
                  ->orWhere('friend_id', $currentUser->id);
        })->where('status', Friendship::STATUS_PENDING)
        ->get()
        ->map(function ($friendship) use ($currentUser) {
            return $friendship->user_id === $currentUser->id ? $friendship->friend_id : $friendship->user_id;
        })
        ->toArray();
        
        $excludeIds = array_merge($excludeIds, $pendingRequestsIds);

        // Escludi utenti bloccati
        $blockedIds = Friendship::where(function ($query) use ($currentUser) {
            $query->where('user_id', $currentUser->id)
                  ->orWhere('friend_id', $currentUser->id);
        })->where('status', Friendship::STATUS_BLOCKED)
        ->get()
        ->map(function ($friendship) use ($currentUser) {
            return $friendship->user_id === $currentUser->id ? $friendship->friend_id : $friendship->user_id;
        })
        ->toArray();
        
        $excludeIds = array_merge($excludeIds, $blockedIds);

        return User::whereNotIn('id', array_unique($excludeIds))
            ->where(function ($query) use ($search) {
                $query->where('name', 'ILIKE', "%{$search}%")
                      ->orWhere('email', 'ILIKE', "%{$search}%");
            })
            // 🔐 PRIVACY: Solo utenti che accettano richieste di amicizia
            ->where('friend_requests_enabled', true)
            ->limit($limit)
            ->get();
    }

    /**
     * Ottiene statistiche amicizie per un utente
     */
    public function getFriendshipStats(User $user): array
    {
        return [
            'total_friends' => $user->friends_count,
            'pending_requests' => $user->receivedFriendRequests()->pending()->count(),
            'sent_requests' => $user->sentFriendRequests()->pending()->count(),
            'blocked_users' => $user->sentFriendRequests()->blocked()->count(),
        ];
    }
}
