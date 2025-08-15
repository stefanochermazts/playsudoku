<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Challenge;
use App\Models\User;
use App\Notifications\NewChallengeNotification;

class NotifyUsersNewChallenge extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'challenge:notify-users {challenge : Challenge ID} {--type=daily : Challenge type (daily/weekly)} {--limit=100 : Numero massimo di utenti da notificare per batch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notifica gli utenti via email delle nuove sfide disponibili';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $challengeId = $this->argument('challenge');
        $type = $this->option('type');
        $limit = (int) $this->option('limit');
        
        // Trova la sfida
        $challenge = Challenge::find($challengeId);
        if (!$challenge) {
            $this->error("❌ Sfida con ID {$challengeId} non trovata");
            return 1;
        }
        
        $this->info("📧 Invio notifiche per la sfida: {$challenge->title}");
        $this->info("📊 Tipo: {$type}");
        
        // Query utenti attivi che vogliono ricevere notifiche
        $usersQuery = User::whereNotNull('email_verified_at')
            ->where('created_at', '<', now()->subDays(1)) // Solo utenti registrati da almeno 1 giorno
            ->whereHas('challengeAttempts', function($query) {
                // Solo utenti che hanno già fatto almeno un tentativo (utenti attivi)
                $query->where('created_at', '>', now()->subDays(30));
            });
            
        // Filtra per preferenze notifiche
        if ($type === 'weekly') {
            $usersQuery->where('notify_weekly_challenges', true)
                      ->whereHas('challengeAttempts', function($query) {
                          $query->where('created_at', '>', now()->subDays(7))
                                ->whereNotNull('completed_at'); // Solo chi completa le sfide
                      });
        } else {
            $usersQuery->where('notify_new_challenges', true);
        }
        
        // Rate limiting: non inviare se l'ultima notifica è stata inviata meno di 1 ora fa
        $usersQuery->where(function($query) {
            $query->whereNull('last_notification_sent_at')
                  ->orWhere('last_notification_sent_at', '<', now()->subHour());
        });
        
        $totalUsers = $usersQuery->count();
        $this->info("👥 Utenti candidati per notifica: {$totalUsers}");
        
        if ($totalUsers === 0) {
            $this->info("✨ Nessun utente da notificare");
            return 0;
        }
        
        // Processo in batch per evitare sovraccarico
        $notifiedCount = 0;
        $failedCount = 0;
        
        $progressBar = $this->output->createProgressBar($totalUsers);
        $progressBar->start();
        
        $usersQuery->chunk($limit, function($users) use ($challenge, &$notifiedCount, &$failedCount, $progressBar) {
            foreach ($users as $user) {
                try {
                    // Controlla se l'utente ha già ricevuto una notifica per questa sfida
                    $existingNotification = $user->notifications()
                        ->where('type', NewChallengeNotification::class)
                        ->where('data->challenge_id', $challenge->id)
                        ->exists();
                        
                    if (!$existingNotification) {
                        $user->notify(new NewChallengeNotification($challenge));
                        
                        // Aggiorna timestamp ultima notifica
                        $user->update(['last_notification_sent_at' => now()]);
                        
                        $notifiedCount++;
                    }
                    
                } catch (\Exception $e) {
                    $failedCount++;
                    $this->newLine();
                    $this->warn("⚠️ Errore notifica utente {$user->id} ({$user->email}): " . $e->getMessage());
                }
                
                $progressBar->advance();
                
                // Pausa breve per evitare rate limiting
                if ($notifiedCount % 10 === 0) {
                    usleep(100000); // 100ms pausa ogni 10 email
                }
            }
        });
        
        $progressBar->finish();
        $this->newLine(2);
        
        $this->info("✅ Processo completato!");
        $this->info("📧 Notifiche inviate: {$notifiedCount}");
        
        if ($failedCount > 0) {
            $this->warn("⚠️ Notifiche fallite: {$failedCount}");
        }
        
        $this->info("⏱️ Le email verranno elaborate dalla coda di Laravel");
        $this->info("🔍 Controlla php artisan queue:work per processare le email");
        
        return 0;
    }
}
