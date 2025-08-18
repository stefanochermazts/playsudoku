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
        'subject' => 'Verify Your Email Address - :app',
        'greeting' => 'Hello :name!',
        'intro' => 'Thanks for signing up for PlaySudoku! Before you can start playing, please verify your email address by clicking the button below.',
        'action' => 'Verify Email',
        'outro_1' => 'This verification link will expire in 60 minutes.',
        'outro_2' => 'If you\'re having trouble clicking the button, copy and paste the URL below into your web browser: :url',
        'outro_3' => 'If you did not create an account, no further action is required.',
        'salutation' => 'Regards,<br>The :app Team',
    ],

    'welcome' => [
        'subject' => 'Welcome to :app!',
        'greeting' => 'Welcome :name!',
        'intro' => 'Your email address has been successfully verified. You can now start playing Sudoku and participate in our daily and weekly challenges!',
        'action' => 'Start Playing',
        'outro' => 'Have fun and may the best player win!',
        'salutation' => 'The :app Team',
    ],

    'password_reset' => [
        'subject' => 'Reset Password - :app',
        'greeting' => 'Hello!',
        'intro' => 'You are receiving this email because we received a password reset request for your account.',
        'action' => 'Reset Password',
        'outro_1' => 'This password reset link will expire in 60 minutes.',
        'outro_2' => 'If you did not request a password reset, no further action is required.',
        'salutation' => 'Regards,<br>The :app Team',
    ],

    'new_challenge' => [
        'subject' => 'New :type Challenge Available! - :app',
        'greeting' => 'Hello :name!',
        'intro' => ':emoji A new :type challenge is now available on PlaySudoku: **:title**',
        'difficulty' => 'Difficulty: **:difficulty** - Test your skills!',
        'action' => 'Play Now',
        'outro_1' => 'The :type challenge ends :ends_at. Don\'t miss your chance to climb the leaderboard!',
        'outro_2' => 'Good luck and may the best player win!',
        'salutation' => 'The :app Team',
    ],

    'contact' => [
        'subject_admin' => '[ :app ] New contact message: :subject',
        'subject_user' => 'Message received confirmation - :app',
    ],
];
