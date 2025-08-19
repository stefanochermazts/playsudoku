<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class RedisController extends Controller
{
    /**
     * Mostra le statistiche e la gestione di Redis
     */
    public function index()
    {
        $redisStats = $this->getRedisStatistics();
        $redisUsage = $this->getRedisUsage();
        
        return view('admin.redis.index', compact('redisStats', 'redisUsage'));
    }

    /**
     * Reset completo di Redis per il sito
     */
    public function reset(Request $request)
    {
        try {
            if (config('cache.default') === 'redis') {
                $redis = Redis::connection();
                
                // Ottieni tutte le chiavi con prefisso rilevato automaticamente
                $actualPrefix = $this->detectActualCachePrefix($redis);
                $keys = $redis->keys($actualPrefix . '*');
                
                // Se non trova chiavi con il prefisso rilevato, prova altri pattern
                if (empty($keys)) {
                    $fallbackPatterns = ['playsudoku:*', '*cache*', 'laravel_cache:*'];
                    foreach ($fallbackPatterns as $pattern) {
                        $keys = $redis->keys($pattern);
                        if (!empty($keys)) break;
                    }
                }
                $deletedCount = 0;
                
                if (!empty($keys)) {
                    $deletedCount = $redis->del($keys);
                }
                
                Log::info('Redis reset completato dall\'admin', [
                    'admin_user' => auth()->user()->email,
                    'deleted_keys' => $deletedCount,
                    'timestamp' => now()
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => "Redis resettato con successo! Rimosse {$deletedCount} chiavi.",
                    'deleted_keys' => $deletedCount
                ]);
            } else {
                // Fallback per altri cache drivers
                Cache::flush();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Cache driver non-Redis: usato flush() generale.',
                    'deleted_keys' => 'N/A'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Errore durante reset Redis dall\'admin', [
                'admin_user' => auth()->user()->email,
                'error' => $e->getMessage(),
                'timestamp' => now()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il reset: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset parziale per tipo specifico di cache
     */
    public function resetByType(Request $request, string $type)
    {
        $validated = $request->validate([
            'confirm' => 'required|boolean'
        ]);

        if (!$validated['confirm']) {
            return response()->json([
                'success' => false,
                'message' => 'Conferma richiesta per procedere'
            ], 400);
        }

        $validTypes = ['leaderboard', 'challenge', 'user', 'puzzle', 'stats'];
        
        if (!in_array($type, $validTypes)) {
            return response()->json([
                'success' => false,
                'message' => 'Tipo di cache non valido'
            ], 400);
        }

        try {
            if (config('cache.default') === 'redis') {
                $redis = Redis::connection();
                
                $actualPrefix = $this->detectActualCachePrefix($redis);
                $patterns = [
                    "{$actualPrefix}{$type}:*",
                    "playsudoku:{$type}:*",
                    "*{$type}*"
                ];
                
                $keys = [];
                foreach ($patterns as $pattern) {
                    $patternKeys = $redis->keys($pattern);
                    $keys = array_merge($keys, $patternKeys);
                }
                $keys = array_unique($keys);
                $deletedCount = 0;
                
                if (!empty($keys)) {
                    $deletedCount = $redis->del($keys);
                }
                
                Log::info("Redis reset parziale per tipo {$type}", [
                    'admin_user' => auth()->user()->email,
                    'type' => $type,
                    'deleted_keys' => $deletedCount,
                    'timestamp' => now()
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => "Cache {$type} resettata! Rimosse {$deletedCount} chiavi.",
                    'deleted_keys' => $deletedCount
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Reset parziale disponibile solo con Redis'
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error("Errore durante reset Redis tipo {$type}", [
                'admin_user' => auth()->user()->email,
                'type' => $type,
                'error' => $e->getMessage(),
                'timestamp' => now()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il reset: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ottieni statistiche generali di Redis
     */
    private function getRedisStatistics(): array
    {
        try {
            if (config('cache.default') !== 'redis') {
                return [
                    'status' => 'not_redis',
                    'driver' => config('cache.default'),
                    'message' => 'Cache driver corrente non è Redis'
                ];
            }

            $redis = Redis::connection();
            $info = $redis->info();
            
            // Conta chiavi con prefisso reale rilevato automaticamente
            $actualPrefix = $this->detectActualCachePrefix($redis);
            $allKeys = $redis->keys($actualPrefix . '*');
            if (empty($allKeys)) {
                // Fallback: cerca qualsiasi chiave cache-like
                $allKeys = $redis->keys('*cache*');
                if (empty($allKeys)) {
                    $allKeys = $redis->keys('*');
                }
            }
            $totalKeys = count($allKeys);
            
            // Analizza memoria utilizzata
            $usedMemory = $info['used_memory'] ?? 0;
            $usedMemoryHuman = $info['used_memory_human'] ?? '0B';
            $maxMemory = $info['maxmemory'] ?? 0;
            
            return [
                'status' => 'connected',
                'redis_version' => $info['redis_version'] ?? 'N/A',
                'connected_clients' => $info['connected_clients'] ?? 0,
                'used_memory' => $usedMemory,
                'used_memory_human' => $usedMemoryHuman,
                'max_memory' => $maxMemory,
                'total_keys' => $totalKeys,
                'uptime_in_seconds' => $info['uptime_in_seconds'] ?? 0,
                'uptime_in_days' => round(($info['uptime_in_seconds'] ?? 0) / 86400, 1),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Analizza l'utilizzo di Redis per tipo di cache
     */
    private function getRedisUsage(): array
    {
        try {
            if (config('cache.default') !== 'redis') {
                return [];
            }

            $redis = Redis::connection();
            
            // Rileva automaticamente il prefisso reale utilizzato
            $actualPrefix = $this->detectActualCachePrefix($redis);
            
            $cacheTypes = ['leaderboard', 'challenge', 'user', 'puzzle', 'stats'];
            $usage = [];

            foreach ($cacheTypes as $type) {
                // Prova vari pattern possibili
                $patterns = [
                    "{$actualPrefix}{$type}:*",
                    "playsudoku:{$type}:*",
                    "*{$type}*",
                    "{$actualPrefix}*{$type}*"
                ];
                
                $allKeys = [];
                foreach ($patterns as $pattern) {
                    $keys = $redis->keys($pattern);
                    $allKeys = array_merge($allKeys, $keys);
                }
                
                // Rimuovi duplicati
                $allKeys = array_unique($allKeys);
                
                $keyCount = count($allKeys);
                $sampleKeys = array_slice($allKeys, 0, 5);
                
                // Ottieni TTL per alcune chiavi campione
                $ttlData = [];
                foreach (array_slice($allKeys, 0, 3) as $key) {
                    $ttl = $redis->ttl($key);
                    if ($ttl > 0) {
                        $ttlData[] = $ttl;
                    }
                }
                
                $avgTtl = !empty($ttlData) ? round(array_sum($ttlData) / count($ttlData)) : null;
                
                $usage[$type] = [
                    'description' => $this->getCacheTypeDescription($type),
                    'key_count' => $keyCount,
                    'sample_keys' => array_map(function($key) use ($actualPrefix) {
                        // Rimuovi il prefisso per visualizzazione più pulita
                        return str_replace([$actualPrefix, 'playsudoku:'], '', $key);
                    }, $sampleKeys),
                    'avg_ttl_seconds' => $avgTtl,
                    'avg_ttl_human' => $avgTtl ? $this->formatSeconds($avgTtl) : null,
                    'patterns_used' => $patterns[0], // Mostra quale pattern ha funzionato
                ];
            }

            return $usage;
        } catch (\Exception $e) {
            Log::error('Errore nell\'analisi utilizzo Redis', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Ottieni descrizione per tipo di cache
     */
    private function getCacheTypeDescription(string $type): string
    {
        return match($type) {
            'leaderboard' => 'Classifiche delle sfide (TTL: 5 min)',
            'challenge' => 'Dettagli delle sfide (TTL: 10 min)', 
            'user' => 'Statistiche utenti (TTL: 15 min)',
            'puzzle' => 'Dati dei puzzle (TTL: 30 min)',
            'stats' => 'Statistiche giornaliere (TTL: 1 ora)',
            default => 'Cache generica'
        };
    }

    /**
     * Formatta secondi in formato leggibile
     */
    private function formatSeconds(int $seconds): string
    {
        if ($seconds < 60) {
            return "{$seconds}s";
        } elseif ($seconds < 3600) {
            return round($seconds / 60) . "m";
        } else {
            return round($seconds / 3600, 1) . "h";
        }
    }

    /**
     * Rileva automaticamente il prefisso cache reale utilizzato
     */
    private function detectActualCachePrefix($redis): string
    {
        try {
            // Crea una chiave di test per rilevare il prefisso reale
            $testKey = 'test_prefix_detection_' . time();
            cache()->put($testKey, 'test', 10);
            
            // Cerca la chiave appena creata per vedere come è stata memorizzata
            $allKeys = $redis->keys('*' . $testKey . '*');
            
            if (!empty($allKeys)) {
                $actualKey = $allKeys[0];
                $prefix = str_replace($testKey, '', $actualKey);
                
                // Pulisci la chiave di test
                cache()->forget($testKey);
                
                return $prefix;
            }
            
            // Fallback: cerca pattern comuni esistenti
            $commonPrefixes = [
                'laravel_cache:',
                'playsudoku:',
                config('app.name', 'laravel') . '_cache:',
                ''
            ];
            
            foreach ($commonPrefixes as $prefix) {
                $keys = $redis->keys($prefix . '*');
                if (!empty($keys)) {
                    return $prefix;
                }
            }
            
            return 'playsudoku:'; // Default fallback
            
        } catch (\Exception $e) {
            Log::warning('Errore rilevamento prefisso cache', ['error' => $e->getMessage()]);
            return 'playsudoku:'; // Default fallback
        }
    }
}
