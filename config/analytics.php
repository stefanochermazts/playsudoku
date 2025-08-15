<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google Analytics Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per Google Analytics 4 (GA4)
    |
    */

    'google' => [
        /*
        |--------------------------------------------------------------------------
        | Google Analytics Tracking ID
        |--------------------------------------------------------------------------
        |
        | Il tracking ID di Google Analytics 4 (formato: G-XXXXXXXXXX)
        |
        */
        'tracking_id' => env('GOOGLE_ANALYTICS_ID'),

        /*
        |--------------------------------------------------------------------------
        | Enable Analytics
        |--------------------------------------------------------------------------
        |
        | Determina se abilitare il tracking analytics. Tipicamente abilitato
        | solo in produzione per rispettare la privacy in development.
        |
        */
        'enabled' => env('ANALYTICS_ENABLED', false),

        /*
        |--------------------------------------------------------------------------
        | Enable Debug Mode
        |--------------------------------------------------------------------------
        |
        | Debug mode per Google Analytics. Utile per testing e development.
        |
        */
        'debug' => env('ANALYTICS_DEBUG', false),

        /*
        |--------------------------------------------------------------------------
        | Anonymize IP
        |--------------------------------------------------------------------------
        |
        | Anonimizza gli indirizzi IP per compliance GDPR
        |
        */
        'anonymize_ip' => env('ANALYTICS_ANONYMIZE_IP', true),

        /*
        |--------------------------------------------------------------------------
        | Consent Mode
        |--------------------------------------------------------------------------
        |
        | Abilita il consent mode per compliance privacy
        |
        */
        'consent_mode' => env('ANALYTICS_CONSENT_MODE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Privacy Settings
    |--------------------------------------------------------------------------
    |
    | Impostazioni per la privacy e compliance GDPR
    |
    */
    'privacy' => [
        'cookie_consent_required' => env('ANALYTICS_COOKIE_CONSENT', true),
        'data_retention_months' => env('ANALYTICS_RETENTION_MONTHS', 14),
    ],

    /*
    |--------------------------------------------------------------------------
    | Environment-based Enabling
    |--------------------------------------------------------------------------
    |
    | Auto-enable analytics basato sull'ambiente
    |
    */
    'auto_enable_environments' => ['production', 'local'],
];
