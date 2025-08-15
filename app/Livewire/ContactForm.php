<?php
declare(strict_types=1);

namespace App\Livewire;

use App\Mail\ContactMessage;
use App\Mail\ContactConfirmation;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Rule;
use Livewire\Component;

class ContactForm extends Component
{
    #[Rule('required|string|min:2|max:100')]
    public string $name = '';

    #[Rule('required|email|max:255')]
    public string $email = '';

    #[Rule('required|string|max:200')]
    public string $subject = '';

    #[Rule('required|string|min:10|max:2000')]
    public string $message = '';
    
    #[Rule('required|accepted')]
    public bool $privacy_accepted = false;

    public bool $isSubmitted = false;
    public bool $isLoading = false;
    public string $locale = '';

    public function mount(): void
    {
        $this->locale = app()->getLocale();
    }

    public function submit(): void
    {
        $this->isLoading = true;
        
        try {
            $this->validate(
                rules: [
                    'name' => 'required|string|min:2|max:100',
                    'email' => 'required|email|max:255',
                    'subject' => 'required|string|max:200',
                    'message' => 'required|string|min:10|max:2000',
                    'privacy_accepted' => 'required|accepted',
                ],
                messages: [
                    'name.required' => __('validation.required', ['attribute' => __('app.contact.name')]),
                    'name.min' => __('validation.min.string', ['attribute' => __('app.contact.name'), 'min' => 2]),
                    'email.required' => __('validation.required', ['attribute' => __('app.contact.email')]),
                    'email.email' => __('validation.email', ['attribute' => __('app.contact.email')]),
                    'subject.required' => __('validation.required', ['attribute' => __('app.contact.subject')]),
                    'message.required' => __('validation.required', ['attribute' => __('app.contact.message')]),
                    'message.min' => __('validation.min.string', ['attribute' => __('app.contact.message'), 'min' => 10]),
                    'privacy_accepted.required' => __('app.privacy.must_accept'),
                    'privacy_accepted.accepted' => __('app.privacy.must_accept'),
                ]
            );

            $contactData = [
                'name' => $this->name,
                'email' => $this->email,
                'subject' => $this->subject,
                'message' => $this->message,
                'locale' => $this->locale,
                'submitted_at' => now(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ];

            // Invia email agli admin del sistema
            $adminEmails = config('mail.admin_addresses', ['admin@playsudoku.com']);
            foreach ($adminEmails as $adminEmail) {
                Mail::to($adminEmail)->send(new ContactMessage($contactData));
            }

            // Invia email di conferma all'utente
            Mail::to($this->email)->send(new ContactConfirmation($contactData));

            // Reset form e mostra messaggio di successo
            $this->reset(['name', 'email', 'subject', 'message', 'privacy_accepted']);
            $this->isSubmitted = true;

        } catch (\Exception $e) {
            session()->flash('error', __('app.contact.error_sending'));
            \Log::error('Contact form error', [
                'error' => $e->getMessage(),
                'email' => $this->email,
                'subject' => $this->subject
            ]);
        } finally {
            $this->isLoading = false;
        }
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'email', 'subject', 'message']);
        $this->isSubmitted = false;
        session()->forget('error');
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}