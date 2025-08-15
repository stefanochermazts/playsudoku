<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware di sicurezza aggiuntivo per endpoint sensibili
 */
class SecurityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $type = 'default'): Response
    {
        // Rate limiting specifico per tipo di azione
        $this->applyRateLimiting($request, $type);
        
        // Headers di sicurezza
        $response = $next($request);
        
        // Aggiungi headers di sicurezza
        $this->addSecurityHeaders($response);
        
        return $response;
    }
    
    /**
     * Applica rate limiting basato sul tipo di azione
     */
    private function applyRateLimiting(Request $request, string $type): void
    {
        $limits = [
            'livewire' => [
                'maxAttempts' => 60,  // 60 richieste per minuto
                'decayMinutes' => 1,
            ],
            'admin' => [
                'maxAttempts' => 30,  // 30 azioni admin per minuto
                'decayMinutes' => 1,
            ],
            'challenge' => [
                'maxAttempts' => 10,  // 10 submit sfide per minuto
                'decayMinutes' => 1,
            ],
            'api' => [
                'maxAttempts' => 100, // 100 chiamate API per minuto
                'decayMinutes' => 1,
            ],
            'default' => [
                'maxAttempts' => 120, // 120 richieste generiche per minuto
                'decayMinutes' => 1,
            ],
        ];
        
        $config = $limits[$type] ?? $limits['default'];
        $key = $this->getThrottleKey($request, $type);
        
        if (RateLimiter::tooManyAttempts($key, $config['maxAttempts'])) {
            $seconds = RateLimiter::availableIn($key);
            abort(429, "Troppe richieste. Riprova tra {$seconds} secondi.");
        }
        
        RateLimiter::hit($key, $config['decayMinutes'] * 60);
    }
    
    /**
     * Genera chiave di throttling
     */
    private function getThrottleKey(Request $request, string $type): string
    {
        $ip = $request->ip();
        $userId = auth()->id() ?? 'anonymous';
        return "security:{$type}:{$userId}:{$ip}";
    }
    
    /**
     * Aggiungi headers di sicurezza alla response
     */
    private function addSecurityHeaders(Response $response): void
    {
        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
        ];
        
        // Solo in produzione
        if (app()->environment('production')) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }
        
        foreach ($headers as $name => $value) {
            $response->headers->set($name, $value);
        }
    }
}
