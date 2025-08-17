<?php

namespace App\Http\Controllers;

use App\Services\ConsentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ConsentController extends Controller
{
    /**
     * Store consent preferences.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'essential' => 'boolean',
            'analytics' => 'boolean', 
            'marketing' => 'boolean',
            'contact_form' => 'boolean',
            'registration' => 'boolean',
            'privacy_settings' => 'boolean',
            'newsletter' => 'boolean',
        ]);

        $consents = [];
        
        // Only include consents that are explicitly provided
        foreach (['essential', 'analytics', 'marketing', 'contact_form', 'registration', 'privacy_settings', 'newsletter'] as $type) {
            if ($request->has($type)) {
                $consents[$type] = $request->boolean($type);
            }
        }

        $consentService = app(ConsentService::class);
        
        $recordedConsents = $consentService->recordConsent(
            consents: $consents,
            user: auth()->user(),
            sessionId: session()->getId(),
            request: $request
        );

        return response()->json([
            'success' => true,
            'message' => 'Consensi salvati con successo',
            'consents' => $recordedConsents->count()
        ]);
    }

    /**
     * Get current consent status.
     */
    public function status(Request $request): JsonResponse
    {
        $consentService = app(ConsentService::class);
        
        $consents = $consentService->getCurrentConsents(
            user: auth()->user(),
            sessionId: session()->getId()
        );

        $status = [
            'essential' => $consentService->hasActiveConsent('essential', auth()->user(), session()->getId()),
            'analytics' => $consentService->hasActiveConsent('analytics', auth()->user(), session()->getId()),
            'marketing' => $consentService->hasActiveConsent('marketing', auth()->user(), session()->getId()),
            'contact_form' => $consentService->hasActiveConsent('contact_form', auth()->user(), session()->getId()),
            'registration' => $consentService->hasActiveConsent('registration', auth()->user(), session()->getId()),
            'privacy_settings' => $consentService->hasActiveConsent('privacy_settings', auth()->user(), session()->getId()),
            'newsletter' => $consentService->hasActiveConsent('newsletter', auth()->user(), session()->getId()),
        ];

        return response()->json([
            'success' => true,
            'consents' => $status,
            'has_consented' => $consents->isNotEmpty()
        ]);
    }
}
