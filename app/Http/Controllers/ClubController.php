<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\ClubMember;
use App\Models\User;
use App\Services\ClubService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ClubController extends Controller
{
    public function __construct(
        private ClubService $clubService
    ) {
        // Authentication handled by route middleware
    }

    /**
     * Mostra la lista dei club
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $myClubs = $user->clubs()->with(['owner', 'activeMembers'])->get();
        $invites = $user->clubInvites()->with(['owner', 'activeMembers'])->get();
        $suggestedClubs = $this->clubService->getSuggestedClubs($user, 6);
        
        // Ottieni club pubblici per l'esplorazione
        $search = $request->get('search');
        $myClubIds = $myClubs->pluck('id')->toArray(); // Converti in array per evitare ambiguità SQL
        $publicClubsQuery = Club::where('visibility', Club::VISIBILITY_PUBLIC)
            ->where('is_active', true)
            ->whereNotIn('clubs.id', $myClubIds) // Specifica la tabella per evitare ambiguità
            ->with(['owner', 'activeMembers']);
            
        if ($search) {
            $publicClubsQuery->where(function($query) use ($search) {
                $query->where('name', 'ILIKE', "%{$search}%")
                      ->orWhere('description', 'ILIKE', "%{$search}%");
            });
        }
        
        $publicClubs = $publicClubsQuery->orderBy('created_at', 'desc')->limit(12)->get();
        
        // Aggiungi informazioni sul membership per ogni club pubblico
        $publicClubs->each(function($club) use ($user) {
            $club->canJoin = !$club->memberships()->where('club_members.user_id', $user->id)->exists() &&
                           (!$club->max_members || $club->activeMembers()->count() < $club->max_members);
        });

        return view('clubs.index', compact('myClubs', 'invites', 'suggestedClubs', 'publicClubs', 'search'));
    }

    /**
     * Esplora club pubblici (pagina dedicata)
     */
    public function explore(Request $request): View
    {
        $user = Auth::user();
        $search = $request->get('search');
        $sort = $request->get('sort', 'newest'); // newest, oldest, members, name
        
        $myClubIds = $user->clubs()->pluck('clubs.id')->toArray(); // Specifica clubs.id per evitare ambiguità
        $clubsQuery = Club::where('visibility', Club::VISIBILITY_PUBLIC)
            ->where('is_active', true)
            ->whereNotIn('clubs.id', $myClubIds) // Specifica la tabella
            ->with(['owner', 'activeMembers']);
            
        if ($search) {
            $clubsQuery->where(function($query) use ($search) {
                $query->where('name', 'ILIKE', "%{$search}%")
                      ->orWhere('description', 'ILIKE', "%{$search}%");
            });
        }
        
        // Ordinamento
        switch ($sort) {
            case 'oldest':
                $clubsQuery->orderBy('created_at', 'asc');
                break;
            case 'members':
                $clubsQuery->withCount('activeMembers')->orderBy('active_members_count', 'desc');
                break;
            case 'name':
                $clubsQuery->orderBy('name', 'asc');
                break;
            default: // newest
                $clubsQuery->orderBy('created_at', 'desc');
                break;
        }
        
        $clubs = $clubsQuery->withCount('activeMembers')->paginate(12)->withQueryString();
        
        // Aggiungi informazioni sul membership
        $clubs->getCollection()->each(function($club) use ($user) {
            $club->canJoin = !$club->memberships()->where('club_members.user_id', $user->id)->exists() &&
                           (!$club->max_members || $club->activeMembers_count < $club->max_members);
        });
        
        return view('clubs.explore', compact('clubs', 'search', 'sort'));
    }

    /**
     * Mostra il form per creare un club
     */
    public function create(): View
    {
        return view('clubs.create');
    }

    /**
     * Crea un nuovo club
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:clubs,name',
            'description' => 'nullable|string|max:1000',
            'visibility' => ['required', Rule::in([Club::VISIBILITY_PUBLIC, Club::VISIBILITY_PRIVATE, Club::VISIBILITY_INVITE_ONLY])],
            'max_members' => 'nullable|integer|min:2|max:200',
        ]);

        try {
            $club = $this->clubService->createClub(Auth::user(), $request->only([
                'name', 'description', 'visibility', 'max_members'
            ]));

            return redirect()
                ->route('localized.clubs.show', ['locale' => app()->getLocale(), 'club' => $club->slug])
                ->with('success', __('app.clubs.created_successfully'));

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => __('app.clubs.creation_error')]);
        }
    }

    /**
     * Mostra un club specifico
     */
    public function show($locale, $club): View
    {
        // $club contiene lo slug, risolvi il modello
        $clubModel = Club::where('slug', $club)->firstOrFail();
        
        $user = Auth::user();
        $membership = $clubModel->memberships()->where('club_members.user_id', $user->id)->first();
        $canView = $clubModel->visibility === Club::VISIBILITY_PUBLIC || 
                   $membership || 
                   $clubModel->isOwner($user);

        if (!$canView) {
            abort(403, 'Non hai accesso a questo club privato');
        }

        $clubModel->load(['owner', 'activeMembers.user', 'invitedMembers.user']);
        $stats = $this->clubService->getClubStats($clubModel);

        // Determina le azioni disponibili per l'utente
        $canJoin = !$membership && $clubModel->visibility === Club::VISIBILITY_PUBLIC;
        $canInvite = $membership && ($clubModel->isOwner($user) || $membership->role === 'admin');
        $canLeave = $membership && !$clubModel->isOwner($user);
        $canManage = $clubModel->isOwner($user);

        return view('clubs.show', [
            'club' => $clubModel,
            'membership' => $membership,
            'stats' => $stats,
            'canJoin' => $canJoin,
            'canInvite' => $canInvite,
            'canLeave' => $canLeave,
            'canManage' => $canManage,
        ]);
    }

    /**
     * Mostra il form per modificare un club
     */
    public function edit($locale, $club): View
    {
        // $club contiene lo slug, risolvi il modello
        $clubModel = Club::where('slug', $club)->firstOrFail();
        
        $user = Auth::user();
        
        // Solo il proprietario può modificare il club
        if (!$clubModel->isOwner($user)) {
            abort(403, 'Solo il proprietario può modificare questo club');
        }

        return view('clubs.edit', ['club' => $clubModel]);
    }

    /**
     * Aggiorna un club
     */
    public function update(Request $request, $locale, $club): RedirectResponse
    {
        // $club contiene lo slug, risolvi il modello
        $clubModel = Club::where('slug', $club)->firstOrFail();
        
        $user = Auth::user();
        
        // Solo il proprietario può modificare il club
        if (!$clubModel->isOwner($user)) {
            abort(403, 'Solo il proprietario può modificare questo club');
        }

        $request->validate([
            'name' => 'required|string|max:100|unique:clubs,name,' . $clubModel->id,
            'description' => 'nullable|string|max:1000',
            'visibility' => ['required', Rule::in([Club::VISIBILITY_PUBLIC, Club::VISIBILITY_PRIVATE, Club::VISIBILITY_INVITE_ONLY])],
            'max_members' => 'nullable|integer|min:2|max:200',
        ]);

        try {
            $clubModel->update($request->only([
                'name', 'description', 'visibility', 'max_members'
            ]));

            return redirect()
                ->route('localized.clubs.show', ['locale' => app()->getLocale(), 'club' => $clubModel->slug])
                ->with('success', __('app.clubs.updated_successfully'));

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => __('app.clubs.update_error')]);
        }
    }

    /**
     * Unisciti a un club pubblico
     */
    public function joinClub($locale, $club): RedirectResponse
    {
        $clubModel = Club::where('slug', $club)->firstOrFail();
        $user = Auth::user();
        
        try {
            // Verifica che l'utente possa unirsi
            if ($clubModel->visibility !== Club::VISIBILITY_PUBLIC) {
                return back()->withErrors(['error' => __('app.clubs.cannot_join_private')]);
            }
            
            // Verifica che non sia già membro
            $existingMembership = $clubModel->memberships()->where('club_members.user_id', $user->id)->first();
            if ($existingMembership) {
                return back()->withErrors(['error' => __('app.clubs.already_member')]);
            }
            
            // Verifica limite membri
            if ($clubModel->max_members && $clubModel->activeMembers()->count() >= $clubModel->max_members) {
                return back()->withErrors(['error' => __('app.clubs.club_full')]);
            }
            
            // Crea membership
            $this->clubService->joinClub($clubModel, $user);
            
            return back()->with('success', __('app.clubs.joined_successfully'));
            
        } catch (\Exception $e) {
            return back()->withErrors(['error' => __('app.clubs.join_error')]);
        }
    }
    
    /**
     * Lascia un club
     */
    public function leaveClub($locale, $club): RedirectResponse
    {
        $clubModel = Club::where('slug', $club)->firstOrFail();
        $user = Auth::user();
        
        try {
            // Verifica che l'utente sia membro
            $membership = $clubModel->memberships()->where('club_members.user_id', $user->id)->first();
            if (!$membership) {
                return back()->withErrors(['error' => __('app.clubs.not_member')]);
            }
            
            // Il proprietario non può lasciare il club
            if ($clubModel->isOwner($user)) {
                return back()->withErrors(['error' => __('app.clubs.owner_cannot_leave')]);
            }
            
            // Rimuovi membership
            $membership->delete();
            
            return redirect()
                ->route('localized.clubs.index', ['locale' => $locale])
                ->with('success', __('app.clubs.left_successfully'));
                
        } catch (\Exception $e) {
            return back()->withErrors(['error' => __('app.clubs.leave_error')]);
        }
    }
    
    /**
     * Mostra il form per invitare amici al club
     */
    public function showInviteForm($locale, $club): View
    {
        $clubModel = Club::where('slug', $club)->firstOrFail();
        $user = Auth::user();
        
        // Verifica che l'utente possa invitare
        $membership = $clubModel->memberships()->where('club_members.user_id', $user->id)->first();
        if (!$membership || (!$clubModel->isOwner($user) && $membership->role !== 'admin')) {
            abort(403, __('app.clubs.cannot_invite'));
        }
        
        // Ottieni lista amici che non sono nel club
        $friends = $user->friends()
            ->whereNotIn('id', $clubModel->members()->pluck('id'))
            ->get();
            
        // Ottieni inviti pendenti
        $pendingInvites = $clubModel->invitedMembers()->with('user')->get();
        
        return view('clubs.invite', [
            'club' => $clubModel,
            'friends' => $friends,
            'pendingInvites' => $pendingInvites,
        ]);
    }
    
    /**
     * Invia inviti al club
     */
    public function sendInvites(Request $request, $locale, $club): RedirectResponse
    {
        $clubModel = Club::where('slug', $club)->firstOrFail();
        $user = Auth::user();
        
        // Verifica che l'utente possa invitare
        $membership = $clubModel->memberships()->where('club_members.user_id', $user->id)->first();
        if (!$membership || (!$clubModel->isOwner($user) && $membership->role !== 'admin')) {
            abort(403, __('app.clubs.cannot_invite'));
        }
        
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);
        
        try {
            $invitedCount = 0;
            
            foreach ($request->user_ids as $userId) {
                // Verifica che l'utente non sia già membro o invitato
                $existingMembership = $clubModel->memberships()->where('club_members.user_id', $userId)->first();
                if ($existingMembership) {
                    continue;
                }
                
                // Crea invito
                $clubModel->memberships()->create([
                    'user_id' => $userId,
                    'role' => 'member',
                    'status' => 'invited',
                    'invited_by' => $user->id,
                    'invited_at' => now(),
                ]);
                
                $invitedCount++;
            }
            
            return back()->with('success', __('app.clubs.invites_sent', ['count' => $invitedCount]));
            
        } catch (\Exception $e) {
            return back()->withErrors(['error' => __('app.clubs.invite_error')]);
        }
    }
    
    /**
     * Accetta un invito al club
     */
    public function acceptInvite($locale, $club, $membershipId): RedirectResponse
    {
        $clubModel = Club::where('slug', $club)->firstOrFail();
        $user = Auth::user();
        
        try {
            $membership = $clubModel->memberships()
                ->where('id', $membershipId)
                ->where('user_id', $user->id)
                ->where('status', 'invited')
                ->firstOrFail();
            
            // Verifica limite membri
            if ($clubModel->max_members && $clubModel->activeMembers()->count() >= $clubModel->max_members) {
                return back()->withErrors(['error' => __('app.clubs.club_full')]);
            }
            
            // Accetta invito
            $membership->update([
                'status' => 'active',
                'joined_at' => now(),
            ]);
            
            return redirect()
                ->route('localized.clubs.show', ['locale' => $locale, 'club' => $clubModel->slug])
                ->with('success', __('app.clubs.invite_accepted'));
                
        } catch (\Exception $e) {
            return back()->withErrors(['error' => __('app.clubs.invite_accept_error')]);
        }
    }
    
    /**
     * Rifiuta un invito al club
     */
    public function declineInvite($locale, $club, $membershipId): RedirectResponse
    {
        $clubModel = Club::where('slug', $club)->firstOrFail();
        $user = Auth::user();
        
        try {
            $membership = $clubModel->memberships()
                ->where('id', $membershipId)
                ->where('user_id', $user->id)
                ->where('status', 'invited')
                ->firstOrFail();
            
            // Rimuovi invito
            $membership->delete();
            
            return back()->with('success', __('app.clubs.invite_declined'));
            
        } catch (\Exception $e) {
            return back()->withErrors(['error' => __('app.clubs.invite_decline_error')]);
        }
    }

    /**
     * Unisciti a un club pubblico (API)
     */
    public function join(Club $club): JsonResponse
    {
        try {
            $membership = $this->clubService->joinClub($club, Auth::user());

            return response()->json([
                'success' => true,
                'message' => __('app.clubs.joined_successfully'),
                'membership_id' => $membership->id,
            ]);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('app.clubs.join_error'),
            ], 500);
        }
    }


}