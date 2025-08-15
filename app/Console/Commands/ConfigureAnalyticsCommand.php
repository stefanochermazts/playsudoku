<?php
declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ConfigureAnalyticsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:configure 
                            {--tracking-id= : Google Analytics Tracking ID (G-XXXXXXXXXX)}
                            {--enable : Abilita analytics immediatamente}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configura Google Analytics per PlaySudoku con il tracking ID fornito';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🎯 Configurazione Google Analytics per PlaySudoku');
        $this->line('================================================');

        // Get tracking ID
        $trackingId = $this->option('tracking-id');
        if (!$trackingId) {
            $trackingId = $this->ask('Inserisci il Google Analytics Tracking ID (formato: G-XXXXXXXXXX)');
        }

        // Validate tracking ID format
        if (!preg_match('/^G-[A-Z0-9]{10}$/', $trackingId)) {
            $this->error('❌ Formato Tracking ID non valido. Deve essere nel formato G-XXXXXXXXXX');
            return 1;
        }

        $this->info("📊 Tracking ID: {$trackingId}");

        // Check environment
        $currentEnv = app()->environment();
        $enableNow = $this->option('enable');

        if (!$enableNow && $currentEnv === 'production') {
            $enableNow = $this->confirm('Siamo in ambiente production. Vuoi abilitare analytics immediatamente?', true);
        }

        // Update .env file
        $this->updateEnvFile($trackingId, $enableNow);

        // Clear config cache
        $this->call('config:clear');

        // Show current status
        $this->showCurrentConfiguration();

        // Show instructions
        $this->showInstructions($trackingId, $enableNow);

        return 0;
    }

    /**
     * Update .env file with analytics configuration
     */
    private function updateEnvFile(string $trackingId, bool $enable): void
    {
        $envPath = base_path('.env');
        
        if (!File::exists($envPath)) {
            $this->error('❌ File .env non trovato');
            return;
        }

        $envContent = File::get($envPath);

        // Update or add GOOGLE_ANALYTICS_ID
        if (str_contains($envContent, 'GOOGLE_ANALYTICS_ID=')) {
            $envContent = preg_replace('/GOOGLE_ANALYTICS_ID=.*/', "GOOGLE_ANALYTICS_ID={$trackingId}", $envContent);
        } else {
            $envContent .= "\n# Analytics\nGOOGLE_ANALYTICS_ID={$trackingId}\n";
        }

        // Update or add ANALYTICS_ENABLED
        if (str_contains($envContent, 'ANALYTICS_ENABLED=')) {
            $envContent = preg_replace('/ANALYTICS_ENABLED=.*/', 'ANALYTICS_ENABLED=' . ($enable ? 'true' : 'false'), $envContent);
        } else {
            $envContent .= "ANALYTICS_ENABLED=" . ($enable ? 'true' : 'false') . "\n";
        }

        File::put($envPath, $envContent);
        
        $this->info('✅ File .env aggiornato');
    }

    /**
     * Show current configuration
     */
    private function showCurrentConfiguration(): void
    {
        $this->line('');
        $this->info('📋 Configurazione Attuale:');
        $this->line('─────────────────────────');
        
        $this->line("Tracking ID: " . (config('analytics.google.tracking_id') ?: 'Non configurato'));
        $this->line("Analytics Abilitato: " . (config('analytics.google.enabled') ? '✅ Sì' : '❌ No'));
        $this->line("Environment: " . app()->environment());
        $this->line("Debug Mode: " . (config('analytics.google.debug') ? '✅ Sì' : '❌ No'));
        $this->line("Consent Mode: " . (config('analytics.google.consent_mode') ? '✅ Sì' : '❌ No'));
        $this->line("Anonymize IP: " . (config('analytics.google.anonymize_ip') ? '✅ Sì' : '❌ No'));
    }

    /**
     * Show setup instructions
     */
    private function showInstructions(string $trackingId, bool $enabled): void
    {
        $this->line('');
        $this->info('🚀 Prossimi Passi:');
        $this->line('─────────────────');

        if (!$enabled) {
            $this->warn('⚠️  Analytics non è ancora abilitato.');
            $this->line('Per abilitarlo in produzione, aggiungi al tuo .env:');
            $this->line('ANALYTICS_ENABLED=true');
            $this->line('');
        }

        $this->info('📊 Google Analytics è ora configurato sui seguenti layout:');
        $this->line('• resources/views/layouts/app.blade.php (Dashboard)');
        $this->line('• resources/views/layouts/site.blade.php (Sito pubblico)');
        $this->line('• resources/views/layouts/guest.blade.php (Login/Register)');
        
        $this->line('');
        $this->info('🔧 Funzionalità implementate:');
        $this->line('• ✅ Tracking pagine automatico');
        $this->line('• ✅ GDPR Consent Mode');
        $this->line('• ✅ IP Anonymization');
        $this->line('• ✅ User ID tracking (hashed)');
        $this->line('• ✅ Custom events per PlaySudoku');
        $this->line('• ✅ Debug mode per development');

        $this->line('');
        $this->info('🎮 Eventi PlaySudoku tracciati:');
        $this->line('• Registrazione utenti');
        $this->line('• Login/Logout');
        $this->line('• Inizio/Completamento sfide');
        $this->line('• Utilizzo hint');
        $this->line('• Condivisioni risultati');
        $this->line('• Errori e performance');

        $this->line('');
        $this->info('🧪 Test dell\'implementazione:');
        $this->line('1. Vai su una pagina del sito');
        $this->line('2. Apri Developer Tools (F12)');
        $this->line('3. Controlla la Console per messaggi di debug');
        $this->line('4. Verifica Network tab per chiamate a googletagmanager.com');

        if ($enabled && app()->environment('production')) {
            $this->line('');
            $this->success('🎉 Google Analytics è attivo e funzionante!');
            $this->line('I dati inizieranno ad apparire in GA4 tra 24-48 ore.');
        }
    }
}