<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use App\Services\CacheService;
use App\Models\Challenge;
use App\Models\User;
use App\Models\Puzzle;

class CacheWarmup extends Command
{
    protected $signature = 'cache:warmup {--force : Force cache regeneration}';
    protected $description = 'Warmup Redis cache with actual application data';

    public function handle(): int
    {
        $this->info('🔥 Cache Warmup - Popolamento cache Redis');
        $this->newLine();

        $cacheService = app(CacheService::class);
        
        // 1. Statistiche giornaliere
        $this->populateDailyStats($cacheService);
        
        // 2. Cache sfide se esistono
        $this->populateChallengeCache($cacheService);
        
        // 3. Cache utenti se esistono
        $this->populateUserCache($cacheService);
        
        // 4. Cache test manuali
        $this->populateTestCache();
        
        // 5. Verifica finale
        $this->verifyPopulation();

        $this->newLine();
        $this->info('✅ Cache warmup completato!');
        $this->line('   🌐 Vai su /admin/redis per vedere le statistiche aggiornate');

        return 0;
    }

    private function populateDailyStats(CacheService $cacheService): void
    {
        $this->info('📊 Popolamento statistiche giornaliere...');
        
        try {
            $stats = $cacheService->getDailySystemStats();
            $this->line("   ✅ Statistiche create:");
            $this->line("     - Nuovi utenti: {$stats['new_users']}");
            $this->line("     - Utenti attivi: {$stats['active_users']}");
            $this->line("     - Sfide completate: {$stats['completed_challenges']}");
            
        } catch (\Exception $e) {
            $this->error("   ❌ Errore statistiche: " . $e->getMessage());
        }
    }

    private function populateChallengeCache(CacheService $cacheService): void
    {
        $this->info('🏆 Popolamento cache sfide...');
        
        try {
            $challenges = Challenge::limit(5)->get();
            
            if ($challenges->isEmpty()) {
                $this->line("   ℹ️ Nessuna sfida trovata per popolare cache");
                return;
            }
            
            foreach ($challenges as $challenge) {
                // Leaderboard
                $leaderboard = $cacheService->getChallengeLeaderboard($challenge->id, 50);
                $this->line("   ✅ Leaderboard sfida {$challenge->id}: " . count($leaderboard) . " entries");
                
                // Dettagli sfida
                $details = $cacheService->getChallengeDetails($challenge->id);
                $this->line("   ✅ Dettagli sfida {$challenge->id}: " . ($details ? 'OK' : 'vuoti'));
                
                // Partecipazione
                $participation = $cacheService->getChallengeParticipation($challenge->id);
                $this->line("   ✅ Partecipazione sfida {$challenge->id}: {$participation['total']} utenti");
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ Errore cache sfide: " . $e->getMessage());
        }
    }

    private function populateUserCache(CacheService $cacheService): void
    {
        $this->info('👤 Popolamento cache utenti...');
        
        try {
            $users = User::limit(3)->get();
            
            if ($users->isEmpty()) {
                $this->line("   ℹ️ Nessun utente trovato per popolare cache");
                return;
            }
            
            foreach ($users as $user) {
                $userStats = $cacheService->getUserStats($user->id);
                $this->line("   ✅ Stats utente {$user->id} ({$user->email}): " . json_encode($userStats));
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ Errore cache utenti: " . $e->getMessage());
        }
    }

    private function populateTestCache(): void
    {
        $this->info('🧪 Popolamento cache di test...');
        
        try {
            // Cache manuali per test
            $testData = [
                'playsudoku:test:manual_' . time() => [
                    'type' => 'manual_test',
                    'created_at' => now()->toISOString(),
                    'data' => 'Test cache warmup'
                ],
                'playsudoku:leaderboard:test_challenge.999.limit.50' => [
                    ['user_id' => 1, 'time' => 120000, 'rank' => 1],
                    ['user_id' => 2, 'time' => 150000, 'rank' => 2],
                ],
                'playsudoku:puzzle:data.999' => [
                    'id' => 999,
                    'difficulty' => 'medium',
                    'seed' => 123456,
                    'cached_at' => now()->toISOString()
                ]
            ];
            
            foreach ($testData as $key => $value) {
                Cache::put($key, $value, 1800); // 30 minuti
                $this->line("   ✅ Cache creata: {$key}");
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ Errore cache test: " . $e->getMessage());
        }
    }

    private function verifyPopulation(): void
    {
        $this->info('🔍 Verifica finale cache populate...');
        
        try {
            $redis = Redis::connection();
            
            // Conta per tipo
            $patterns = [
                'stats' => '*stats*',
                'leaderboard' => '*leaderboard*', 
                'challenge' => '*challenge*',
                'user' => '*user*',
                'puzzle' => '*puzzle*',
                'playsudoku' => 'playsudoku:*'
            ];
            
            $totalKeys = 0;
            foreach ($patterns as $type => $pattern) {
                $keys = $redis->keys($pattern);
                $count = count($keys);
                $totalKeys += $count;
                
                if ($count > 0) {
                    $this->line("   📊 {$type}: {$count} chiavi");
                    
                    // Mostra alcune chiavi come esempio
                    foreach (array_slice($keys, 0, 2) as $key) {
                        $ttl = $redis->ttl($key);
                        $this->line("     - {$key} (TTL: {$ttl}s)");
                    }
                }
            }
            
            $this->newLine();
            $this->line("   🎯 TOTALE CHIAVI CACHE: {$totalKeys}");
            
            if ($totalKeys == 0) {
                $this->error("   ⚠️ Nessuna chiave trovata! Verifica configurazione Redis.");
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ Errore verifica: " . $e->getMessage());
        }
    }
}
