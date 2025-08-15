<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\UserProfile;

/**
 * Controller per gestire le preferenze utente
 */
class UserPreferencesController extends Controller
{
    /**
     * Aggiorna le preferenze di tema
     */
    public function updateTheme(Request $request): JsonResponse
    {
        $request->validate([
            'theme' => 'required|in:light,dark,auto'
        ]);

        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Non autenticato'], 401);
        }

        // Ottieni o crea il profilo utente
        $profile = UserProfile::firstOrCreate(
            ['user_id' => $user->id],
            ['preferences_json' => []]
        );

        // Aggiorna la preferenza del tema
        $profile->setPreference('theme', $request->theme);
        $profile->save();

        return response()->json([
            'success' => true,
            'theme' => $request->theme,
            'message' => 'Preferenza tema aggiornata'
        ]);
    }

    /**
     * Ottiene le preferenze dell'utente
     */
    public function getPreferences(): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Non autenticato'], 401);
        }

        $profile = $user->profile;
        
        $preferences = [
            'theme' => $profile?->getTheme() ?? 'auto',
            'language' => $profile?->getLanguage() ?? 'it',
            'notifications' => $profile?->getNotificationPreferences() ?? []
        ];

        return response()->json($preferences);
    }

    /**
     * Aggiorna le preferenze di accessibilità
     */
    public function updateAccessibility(Request $request): JsonResponse
    {
        $request->validate([
            'high_contrast' => 'boolean',
            'reduced_motion' => 'boolean',
            'screen_reader_announcements' => 'boolean',
            'keyboard_navigation_hints' => 'boolean'
        ]);

        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Non autenticato'], 401);
        }

        $profile = UserProfile::firstOrCreate(
            ['user_id' => $user->id],
            ['preferences_json' => []]
        );

        // Aggiorna le preferenze di accessibilità
        foreach ($request->only(['high_contrast', 'reduced_motion', 'screen_reader_announcements', 'keyboard_navigation_hints']) as $key => $value) {
            if ($value !== null) {
                $profile->setPreference("accessibility.{$key}", $value);
            }
        }
        
        $profile->save();

        return response()->json([
            'success' => true,
            'message' => 'Preferenze accessibilità aggiornate'
        ]);
    }
}
