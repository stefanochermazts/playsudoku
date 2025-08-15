<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Club;
use App\Models\ClubMember;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Servizio per gestire i club e le loro operazioni
 */
class ClubService
{
    /**
     * Crea un nuovo club
     */
    public function createClub(User $owner, array $data): Club
    {
        return DB::transaction(function () use ($owner, $data) {
            // Crea il club
            $club = Club::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'owner_id' => $owner->id,
                'visibility' => $data['visibility'] ?? Club::VISIBILITY_PUBLIC,
                'max_members' => $data['max_members'] ?? 50,
                'settings' => $data['settings'] ?? null,
            ]);

            // Aggiungi il proprietario come membro
            ClubMember::create([
                'club_id' => $club->id,
                'user_id' => $owner->id,
                'role' => ClubMember::ROLE_OWNER,
                'status' => ClubMember::STATUS_ACTIVE,
                'joined_at' => now(),
            ]);

            Log::info('Club created', [
                'club_id' => $club->id,
                'club_name' => $club->name,
                'owner_id' => $owner->id,
            ]);

            return $club->refresh();
        });
    }

    /**
     * Aggiorna un club
     */
    public function updateClub(Club $club, array $data): bool
    {
        $result = $club->update($data);

        if ($result) {
            Log::info('Club updated', [
                'club_id' => $club->id,
                'updated_fields' => array_keys($data),
            ]);
        }

        return $result;
    }

    /**
     * Elimina un club
     */
    public function deleteClub(Club $club): bool
    {
        $result = $club->delete();

        if ($result) {
            Log::info('Club deleted', [
                'club_id' => $club->id,
                'club_name' => $club->name,
            ]);
        }

        return $result;
    }

    /**
     * Invita un utente al club
     */
    public function inviteUser(Club $club, User $user, User $inviter, ?string $message = null): ClubMember
    {
        // Verifica che l'utente non sia già membro o invitato
        if ($club->isMember($user) || $club->hasPendingInvite($user)) {
            throw new \InvalidArgumentException('L\'utente è già membro o ha un invito pendente');
        }

        // Verifica che il club non sia pieno
        if ($club->isFull()) {
            throw new \InvalidArgumentException('Il club ha raggiunto il limite massimo di membri');
        }

        $membership = ClubMember::create([
            'club_id' => $club->id,
            'user_id' => $user->id,
            'role' => ClubMember::ROLE_MEMBER,
            'status' => ClubMember::STATUS_INVITED,
            'invited_at' => now(),
            'invited_by' => $inviter->id,
            'invite_message' => $message,
        ]);

        Log::info('User invited to club', [
            'club_id' => $club->id,
            'user_id' => $user->id,
            'inviter_id' => $inviter->id,
        ]);

        return $membership;
    }

    /**
     * Accetta un invito al club
     */
    public function acceptInvite(ClubMember $membership): bool
    {
        if (!$membership->isInvited()) {
            throw new \InvalidArgumentException('Nessun invito pendente da accettare');
        }

        $result = $membership->acceptInvite();

        if ($result) {
            Log::info('Club invite accepted', [
                'club_id' => $membership->club_id,
                'user_id' => $membership->user_id,
            ]);
        }

        return $result;
    }

    /**
     * Rifiuta un invito al club
     */
    public function declineInvite(ClubMember $membership): bool
    {
        if (!$membership->isInvited()) {
            throw new \InvalidArgumentException('Nessun invito pendente da rifiutare');
        }

        $result = $membership->declineInvite();

        if ($result) {
            Log::info('Club invite declined', [
                'club_id' => $membership->club_id,
                'user_id' => $membership->user_id,
            ]);
        }

        return $result;
    }

    /**
     * Unisciti a un club pubblico
     */
    public function joinClub(Club $club, User $user): ClubMember
    {
        // Verifica che il club sia pubblico
        if ($club->visibility !== Club::VISIBILITY_PUBLIC) {
            throw new \InvalidArgumentException('Questo club richiede un invito per unirsi');
        }

        // Verifica che l'utente non sia già membro
        if ($club->isMember($user)) {
            throw new \InvalidArgumentException('Sei già membro di questo club');
        }

        // Verifica che il club non sia pieno
        if ($club->isFull()) {
            throw new \InvalidArgumentException('Il club ha raggiunto il limite massimo di membri');
        }

        $membership = ClubMember::create([
            'club_id' => $club->id,
            'user_id' => $user->id,
            'role' => ClubMember::ROLE_MEMBER,
            'status' => ClubMember::STATUS_ACTIVE,
            'joined_at' => now(),
        ]);

        Log::info('User joined club', [
            'club_id' => $club->id,
            'user_id' => $user->id,
        ]);

        return $membership;
    }

    /**
     * Unisciti a un club tramite codice invito
     */
    public function joinClubByCode(string $inviteCode, User $user): ClubMember
    {
        $club = Club::where('invite_code', $inviteCode)->first();

        if (!$club) {
            throw new \InvalidArgumentException('Codice invito non valido');
        }

        // Verifica che l'utente non sia già membro
        if ($club->isMember($user)) {
            throw new \InvalidArgumentException('Sei già membro di questo club');
        }

        // Verifica che il club non sia pieno
        if ($club->isFull()) {
            throw new \InvalidArgumentException('Il club ha raggiunto il limite massimo di membri');
        }

        $membership = ClubMember::create([
            'club_id' => $club->id,
            'user_id' => $user->id,
            'role' => ClubMember::ROLE_MEMBER,
            'status' => ClubMember::STATUS_ACTIVE,
            'joined_at' => now(),
        ]);

        Log::info('User joined club by invite code', [
            'club_id' => $club->id,
            'user_id' => $user->id,
            'invite_code' => $inviteCode,
        ]);

        return $membership;
    }

    /**
     * Lascia un club
     */
    public function leaveClub(Club $club, User $user): bool
    {
        $membership = $club->memberships()
                          ->where('user_id', $user->id)
                          ->where('status', ClubMember::STATUS_ACTIVE)
                          ->first();

        if (!$membership) {
            throw new \InvalidArgumentException('Non sei membro di questo club');
        }

        // Il proprietario non può lasciare il club
        if ($membership->isOwner()) {
            throw new \InvalidArgumentException('Il proprietario non può lasciare il club. Trasferisci la proprietà prima.');
        }

        $result = $membership->delete();

        if ($result) {
            Log::info('User left club', [
                'club_id' => $club->id,
                'user_id' => $user->id,
            ]);
        }

        return $result;
    }

    /**
     * Rimuove un membro dal club
     */
    public function removeMember(Club $club, User $user): bool
    {
        $membership = $club->memberships()
                          ->where('user_id', $user->id)
                          ->where('status', ClubMember::STATUS_ACTIVE)
                          ->first();

        if (!$membership) {
            throw new \InvalidArgumentException('L\'utente non è membro di questo club');
        }

        // Non si può rimuovere il proprietario
        if ($membership->isOwner()) {
            throw new \InvalidArgumentException('Non è possibile rimuovere il proprietario del club');
        }

        $result = $membership->delete();

        if ($result) {
            Log::info('Member removed from club', [
                'club_id' => $club->id,
                'user_id' => $user->id,
            ]);
        }

        return $result;
    }

    /**
     * Cambia il ruolo di un membro
     */
    public function changeRole(ClubMember $membership, string $newRole): bool
    {
        // Non si può cambiare il ruolo del proprietario
        if ($membership->isOwner()) {
            throw new \InvalidArgumentException('Non è possibile modificare il ruolo del proprietario');
        }

        // Verifica che il nuovo ruolo sia valido
        if (!in_array($newRole, [ClubMember::ROLE_ADMIN, ClubMember::ROLE_MEMBER])) {
            throw new \InvalidArgumentException('Ruolo non valido');
        }

        $oldRole = $membership->role;
        $result = $membership->update(['role' => $newRole]);

        if ($result) {
            Log::info('Member role changed', [
                'club_id' => $membership->club_id,
                'user_id' => $membership->user_id,
                'old_role' => $oldRole,
                'new_role' => $newRole,
            ]);
        }

        return $result;
    }

    /**
     * Trasferisce la proprietà del club
     */
    public function transferOwnership(Club $club, User $newOwner): bool
    {
        return DB::transaction(function () use ($club, $newOwner) {
            // Verifica che il nuovo proprietario sia membro del club
            $newOwnerMembership = $club->memberships()
                                      ->where('user_id', $newOwner->id)
                                      ->where('status', ClubMember::STATUS_ACTIVE)
                                      ->first();

            if (!$newOwnerMembership) {
                throw new \InvalidArgumentException('Il nuovo proprietario deve essere membro del club');
            }

            // Cambia il ruolo del proprietario attuale in admin
            $currentOwnerMembership = $club->memberships()
                                          ->where('role', ClubMember::ROLE_OWNER)
                                          ->first();

            if ($currentOwnerMembership) {
                $currentOwnerMembership->update(['role' => ClubMember::ROLE_ADMIN]);
            }

            // Cambia il ruolo del nuovo proprietario
            $newOwnerMembership->update(['role' => ClubMember::ROLE_OWNER]);

            // Aggiorna il club
            $club->update(['owner_id' => $newOwner->id]);

            Log::info('Club ownership transferred', [
                'club_id' => $club->id,
                'old_owner_id' => $currentOwnerMembership?->user_id,
                'new_owner_id' => $newOwner->id,
            ]);

            return true;
        });
    }

    /**
     * Cerca club pubblici
     */
    public function searchPublicClubs(string $search, int $limit = 20): Collection
    {
        return Club::public()
                   ->active()
                   ->where(function ($query) use ($search) {
                       $query->where('name', 'ILIKE', "%{$search}%")
                             ->orWhere('description', 'ILIKE', "%{$search}%");
                   })
                   ->with(['owner', 'activeMembers'])
                   ->orderBy('created_at', 'desc')
                   ->limit($limit)
                   ->get();
    }

    /**
     * Ottiene i club suggeriti per un utente
     */
    public function getSuggestedClubs(User $user, int $limit = 10): Collection
    {
        // Ottieni gli ID dei club di cui è già membro
        $memberClubIds = $user->clubs()->pluck('clubs.id')->toArray();

        // Ottieni gli ID degli amici
        $friendIds = $user->friends()->pluck('id')->toArray();

        // Suggerisci club dove gli amici sono membri
        return Club::public()
                   ->active()
                   ->whereNotIn('id', $memberClubIds)
                   ->whereHas('members', function ($query) use ($friendIds) {
                       $query->whereIn('users.id', $friendIds);
                   })
                   ->with(['owner', 'activeMembers'])
                   ->orderBy('created_at', 'desc')
                   ->limit($limit)
                   ->get();
    }

    /**
     * Rigenera il codice invito del club
     */
    public function regenerateInviteCode(Club $club): string
    {
        $newCode = $club->generateInviteCode();
        $club->update(['invite_code' => $newCode]);

        Log::info('Club invite code regenerated', [
            'club_id' => $club->id,
            'new_code' => $newCode,
        ]);

        return $newCode;
    }

    /**
     * Ottiene statistiche del club
     */
    public function getClubStats(Club $club): array
    {
        return [
            'total_members' => $club->members_count,
            'admins_count' => $club->memberships()->where('role', ClubMember::ROLE_ADMIN)->count(),
            'pending_invites' => $club->memberships()->where('status', ClubMember::STATUS_INVITED)->count(),
            'recent_joins' => $club->memberships()
                                  ->where('status', ClubMember::STATUS_ACTIVE)
                                  ->where('joined_at', '>=', now()->subDays(30))
                                  ->count(),
        ];
    }
}
