<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Friendship;
use App\Services\FriendshipService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class FriendshipController extends Controller
{
    public function __construct(
        private FriendshipService $friendshipService
    ) {
        // Authentication handled by route middleware
    }

    /**
     * Mostra la pagina degli amici
     */
    public function index(): View
    {
        $user = Auth::user();
        $friends = $user->friends();
        $pendingRequests = $user->pendingFriendRequests();
        $suggestions = $this->friendshipService->getFriendSuggestions($user, 8);
        $stats = $this->friendshipService->getFriendshipStats($user);

        // Calcola amici in comune per ogni suggestion
        $suggestions = $suggestions->map(function ($suggestion) use ($user) {
            $mutualFriends = $this->friendshipService->getMutualFriends($user, $suggestion);
            $suggestion->mutual_friends_count = $mutualFriends->count();
            return $suggestion;
        });

        return view('friends.index', compact('friends', 'pendingRequests', 'suggestions', 'stats'));
    }

    /**
     * Cerca utenti
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2|max:50',
        ]);

        $results = $this->friendshipService->searchUsers(
            Auth::user(),
            $request->input('query'),
            20
        );

        return response()->json([
            'users' => $results->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile_url' => route('users.profile', $user->id),
                ];
            })
        ]);
    }

    /**
     * Invia richiesta di amicizia
     */
    public function sendRequest(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'message' => 'nullable|string|max:500',
        ]);

        try {
            $friend = User::findOrFail($request->input('user_id'));
            $user = Auth::user();

            $friendship = $this->friendshipService->sendFriendRequest(
                $user,
                $friend,
                $request->input('message')
            );

            return response()->json([
                'success' => true,
                'message' => __('app.friends.request_sent'),
                'friendship_id' => $friendship->id,
            ]);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('app.friends.request_error'),
            ], 500);
        }
    }

    /**
     * Accetta richiesta di amicizia
     */
    public function acceptRequest(Friendship $friendship): JsonResponse
    {
        try {
            // Verifica che l'utente autenticato sia il destinatario
            if ($friendship->friend_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => __('app.friends.unauthorized'),
                ], 403);
            }

            $this->friendshipService->acceptFriendRequest($friendship);

            return response()->json([
                'success' => true,
                'message' => __('app.friends.request_accepted'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('app.friends.accept_error'),
            ], 500);
        }
    }

    /**
     * Rifiuta richiesta di amicizia
     */
    public function declineRequest(Friendship $friendship): JsonResponse
    {
        try {
            // Verifica che l'utente autenticato sia il destinatario
            if ($friendship->friend_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => __('app.friends.unauthorized'),
                ], 403);
            }

            $this->friendshipService->declineFriendRequest($friendship);

            return response()->json([
                'success' => true,
                'message' => __('app.friends.request_declined'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('app.friends.decline_error'),
            ], 500);
        }
    }

    /**
     * Rimuove amicizia
     */
    public function removeFriend(User $friend): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user->isFriendWith($friend)) {
                return response()->json([
                    'success' => false,
                    'message' => __('app.friends.not_friends'),
                ], 400);
            }

            $this->friendshipService->removeFriendship($user, $friend);

            return response()->json([
                'success' => true,
                'message' => __('app.friends.friendship_removed'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('app.friends.remove_error'),
            ], 500);
        }
    }

    /**
     * Blocca utente
     */
    public function blockUser(User $userToBlock): JsonResponse
    {
        try {
            $user = Auth::user();

            if ($user->id === $userToBlock->id) {
                return response()->json([
                    'success' => false,
                    'message' => __('app.friends.cannot_block_self'),
                ], 400);
            }

            $this->friendshipService->blockUser($user, $userToBlock);

            return response()->json([
                'success' => true,
                'message' => __('app.friends.user_blocked'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('app.friends.block_error'),
            ], 500);
        }
    }

    /**
     * Mostra amici comuni
     */
    public function mutualFriends(User $user): JsonResponse
    {
        $currentUser = Auth::user();
        $mutualFriends = $this->friendshipService->getMutualFriends($currentUser, $user);

        return response()->json([
            'mutual_friends' => $mutualFriends->map(function ($friend) {
                return [
                    'id' => $friend->id,
                    'name' => $friend->name,
                    'profile_url' => route('users.profile', $friend->id),
                ];
            }),
            'count' => $mutualFriends->count(),
        ]);
    }
}
