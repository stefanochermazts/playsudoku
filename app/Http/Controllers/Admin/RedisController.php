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
                
                // Pattern noti dal debug: rimuovi solo chiavi cache Laravel
                $targetPatterns = [
                    'playsudoku_prod_laravel_cache_*',  // Cache applicazione (hashate)
                    'playsudoku_prod_laravel_cache_framework/*', // Framework cache
                ];
                
                $allKeys = [];
                $deletedCount = 0;
                
                foreach ($targetPatterns as $pattern) {
                    $keys = $redis->keys($pattern);
                    if (!empty($keys)) {
                        $allKeys = array_merge($allKeys, $keys);
                    }
                }
                
                // Rimuovi duplicati
                $allKeys = array_unique($allKeys);
                
                // Log chiavi trovate per debug
                Log::info('Redis reset - chiavi trovate', [
                    'admin_user' => auth()->user()->email,
                    'patterns_checked' => $targetPatterns,
                    'keys_found' => count($allKeys),
                    'sample_keys' => array_slice($allKeys, 0, 5),
                    'timestamp' => now()
                ]);
                
                // Cancella le chiavi in batch per evitare problemi di performance
                if (!empty($allKeys)) {
                    // Dividi in batch da 100 chiavi per evitare timeout
                    $batches = array_chunk($allKeys, 100);
                    
                    foreach ($batches as $batch) {
                        $deleted = $redis->del($batch);
                        $deletedCount += $deleted;
                    }
                }
                
                Log::info('Redis reset completato dall\'admin', [
                    'admin_user' => auth()->user()->email,
                    'total_keys_found' => count($allKeys),
                    'deleted_keys' => $deletedCount,
                    'timestamp' => now()
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => "Redis resettato con successo! Trovate {" . count($allKeys) . "} chiavi, rimosse {$deletedCount}.",
                    'deleted_keys' => $deletedCount,
                    'keys_found' => count($allKeys)
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
            
            // Laravel cripta i nomi delle chiavi cache, quindi analizziamo per categoria
            $categories = [
                'laravel_cache' => [
                    'pattern' => $actualPrefix . '*',
                    'description' => 'Cache applicazione Laravel (chiavi hashate)',
                    'exclude_patterns' => ['*schedule*', '*framework*']
                ],
                'framework' => [
                    'pattern' => $actualPrefix . 'framework/*',
                    'description' => 'Cache framework Laravel (schedule, ecc.)',
                    'exclude_patterns' => []
                ],
                'queues' => [
                    'pattern' => '*queues*',
                    'description' => 'Code di lavoro Redis',
                    'exclude_patterns' => []
                ],
                'sessions' => [
                    'pattern' => '*session*',
                    'description' => 'Sessioni utente',
                    'exclude_patterns' => []
                ]
            ];
            
            $usage = [];

            foreach ($categories as $type => $config) {
                $allKeys = $redis->keys($config['pattern']);
                
                // Filtra chiavi da escludere
                foreach ($config['exclude_patterns'] as $excludePattern) {
                    $excludeKeys = $redis->keys($excludePattern);
                    $allKeys = array_diff($allKeys, $excludeKeys);
                }
                
                // Se è laravel_cache, rimuovi anche framework e queues
                if ($type === 'laravel_cache') {
                    $frameworkKeys = $redis->keys($actualPrefix . 'framework/*');
                    $allKeys = array_diff($allKeys, $frameworkKeys);
                }
                
                $keyCount = count($allKeys);
                $sampleKeys = array_slice($allKeys, 0, 5);
                
                // Analizza TTL
                $ttlData = [];
                $activeKeys = 0;
                $expiredKeys = 0;
                
                foreach (array_slice($allKeys, 0, 10) as $key) {
                    $ttl = $redis->ttl($key);
                    if ($ttl > 0) {
                        $ttlData[] = $ttl;
                        $activeKeys++;
                    } elseif ($ttl === -2) {
                        $expiredKeys++;
                    }
                }
                
                $avgTtl = !empty($ttlData) ? round(array_sum($ttlData) / count($ttlData)) : null;
                
                $usage[$type] = [
                    'description' => $config['description'],
                    'key_count' => $keyCount,
                    'active_keys' => $activeKeys,
                    'expired_keys' => $expiredKeys,
                    'sample_keys' => array_map(function($key) use ($actualPrefix) {
                        // Mostra nome completo ma evidenzia la parte importante
                        if (strlen($key) > 80) {
                            $start = substr($key, 0, 40);
                            $end = substr($key, -20);
                            return $start . '...' . $end;
                        }
                        return str_replace($actualPrefix, '', $key);
                    }, $sampleKeys),
                    'avg_ttl_seconds' => $avgTtl,
                    'avg_ttl_human' => $avgTtl ? $this->formatSeconds($avgTtl) : 'N/A',
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
            // Laravel usa prefissi configurabili - rileva da configurazione
            $redisPrefix = config('database.redis.options.prefix', '');
            $cachePrefix = config('cache.prefix', 'laravel_cache_');
            
            // Costruisci il prefisso completo utilizzato da Laravel
            $fullPrefix = $redisPrefix . $cachePrefix;
            
            // Verifica che esistano chiavi con questo prefisso
            $keys = $redis->keys($fullPrefix . '*');
            if (!empty($keys)) {
                return $fullPrefix;
            }
            
            // Fallback: cerca pattern comuni esistenti
            $commonPrefixes = [
                'playsudoku_prod_laravel_cache_',
                'laravel_cache_',
                'playsudoku_prod_',
                'playsudoku:',
                ''
            ];
            
            foreach ($commonPrefixes as $prefix) {
                $keys = $redis->keys($prefix . '*');
                if (!empty($keys)) {
                    return $prefix;
                }
            }
            
            return $fullPrefix; // Usa quello configurato anche se vuoto
            
        } catch (\Exception $e) {
            Log::warning('Errore rilevamento prefisso cache', ['error' => $e->getMessage()]);
            return 'playsudoku_prod_laravel_cache_'; // Prefisso noto dal debug
        }
    }
}
