<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use App\Services\CacheService;

class RedisDebug extends Command
{
    protected $signature = 'redis:debug {--clear : Clear test keys after debug}';
    protected $description = 'Debug Redis configuration and key storage';

    public function handle(): int
    {
        $this->info('🔍 Redis Debug & Diagnostics');
        $this->newLine();

        // 1. Verifica configurazione
        $this->checkConfiguration();
        
        // 2. Test connessione base
        $this->testConnection();
        
        // 3. Test cache Laravel
        $this->testLaravelCache();
        
        // 4. Lista chiavi esistenti
        $this->listExistingKeys();
        
        // 5. Test CacheService
        $this->testCacheService();
        
        // 6. Cleanup se richiesto
        if ($this->option('clear')) {
            $this->cleanupTestKeys();
        }

        return 0;
    }

    private function checkConfiguration(): void
    {
        $this->info('📋 Configurazione Redis:');
        
        $cacheDefault = config('cache.default');
        $this->line("   Cache driver: {$cacheDefault}");
        
        $redisConfig = config('database.redis.cache');
        $this->line("   Redis host: {$redisConfig['host']}:{$redisConfig['port']}");
        $this->line("   Redis database: {$redisConfig['database']}");
        
        $redisPrefix = config('database.redis.options.prefix');
        $this->line("   Redis prefix: {$redisPrefix}");
        
        $cachePrefix = config('cache.prefix');
        $this->line("   Cache prefix: {$cachePrefix}");
        
        $this->newLine();
    }

    private function testConnection(): void
    {
        $this->info('🔌 Test Connessione Redis:');
        
        try {
            $redis = Redis::connection();
            $pong = $redis->ping();
            $this->line("   ✅ Ping: {$pong}");
            
            $info = $redis->info();
            $this->line("   ✅ Redis version: {$info['redis_version']}");
            $this->line("   ✅ Memoria usata: {$info['used_memory_human']}");
            
        } catch (\Exception $e) {
            $this->error("   ❌ Errore connessione: " . $e->getMessage());
        }
        
        $this->newLine();
    }

    private function testLaravelCache(): void
    {
        $this->info('💾 Test Cache Laravel:');
        
        try {
            // Test put/get
            $testKey = 'redis_debug_test_' . time();
            $testValue = ['test' => 'data', 'timestamp' => now()->toISOString()];
            
            $putResult = Cache::put($testKey, $testValue, 300);
            $this->line("   ✅ Cache::put: " . ($putResult ? 'OK' : 'FAILED'));
            
            $getValue = Cache::get($testKey);
            $this->line("   ✅ Cache::get: " . ($getValue ? 'OK' : 'FAILED'));
            
            if ($getValue) {
                $this->line("   📄 Valore recuperato: " . json_encode($getValue));
            }
            
            // Test remember
            $rememberKey = 'redis_debug_remember_' . time();
            $rememberValue = Cache::remember($rememberKey, 300, function() {
                return ['computed' => true, 'time' => now()->toISOString()];
            });
            $this->line("   ✅ Cache::remember: " . ($rememberValue ? 'OK' : 'FAILED'));
            
        } catch (\Exception $e) {
            $this->error("   ❌ Errore cache: " . $e->getMessage());
        }
        
        $this->newLine();
    }

    private function listExistingKeys(): void
    {
        $this->info('🔑 Chiavi Redis Esistenti:');
        
        try {
            $redis = Redis::connection();
            
            // Tutte le chiavi
            $allKeys = $redis->keys('*');
            $this->line("   📊 Totale chiavi: " . count($allKeys));
            
            // Filtra chiavi Laravel/cache
            $patterns = ['laravel_cache:', 'playsudoku:', '*cache*', '*stats*', '*leaderboard*'];
            
            foreach ($patterns as $pattern) {
                $matchingKeys = $redis->keys($pattern);
                if (!empty($matchingKeys)) {
                    $this->line("   🔍 Pattern '{$pattern}': " . count($matchingKeys) . " chiavi");
                    foreach (array_slice($matchingKeys, 0, 5) as $key) {
                        $ttl = $redis->ttl($key);
                        $this->line("     - {$key} (TTL: {$ttl}s)");
                    }
                    if (count($matchingKeys) > 5) {
                        $this->line("     ... e altre " . (count($matchingKeys) - 5) . " chiavi");
                    }
                }
            }
            
            // Mostra alcune chiavi casuali se non troviamo pattern noti
            if (count($allKeys) > 0 && count($allKeys) <= 20) {
                $this->line("   📋 Tutte le chiavi:");
                foreach ($allKeys as $key) {
                    $ttl = $redis->ttl($key);
                    $this->line("     - {$key} (TTL: {$ttl}s)");
                }
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ Errore lettura chiavi: " . $e->getMessage());
        }
        
        $this->newLine();
    }

    private function testCacheService(): void
    {
        $this->info('🎯 Test CacheService:');
        
        try {
            $cacheService = app(CacheService::class);
            
            // Test statistiche giornaliere
            $stats = $cacheService->getDailySystemStats();
            $this->line("   ✅ getDailySystemStats: " . json_encode($stats));
            
            // Verifica se la cache è stata creata
            $redis = Redis::connection();
            $statsKeys = $redis->keys('*stats*');
            $this->line("   📊 Chiavi stats trovate: " . count($statsKeys));
            foreach ($statsKeys as $key) {
                $ttl = $redis->ttl($key);
                $this->line("     - {$key} (TTL: {$ttl}s)");
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ Errore CacheService: " . $e->getMessage());
        }
        
        $this->newLine();
    }

    private function cleanupTestKeys(): void
    {
        $this->info('🧹 Pulizia chiavi di test:');
        
        try {
            $redis = Redis::connection();
            $testKeys = $redis->keys('*redis_debug*');
            
            if (!empty($testKeys)) {
                $deleted = $redis->del($testKeys);
                $this->line("   ✅ Rimosse {$deleted} chiavi di test");
            } else {
                $this->line("   ℹ️ Nessuna chiave di test da rimuovere");
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ Errore cleanup: " . $e->getMessage());
        }
    }
}
