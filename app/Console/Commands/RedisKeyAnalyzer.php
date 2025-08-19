<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class RedisKeyAnalyzer extends Command
{
    protected $signature = 'redis:keys {pattern?} {--count=50 : Number of keys to show}';
    protected $description = 'Analyze Redis keys with pattern matching';

    public function handle(): int
    {
        $pattern = $this->argument('pattern') ?: '*';
        $count = (int) $this->option('count');
        
        $this->info("🔍 Analisi chiavi Redis con pattern: {$pattern}");
        $this->newLine();

        try {
            $redis = Redis::connection();
            
            // Trova chiavi con pattern
            $keys = $redis->keys($pattern);
            $total = count($keys);
            
            $this->info("📊 Trovate {$total} chiavi che corrispondono al pattern");
            $this->newLine();
            
            if ($total === 0) {
                $this->warn("❌ Nessuna chiave trovata!");
                $this->suggestAlternatives($redis);
                return 1;
            }
            
            // Analizza pattern delle chiavi
            $this->analyzeKeyPatterns($keys);
            
            // Mostra dettagli delle prime N chiavi
            $this->showKeyDetails($redis, $keys, $count);
            
            // Suggerimenti per il pannello admin
            $this->suggestAdminPanelFix($keys);
            
        } catch (\Exception $e) {
            $this->error("❌ Errore: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function analyzeKeyPatterns(array $keys): void
    {
        $this->info("🧩 Analisi pattern chiavi:");
        
        $patterns = [];
        foreach ($keys as $key) {
            // Estrai pattern (prefisso comune)
            $parts = explode(':', $key);
            $prefix = count($parts) > 1 ? $parts[0] . ':' . $parts[1] : $parts[0];
            
            if (!isset($patterns[$prefix])) {
                $patterns[$prefix] = 0;
            }
            $patterns[$prefix]++;
        }
        
        arsort($patterns);
        
        foreach ($patterns as $pattern => $count) {
            $this->line("   📋 {$pattern}* → {$count} chiavi");
        }
        
        $this->newLine();
    }

    private function showKeyDetails($redis, array $keys, int $count): void
    {
        $this->info("🔍 Dettaglio prime {$count} chiavi:");
        
        $keysToShow = array_slice($keys, 0, $count);
        
        foreach ($keysToShow as $key) {
            $ttl = $redis->ttl($key);
            $type = $redis->type($key);
            
            $ttlText = match ($ttl) {
                -1 => 'NO TTL',
                -2 => 'EXPIRED', 
                default => "{$ttl}s"
            };
            
            $this->line("   🔑 {$key}");
            $this->line("      ⏱️  TTL: {$ttlText} | Type: {$type}");
            
            // Mostra valore se è stringa e non troppo lungo
            if ($type === 'string') {
                try {
                    $value = $redis->get($key);
                    if (strlen($value) < 200) {
                        $this->line("      💾 Valore: " . substr($value, 0, 150) . (strlen($value) > 150 ? '...' : ''));
                    } else {
                        $this->line("      💾 Valore: [" . strlen($value) . " caratteri]");
                    }
                } catch (\Exception $e) {
                    $this->line("      💾 Valore: [Errore lettura]");
                }
            }
            
            $this->newLine();
        }
    }

    private function suggestAlternatives($redis): void
    {
        $this->warn("💡 Proviamo pattern alternativi:");
        
        $alternativePatterns = [
            'laravel_cache:*',
            '*cache*',
            '*stats*',
            '*leaderboard*',
            '*challenge*',
            '*user*',
            '*:playsudoku:*',
            'playsudoku_*'
        ];
        
        foreach ($alternativePatterns as $altPattern) {
            $keys = $redis->keys($altPattern);
            $count = count($keys);
            
            if ($count > 0) {
                $this->line("   ✅ {$altPattern} → {$count} chiavi");
                // Mostra un esempio
                if (isset($keys[0])) {
                    $this->line("      Esempio: {$keys[0]}");
                }
            } else {
                $this->line("   ❌ {$altPattern} → 0 chiavi");
            }
        }
        
        $this->newLine();
    }

    private function suggestAdminPanelFix(array $keys): void
    {
        if (empty($keys)) return;
        
        $this->info("🔧 Suggerimenti per il pannello admin:");
        
        // Analizza prefissi reali
        $commonPrefixes = [];
        foreach ($keys as $key) {
            $parts = explode(':', $key);
            if (count($parts) >= 2) {
                $prefix = $parts[0] . ':' . $parts[1];
                $commonPrefixes[$prefix] = ($commonPrefixes[$prefix] ?? 0) + 1;
            }
        }
        
        arsort($commonPrefixes);
        
        $this->line("   📋 Prefissi più comuni trovati:");
        foreach (array_slice($commonPrefixes, 0, 5, true) as $prefix => $count) {
            $this->line("      - {$prefix}:* ({$count} chiavi)");
        }
        
        $this->newLine();
        
        if (!empty($commonPrefixes)) {
            $mostCommon = array_key_first($commonPrefixes);
            $this->warn("💡 Il pannello admin cerca 'playsudoku:*' ma le chiavi usano '{$mostCommon}:*'");
            $this->line("   Aggiorna il RedisController per cercare pattern: {$mostCommon}:*");
        }
    }
}
