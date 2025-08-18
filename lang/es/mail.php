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
        'subject' => 'Verifica tu dirección de correo electrónico - :app',
        'greeting' => '¡Hola :name!',
        'intro' => '¡Gracias por registrarte en PlaySudoku! Antes de que puedas comenzar a jugar, por favor verifica tu dirección de correo electrónico haciendo clic en el botón de abajo.',
        'action' => 'Verificar correo',
        'outro_1' => 'Este enlace de verificación expirará en 60 minutos.',
        'outro_2' => 'Si tienes problemas haciendo clic en el botón, copia y pega la URL de abajo en tu navegador web: :url',
        'outro_3' => 'Si no creaste una cuenta, no se requiere ninguna acción adicional.',
        'salutation' => 'Saludos,<br>El equipo de :app',
    ],

    'welcome' => [
        'subject' => '¡Bienvenido a :app!',
        'greeting' => '¡Bienvenido :name!',
        'intro' => 'Tu dirección de correo electrónico ha sido verificada exitosamente. ¡Ahora puedes comenzar a jugar Sudoku y participar en nuestros desafíos diarios y semanales!',
        'action' => 'Comenzar a jugar',
        'outro' => '¡Diviértete y que gane el mejor jugador!',
        'salutation' => 'El equipo de :app',
    ],

    'password_reset' => [
        'subject' => 'Restablecer contraseña - :app',
        'greeting' => '¡Hola!',
        'intro' => 'Estás recibiendo este correo porque recibimos una solicitud de restablecimiento de contraseña para tu cuenta.',
        'action' => 'Restablecer contraseña',
        'outro_1' => 'Este enlace de restablecimiento de contraseña expirará en 60 minutos.',
        'outro_2' => 'Si no solicitaste un restablecimiento de contraseña, no se requiere ninguna acción adicional.',
        'salutation' => 'Saludos,<br>El equipo de :app',
    ],

    'new_challenge' => [
        'subject' => '¡Nuevo desafío :type disponible! - :app',
        'greeting' => '¡Hola :name!',
        'intro' => ':emoji Un nuevo desafío :type está ahora disponible en PlaySudoku: **:title**',
        'difficulty' => 'Dificultad: **:difficulty** - ¡Pon a prueba tus habilidades!',
        'action' => 'Jugar ahora',
        'outro_1' => 'El desafío :type termina :ends_at. ¡No pierdas tu oportunidad de escalar en la clasificación!',
        'outro_2' => '¡Buena suerte y que gane el mejor jugador!',
        'salutation' => 'El equipo de :app',
    ],

    'contact' => [
        'subject_admin' => '[:app] Nuevo mensaje de contacto: :subject',
        'subject_user' => 'Confirmación de recepción del mensaje - :app',
    ],
];