<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Challenge;
use App\Models\ChallengeAttempt;

/**
 * Servizio per eventi Analytics personalizzati di PlaySudoku
 */
class AnalyticsService
{
    /**
     * Verifica se analytics è abilitato
     */
    public function isEnabled(): bool
    {
        return config('analytics.google.enabled') && 
               config('analytics.google.tracking_id') && 
               app()->environment(config('analytics.auto_enable_environments', ['production']));
    }

    /**
     * Genera JavaScript per evento di registrazione utente
     */
    public function generateUserRegistrationEvent(User $user): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        return "
        <script>
            if (typeof gtag !== 'undefined') {
                gtag('event', 'sign_up', {
                    'method': 'email',
                    'event_category': 'engagement',
                    'event_label': 'user_registration',
                    'value': 1
                });
            }
        </script>";
    }

    /**
     * Genera JavaScript per evento di login
     */
    public function generateUserLoginEvent(User $user): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        return "
        <script>
            if (typeof gtag !== 'undefined') {
                gtag('event', 'login', {
                    'method': 'email',
                    'event_category': 'engagement',
                    'event_label': 'user_login'
                });
            }
        </script>";
    }

    /**
     * Genera JavaScript per evento di completamento sfida
     */
    public function generateChallengeCompletedEvent(ChallengeAttempt $attempt): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        $challenge = $attempt->challenge;
        $durationMinutes = round($attempt->duration_ms / 1000 / 60, 2);

        return "
        <script>
            if (typeof gtag !== 'undefined') {
                gtag('event', 'level_end', {
                    'level_name': '{$challenge->type}_challenge',
                    'success': true,
                    'score': " . (3600000 - $attempt->duration_ms) . ", // Higher score for faster completion
                    'custom_parameters': {
                        'challenge_type': '{$challenge->type}',
                        'difficulty': '" . ($challenge->puzzle->difficulty ?? 'unknown') . "',
                        'duration_minutes': {$durationMinutes},
                        'errors_count': {$attempt->errors_count},
                        'hints_used': {$attempt->hints_used}
                    },
                    'event_category': 'game',
                    'event_label': 'challenge_completed',
                    'value': " . (5 - $attempt->errors_count) . " // Better score for fewer errors
                });
                
                // Track engagement
                gtag('event', 'engagement_time', {
                    'event_category': 'game',
                    'name': 'challenge_engagement',
                    'value': " . round($attempt->duration_ms / 1000) . " // Duration in seconds
                });
            }
        </script>";
    }

    /**
     * Genera JavaScript per evento di avvio sfida
     */
    public function generateChallengeStartedEvent(Challenge $challenge): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        return "
        <script>
            if (typeof gtag !== 'undefined') {
                gtag('event', 'level_start', {
                    'level_name': '{$challenge->type}_challenge',
                    'custom_parameters': {
                        'challenge_type': '{$challenge->type}',
                        'difficulty': '" . ($challenge->puzzle->difficulty ?? 'unknown') . "',
                        'challenge_id': {$challenge->id}
                    },
                    'event_category': 'game',
                    'event_label': 'challenge_started'
                });
            }
        </script>";
    }

    /**
     * Genera JavaScript per evento di utilizzo hint
     */
    public function generateHintUsedEvent(string $challengeType, string $difficulty): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        return "
        <script>
            if (typeof gtag !== 'undefined') {
                gtag('event', 'tutorial_begin', {
                    'custom_parameters': {
                        'challenge_type': '{$challengeType}',
                        'difficulty': '{$difficulty}',
                        'help_type': 'hint'
                    },
                    'event_category': 'game',
                    'event_label': 'hint_used'
                });
            }
        </script>";
    }

    /**
     * Genera JavaScript per evento di condivisione risultato
     */
    public function generateShareEvent(string $platform, string $contentType): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        return "
        <script>
            if (typeof gtag !== 'undefined') {
                gtag('event', 'share', {
                    'method': '{$platform}',
                    'content_type': '{$contentType}',
                    'event_category': 'social',
                    'event_label': 'result_share'
                });
            }
        </script>";
    }

    /**
     * Genera JavaScript per evento di export dati
     */
    public function generateDataExportEvent(string $dataType): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        return "
        <script>
            if (typeof gtag !== 'undefined') {
                gtag('event', 'file_download', {
                    'file_extension': 'csv',
                    'file_name': '{$dataType}_export',
                    'custom_parameters': {
                        'data_type': '{$dataType}'
                    },
                    'event_category': 'engagement',
                    'event_label': 'data_export'
                });
            }
        </script>";
    }

    /**
     * Genera JavaScript per evento di cambio tema
     */
    public function generateThemeChangeEvent(string $newTheme): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        return "
        <script>
            if (typeof gtag !== 'undefined') {
                gtag('event', 'select_content', {
                    'content_type': 'theme',
                    'item_id': '{$newTheme}',
                    'custom_parameters': {
                        'theme_selected': '{$newTheme}'
                    },
                    'event_category': 'ui',
                    'event_label': 'theme_change'
                });
            }
        </script>";
    }

    /**
     * Genera JavaScript per errore/problema tecnico
     */
    public function generateErrorEvent(string $errorType, string $errorMessage = ''): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        $safeMessage = htmlspecialchars(substr($errorMessage, 0, 100), ENT_QUOTES, 'UTF-8');

        return "
        <script>
            if (typeof gtag !== 'undefined') {
                gtag('event', 'exception', {
                    'description': '{$errorType}: {$safeMessage}',
                    'fatal': false,
                    'custom_parameters': {
                        'error_type': '{$errorType}'
                    },
                    'event_category': 'error'
                });
            }
        </script>";
    }

    /**
     * Genera JavaScript per evento di performance (per monitoraggio)
     */
    public function generatePerformanceEvent(string $metricName, float $value, string $unit = 'ms'): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        return "
        <script>
            if (typeof gtag !== 'undefined') {
                gtag('event', 'timing_complete', {
                    'name': '{$metricName}',
                    'value': {$value},
                    'custom_parameters': {
                        'metric_unit': '{$unit}'
                    },
                    'event_category': 'performance'
                });
            }
        </script>";
    }

    /**
     * Helper per includere eventi nelle view via stack
     */
    public function pushEventToStack(string $eventScript): void
    {
        if ($this->isEnabled() && !empty(trim($eventScript))) {
            // Remove outer script tags if present for clean stacking
            $cleanScript = preg_replace('/^\s*<script[^>]*>|<\/script>\s*$/i', '', trim($eventScript));
            
            view()->startPush('analytics-events');
            echo "<script>{$cleanScript}</script>";
            view()->stopPush();
        }
    }
}
