<?php
declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Email Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used for various email notifications
    | that are sent to users. You are free to modify these language lines
    | according to your application's requirements.
    |
    */

    'verify_email' => [
        'subject' => 'Verifica il tuo indirizzo email - :app',
        'greeting' => 'Ciao :name!',
        'intro' => 'Grazie per esserti registrato a PlaySudoku! Prima di iniziare a giocare, verifica il tuo indirizzo email cliccando sul pulsante sottostante.',
        'action' => 'Verifica Email',
        'outro_1' => 'Questo link di verifica scadrà tra 60 minuti.',
        'outro_2' => 'Se hai problemi con il pulsante, copia e incolla il seguente URL nel tuo browser: :url',
        'outro_3' => 'Se non hai creato un account, ignora questa email.',
        'salutation' => 'Saluti,<br>Il team di :app',
    ],

    'welcome' => [
        'subject' => 'Benvenuto in :app!',
        'greeting' => 'Benvenuto :name!',
        'intro' => 'Il tuo indirizzo email è stato verificato con successo. Ora puoi iniziare a giocare a Sudoku e partecipare alle nostre sfide quotidiane e settimanali!',
        'action' => 'Inizia a Giocare',
        'outro' => 'Buon divertimento e che vinca il migliore!',
        'salutation' => 'Il team di :app',
    ],

    'password_reset' => [
        'subject' => 'Reset Password - :app',
        'greeting' => 'Ciao!',
        'intro' => 'Hai ricevuto questa email perché abbiamo ricevuto una richiesta di reset password per il tuo account.',
        'action' => 'Reset Password',
        'outro_1' => 'Questo link di reset password scadrà tra 60 minuti.',
        'outro_2' => 'Se non hai richiesto un reset password, ignora questa email.',
        'salutation' => 'Saluti,<br>Il team di :app',
    ],

    'new_challenge' => [
        'subject' => 'Nuova Sfida :type Disponibile! - :app',
        'greeting' => 'Ciao :name!',
        'intro' => ':emoji È disponibile una nuova sfida :type su PlaySudoku: **:title**',
        'difficulty' => 'Difficoltà: **:difficulty** - Metti alla prova le tue abilità!',
        'action' => 'Gioca Ora',
        'outro_1' => 'La sfida :type termina :ends_at. Non perdere l\'opportunità di scalare la classifica!',
        'outro_2' => 'Buona fortuna e che vinca il migliore!',
        'salutation' => 'Il team di :app',
    ],

    'contact' => [
        'subject_admin' => '[:app] Nuovo messaggio di contatto: :subject',
        'subject_user' => 'Conferma ricezione messaggio - :app',
    ],
];
