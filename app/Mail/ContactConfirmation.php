<?php
declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public array $contactData;

    public function __construct(array $contactData)
    {
        $this->contactData = $contactData;
    }

    public function envelope(): Envelope
    {
        $locale = $this->contactData['locale'] ?? 'en';
        
        // Subject localizzato
        $subject = $locale === 'it' 
            ? 'Conferma ricezione messaggio - PlaySudoku'
            : 'Message received confirmation - PlaySudoku';

        return new Envelope(
            subject: $subject
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-confirmation',
            with: [
                'contactData' => $this->contactData
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}