<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Challenge;

class NewChallengeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Challenge $challenge
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $locale = app()->getLocale();
        $challengeUrl = $this->getChallengeUrl($locale);
        
        $emoji = $this->challenge->type === 'daily' ? '📅' : '📆';
        $typeText = $this->challenge->type === 'daily' ? 'giornaliera' : 'settimanale';
        
        return (new MailMessage)
            ->subject(__('mail.new_challenge.subject', [
                'type' => ucfirst($typeText),
                'app' => config('app.name')
            ]))
            ->greeting(__('mail.new_challenge.greeting', ['name' => $notifiable->name]))
            ->line(__('mail.new_challenge.intro', [
                'emoji' => $emoji,
                'type' => $typeText,
                'title' => $this->challenge->title
            ]))
            ->line(__('mail.new_challenge.difficulty', [
                'difficulty' => $this->getDifficultyText($this->challenge->difficulty)
            ]))
            ->action(__('mail.new_challenge.action'), $challengeUrl)
            ->line(__('mail.new_challenge.outro_1', [
                'type' => $typeText,
                'ends_at' => $this->challenge->ends_at->locale('it')->isoFormat('dddd D MMMM [alle] HH:mm')
            ]))
            ->line(__('mail.new_challenge.outro_2'))
            ->salutation(__('mail.new_challenge.salutation', ['app' => config('app.name')]));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'challenge_id' => $this->challenge->id,
            'challenge_type' => $this->challenge->type,
            'challenge_title' => $this->challenge->title,
            'difficulty' => $this->challenge->difficulty,
            'ends_at' => $this->challenge->ends_at->toISOString(),
        ];
    }
    
    /**
     * Get the challenge URL based on locale
     */
    private function getChallengeUrl(string $locale): string
    {
        $routeName = $this->challenge->type === 'daily' ? 'daily-board.show' : 'weekly-board.show';
        
        if ($locale !== config('app.fallback_locale')) {
            $routeName = 'localized.' . $routeName;
            return route($routeName, [
                'locale' => $locale,
                'challenge' => $this->challenge->id
            ]);
        }
        
        return route($routeName, ['challenge' => $this->challenge->id]);
    }
    
    /**
     * Get localized difficulty text
     */
    private function getDifficultyText(string $difficulty): string
    {
        return match($difficulty) {
            'easy' => 'Facile',
            'medium' => 'Medio',
            'hard' => 'Difficile',
            'expert' => 'Esperto',
            'crazy' => 'Estremo',
            default => ucfirst($difficulty)
        };
    }
}
