<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ==========================================
// 📅 SCHEDULING SUDOKU CHALLENGES
// ==========================================

// Sfida giornaliera - ogni giorno alle 00:00
Schedule::command('challenge:generate-daily')
    ->dailyAt(config('sudoku.scheduling.daily_time', '00:00'))
    ->withoutOverlapping(60) // Previene sovrapposizioni per 60 minuti
    ->onOneServer() // Esegue solo su un server in caso di deployment multi-server
    ->emailOutputOnFailure(config('mail.from.address'))
    ->appendOutputTo(storage_path('logs/scheduler.log'));

// Sfida settimanale - ogni lunedì alle 00:00  
Schedule::command('challenge:generate-weekly')
    ->weeklyOn(1, config('sudoku.scheduling.weekly_time', '00:00')) // 1 = Lunedì
    ->withoutOverlapping(60)
    ->onOneServer()
    ->emailOutputOnFailure(config('mail.from.address'))
    ->appendOutputTo(storage_path('logs/scheduler.log'));

// Attivazione sfide programmate - ogni 5 minuti
Schedule::command('challenge:activate-scheduled')
    ->everyFiveMinutes()
    ->withoutOverlapping(5)
    ->onOneServer()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/scheduler.log'));

// Cleanup tentativi incompleti - ogni notte alle 02:00
Schedule::command('challenge:cleanup', [
    '--days' => config('sudoku.scheduling.cleanup_incomplete_days', 7)
])
    ->dailyAt('02:00')
    ->withoutOverlapping(30)
    ->onOneServer()
    ->emailOutputOnFailure(config('mail.from.address'))
    ->appendOutputTo(storage_path('logs/scheduler.log'));

// Ottimizzazione performance - ogni domenica alle 03:00
Schedule::command('performance:optimize')
    ->weeklyOn(0, '03:00') // 0 = Domenica
    ->withoutOverlapping(60)
    ->onOneServer()
    ->emailOutputOnFailure(config('mail.from.address'))
    ->appendOutputTo(storage_path('logs/scheduler.log'));

// Analisi anomalie - ogni ora
Schedule::command('sudoku:analyze-anomalies')
    ->hourly()
    ->withoutOverlapping(10)
    ->onOneServer()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/scheduler.log'));

// Pulizia consensi scaduti (GDPR) - ogni settimana alle 01:00
Schedule::command('consent:cleanup')
    ->weeklyOn(1, '01:00') // Lunedì alle 01:00
    ->withoutOverlapping(30)
    ->onOneServer()
    ->emailOutputOnFailure(config('mail.from.address'))
    ->appendOutputTo(storage_path('logs/scheduler.log'));

// Generazione sitemap statica - ogni giorno alle 02:00
Schedule::command('sitemap:generate --force')
    ->dailyAt('02:00')
    ->withoutOverlapping(30)
    ->onOneServer()
    ->emailOutputOnFailure(config('mail.from.address'))
    ->appendOutputTo(storage_path('logs/scheduler.log'));

// ==========================================
// 🔍 SCHEDULING DIAGNOSTICS  
// ==========================================

// Comando per verificare lo stato del scheduler
Artisan::command('schedule:status', function () {
    $this->info('📅 Stato Scheduler PlaySudoku');
    $this->info('================================');
    
    $schedules = [
        'Sfida Giornaliera' => 'Ogni giorno alle ' . config('sudoku.scheduling.daily_time', '00:00'),
        'Sfida Settimanale' => 'Ogni lunedì alle ' . config('sudoku.scheduling.weekly_time', '00:00'),
        'Attivazione Sfide' => 'Ogni 5 minuti (scheduled → active)',
        'Cleanup Database' => 'Ogni notte alle 02:00',
        'Cleanup Consensi GDPR' => 'Ogni lunedì alle 01:00',
        'Generazione Sitemap' => 'Ogni giorno alle 02:00',
        'Ottimizzazione' => 'Ogni domenica alle 03:00',
        'Analisi Anomalie' => 'Ogni ora',
    ];
    
    foreach ($schedules as $name => $schedule) {
        $this->line("📋 {$name}: {$schedule}");
    }
    
    $this->info('');
    $this->info('🔧 Per avviare lo scheduler in produzione:');
    $this->comment('   * * * * * php ' . base_path('artisan') . ' schedule:run >> /dev/null 2>&1');
    $this->info('');
    $this->info('📊 Log scheduler: ' . storage_path('logs/scheduler.log'));
    
})->purpose('Mostra lo stato della configurazione dello scheduler');
