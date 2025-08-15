<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PlaySudoku Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione specifica per l'applicazione PlaySudoku
    |
    */

    'notifications' => [
        /*
        |--------------------------------------------------------------------------
        | New Challenge Notifications
        |--------------------------------------------------------------------------
        |
        | Determina se inviare notifiche email agli utenti quando vengono
        | create nuove sfide giornaliere o settimanali.
        |
        */
        'new_challenges' => env('SUDOKU_NOTIFY_NEW_CHALLENGES', false),
        
        /*
        |--------------------------------------------------------------------------
        | Notification Settings
        |--------------------------------------------------------------------------
        |
        | Impostazioni per le notifiche email
        |
        */
        'batch_size' => env('SUDOKU_NOTIFICATION_BATCH_SIZE', 100),
        'rate_limit_delay' => env('SUDOKU_NOTIFICATION_DELAY_MS', 100),
    ],

    'scheduling' => [
        /*
        |--------------------------------------------------------------------------
        | Challenge Generation Times
        |--------------------------------------------------------------------------
        |
        | Orari per la generazione automatica delle sfide
        |
        */
        'daily_time' => env('SUDOKU_DAILY_TIME', '00:00'),
        'weekly_time' => env('SUDOKU_WEEKLY_TIME', '00:00'),
        
        /*
        |--------------------------------------------------------------------------
        | Cleanup Settings
        |--------------------------------------------------------------------------
        |
        | Impostazioni per il cleanup automatico
        |
        */
        'cleanup_incomplete_days' => env('SUDOKU_CLEANUP_DAYS', 7),
        'cleanup_moves_days' => env('SUDOKU_CLEANUP_MOVES_DAYS', 30),
        'cleanup_flagged_days' => env('SUDOKU_CLEANUP_FLAGGED_DAYS', 90),
    ],

    'challenges' => [
        /*
        |--------------------------------------------------------------------------
        | Challenge Difficulty Rotation
        |--------------------------------------------------------------------------
        |
        | Schema di rotazione delle difficoltà per le sfide giornaliere
        | 1 = Lunedì, 2 = Martedì, ... 0 = Domenica
        |
        */
        'daily_difficulty_rotation' => [
            1 => 'easy',        // Lunedì
            2 => 'medium',      // Martedì
            3 => 'medium',      // Mercoledì
            4 => 'hard',        // Giovedì
            5 => 'hard',        // Venerdì
            6 => 'expert',      // Sabato
            0 => 'expert',      // Domenica
        ],
        
        /*
        |--------------------------------------------------------------------------
        | Weekly Challenge Settings
        |--------------------------------------------------------------------------
        |
        | Impostazioni per le sfide settimanali
        |
        */
        'weekly_difficulties' => ['expert', 'crazy'],
        'weekly_rotation_weeks' => 2, // Alterna ogni 2 settimane
        
        /*
        |--------------------------------------------------------------------------
        | Estimated Duration (minutes)
        |--------------------------------------------------------------------------
        |
        | Durata stimata in minuti per ogni difficoltà
        |
        */
        'estimated_duration' => [
            'easy' => 8,
            'medium' => 15,
            'hard' => 25,
            'expert' => 40,
            'crazy' => 60,
        ],
    ],
];
