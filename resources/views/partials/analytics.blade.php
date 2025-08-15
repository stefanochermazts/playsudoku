{{-- Google Analytics 4 Implementation --}}
@if(config('analytics.google.enabled') && config('analytics.google.tracking_id') && app()->environment(config('analytics.auto_enable_environments', ['production'])))
    {{-- Google tag (gtag.js) --}}
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('analytics.google.tracking_id') }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        
        {{-- Initialize with timestamp --}}
        gtag('js', new Date());

        @if(config('analytics.google.consent_mode'))
            {{-- Consent Mode per GDPR compliance --}}
            gtag('consent', 'default', {
                'analytics_storage': 'denied',
                'ad_storage': 'denied',
                'wait_for_update': 500
            });
        @endif

        {{-- Configure Google Analytics --}}
        gtag('config', '{{ config('analytics.google.tracking_id') }}', {
            @if(config('analytics.google.anonymize_ip'))
                'anonymize_ip': true,
            @endif
            @if(config('analytics.google.debug'))
                'debug_mode': true,
            @endif
            'cookie_domain': 'auto',
            'cookie_expires': {{ config('analytics.privacy.data_retention_months', 14) * 30 * 24 * 60 * 60 }}, {{-- Convert months to seconds --}}
            'send_page_view': true,
            @auth
                'user_id': '{{ hash('sha256', auth()->id() . config('app.key')) }}', {{-- Hashed user ID for privacy --}}
            @endauth
        });

        @if(config('analytics.google.consent_mode'))
            {{-- Function to grant consent (called when user accepts cookies) --}}
            window.grantAnalyticsConsent = function() {
                gtag('consent', 'update', {
                    'analytics_storage': 'granted'
                });
                
                {{-- Store consent in localStorage --}}
                localStorage.setItem('analytics_consent', 'granted');
                
                {{-- Send event about consent granted --}}
                gtag('event', 'consent_granted', {
                    'event_category': 'privacy',
                    'event_label': 'analytics_consent'
                });
            };

            {{-- Function to deny consent --}}
            window.denyAnalyticsConsent = function() {
                gtag('consent', 'update', {
                    'analytics_storage': 'denied'
                });
                
                localStorage.setItem('analytics_consent', 'denied');
                
                gtag('event', 'consent_denied', {
                    'event_category': 'privacy',
                    'event_label': 'analytics_consent'
                });
            };

            {{-- Check existing consent on page load --}}
            const existingConsent = localStorage.getItem('analytics_consent');
            if (existingConsent === 'granted') {
                grantAnalyticsConsent();
            } else if (existingConsent === 'denied') {
                denyAnalyticsConsent();
            }
        @endif

        {{-- Custom events for PlaySudoku --}}
        @auth
            {{-- Track authenticated user properties --}}
            gtag('set', {
                'user_properties': {
                    'user_role': '{{ auth()->user()->role }}',
                    'user_registration_date': '{{ auth()->user()->created_at->format('Y-m') }}',
                    'locale': '{{ app()->getLocale() }}'
                }
            });
        @endauth

        {{-- Track page locale --}}
        gtag('set', {
            'language': '{{ app()->getLocale() }}',
            'country': 'IT', {{-- Default, può essere dinamico --}}
        });

        @if(config('app.debug'))
            console.log('Google Analytics initialized with ID: {{ config('analytics.google.tracking_id') }}');
        @endif
    </script>

    {{-- Enhanced Ecommerce tracking for challenges (if applicable) --}}
    @stack('analytics-events')
@elseif(config('app.debug'))
    {{-- Debug message when analytics is disabled --}}
    <script>
        console.log('Google Analytics disabled in {{ app()->environment() }} environment');
    </script>
@endif
