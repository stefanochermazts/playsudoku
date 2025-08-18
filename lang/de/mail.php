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
        'subject' => 'E-Mail-Adresse bestätigen - :app',
        'greeting' => 'Hallo :name!',
        'intro' => 'Danke, dass Sie sich bei PlaySudoku angemeldet haben! Bevor Sie mit dem Spielen beginnen können, bestätigen Sie bitte Ihre E-Mail-Adresse, indem Sie auf die Schaltfläche unten klicken.',
        'action' => 'E-Mail bestätigen',
        'outro_1' => 'Dieser Bestätigungslink läuft in 60 Minuten ab.',
        'outro_2' => 'Falls Sie Probleme beim Klicken der Schaltfläche haben, kopieren Sie die folgende URL und fügen Sie sie in Ihren Webbrowser ein: :url',
        'outro_3' => 'Falls Sie kein Konto erstellt haben, ist keine weitere Aktion erforderlich.',
        'salutation' => 'Mit freundlichen Grüßen,<br>Das :app Team',
    ],

    'welcome' => [
        'subject' => 'Willkommen bei :app!',
        'greeting' => 'Willkommen :name!',
        'intro' => 'Ihre E-Mail-Adresse wurde erfolgreich bestätigt. Sie können jetzt Sudoku spielen und an unseren täglichen und wöchentlichen Herausforderungen teilnehmen!',
        'action' => 'Jetzt spielen',
        'outro' => 'Viel Spaß und möge der beste Spieler gewinnen!',
        'salutation' => 'Das :app Team',
    ],

    'password_reset' => [
        'subject' => 'Passwort zurücksetzen - :app',
        'greeting' => 'Hallo!',
        'intro' => 'Sie erhalten diese E-Mail, weil wir eine Anfrage zum Zurücksetzen des Passworts für Ihr Konto erhalten haben.',
        'action' => 'Passwort zurücksetzen',
        'outro_1' => 'Dieser Link zum Zurücksetzen des Passworts läuft in 60 Minuten ab.',
        'outro_2' => 'Falls Sie kein Zurücksetzen des Passworts angefordert haben, ist keine weitere Aktion erforderlich.',
        'salutation' => 'Mit freundlichen Grüßen,<br>Das :app Team',
    ],

    'new_challenge' => [
        'subject' => 'Neue :type Herausforderung verfügbar! - :app',
        'greeting' => 'Hallo :name!',
        'intro' => ':emoji Eine neue :type Herausforderung ist jetzt auf PlaySudoku verfügbar: **:title**',
        'difficulty' => 'Schwierigkeit: **:difficulty** - Testen Sie Ihre Fähigkeiten!',
        'action' => 'Jetzt spielen',
        'outro_1' => 'Die :type Herausforderung endet :ends_at. Verpassen Sie nicht Ihre Chance, die Bestenliste zu erklimmen!',
        'outro_2' => 'Viel Glück und möge der beste Spieler gewinnen!',
        'salutation' => 'Das :app Team',
    ],

    'contact' => [
        'subject_admin' => '[:app] Neue Kontaktanfrage: :subject',
        'subject_user' => 'Eingang Ihrer Nachricht bestätigt - :app',
    ],
];