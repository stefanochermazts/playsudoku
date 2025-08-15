<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class PrivacyController extends Controller
{
    public function __construct()
    {
        // Middleware 'auth' già applicato dal gruppo di rotte
    }

    /**
     * Mostra la pagina delle impostazioni privacy
     */
    public function index(): View
    {
        $user = Auth::user();
        $visibilityOptions = User::getVisibilityOptions();

        return view('privacy.index', compact('user', 'visibilityOptions'));
    }

    /**
     * Aggiorna le impostazioni privacy
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'profile_visibility' => 'required|in:public,friends,private',
            'stats_visibility' => 'required|in:public,friends,private',
            'friend_requests_enabled' => 'boolean',
            'show_online_status' => 'boolean',
            'activity_feed_visible' => 'boolean',
        ]);

        // Assicurati che i valori boolean siano settati correttamente
        $validated['friend_requests_enabled'] = $request->has('friend_requests_enabled');
        $validated['show_online_status'] = $request->has('show_online_status');
        $validated['activity_feed_visible'] = $request->has('activity_feed_visible');

        $user->update($validated);

        return redirect()->route('localized.privacy.index', ['locale' => app()->getLocale()])
                        ->with('success', __('app.privacy.settings_updated'));
    }
}
