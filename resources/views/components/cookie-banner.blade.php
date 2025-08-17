{{-- Cookie Container con una singola istanza --}}
<div x-data="cookieBanner()">
    
    {{-- Cookie Banner --}}
    <div x-show="showBanner" 
         x-cloak
         class="fixed bottom-0 left-0 right-0 w-full bg-white dark:bg-gray-800 border-t-2 border-gray-200 dark:border-gray-700 shadow-lg p-3 sm:p-4 md:p-6"
         style="z-index: 9998 !important;"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="transform translate-y-full"
         x-transition:enter-end="transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="transform translate-y-0"
         x-transition:leave-end="transform translate-y-full">
        
        <div class="max-w-7xl mx-auto px-2 sm:px-4">
            <div class="flex flex-col gap-3 sm:gap-4">
                
                {{-- Banner Content --}}
                <div class="flex-1">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-1 sm:mb-2">
                        🍪 {{ __('app.cookies.banner_title') }}
                    </h3>
                    <p class="text-xs sm:text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                        {{ __('app.cookies.banner_text') }}
                        <a href="{{ route('localized.cookie-policy', ['locale' => app()->getLocale()]) }}" 
                           class="text-blue-600 dark:text-blue-400 hover:underline font-medium">
                            {{ __('app.cookies.title') }}
                        </a>
                    </p>
                </div>
                
                {{-- Banner Actions --}}
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                    <button @click="acceptEssential()" 
                            class="w-full sm:w-auto px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                        {{ __('app.cookies.accept_essential') }}
                    </button>
                    <button @click="openPreferences()" 
                            class="w-full sm:w-auto px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg transition-colors">
                        {{ __('app.cookies.manage_preferences') }}
                    </button>
                    <button @click="acceptAll()" 
                            class="w-full sm:w-auto px-4 sm:px-6 py-2 text-xs sm:text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">
                        {{ __('app.cookies.accept_all') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Cookie Preferences Modal --}}
    <div x-show="showPreferences" 
         x-cloak
         @keydown.escape.window="showPreferences = false"
         class="fixed inset-0 overflow-y-auto"
         style="z-index: 99999 !important;"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
    
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
    
    {{-- Modal --}}
    <div class="flex min-h-full items-end sm:items-center justify-center p-2 sm:p-4">
        <div @click.away="showPreferences = false"
             class="relative w-full max-w-2xl bg-white dark:bg-gray-800 rounded-t-xl sm:rounded-xl shadow-xl"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="transform translate-y-full sm:scale-95 opacity-0"
             x-transition:enter-end="transform translate-y-0 sm:scale-100 opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="transform translate-y-0 sm:scale-100 opacity-100"
             x-transition:leave-end="transform translate-y-full sm:scale-95 opacity-0">
            
            {{-- Modal Header --}}
            <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                    🍪 {{ __('app.cookies.manage_preferences') }}
                </h2>
                <button @click="showPreferences = false" 
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            {{-- Modal Content --}}
            <div class="p-6 space-y-6">
                
                {{-- Essential Cookies --}}
                <div class="flex items-start justify-between">
                    <div class="flex-1 mr-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                            🔒 {{ __('app.cookies.essential_cookies') }}
                        </h3>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            {{ __('app.cookies.essential_description') }}
                        </p>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="px-3 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-100 text-sm font-medium rounded-full">
                            Sempre Attivi
                        </div>
                    </div>
                </div>
                
                {{-- Analytics Cookies --}}
                <div class="flex items-start justify-between">
                    <div class="flex-1 mr-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                            📊 {{ __('app.cookies.analytics_cookies') }}
                        </h3>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            {{ __('app.cookies.analytics_description') }}
                        </p>
                    </div>
                    <div class="flex-shrink-0">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" x-model="consent.analytics" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                </div>
                
                {{-- Marketing Cookies --}}
                <div class="flex items-start justify-between opacity-50">
                    <div class="flex-1 mr-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                            🎯 {{ __('app.cookies.marketing_cookies') }}
                        </h3>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            Non utilizzati attualmente
                        </p>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 text-sm font-medium rounded-full">
                            Non Attivi
                        </div>
                    </div>
                </div>
                
            </div>
            
            {{-- Modal Footer --}}
            <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200 dark:border-gray-700">
                <button @click="acceptEssential()" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                    Solo Essenziali
                </button>
                <button @click="savePreferences()" 
                        class="px-6 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">
                    Salva Preferenze
                </button>
            </div>
        </div>
    </div>

</div> {{-- Chiusura del container principale --}}

<script>
// Funzione globale cookieBanner
window.cookieBanner = function() {
    return {
        showBanner: false,
        showPreferences: false,
        consent: {
            essential: true,
            analytics: false,
            marketing: false
        },
        
        async init() {
            console.log('Cookie banner initializing...');
            
            // First check backend for existing consent
            const backendHasConsent = await this.loadConsentFromBackend();
            
            // If backend doesn't have consent, check localStorage
            if (!backendHasConsent) {
                const savedConsent = localStorage.getItem('cookie-consent');
                if (savedConsent) {
                    this.consent = JSON.parse(savedConsent);
                    this.applyConsent();
                    console.log('Cookie consent loaded from localStorage');
                } else {
                    // No consent anywhere - show banner
                    this.showBanner = true;
                    console.log('No consent found - showing cookie banner');
                }
            }
            
            // Make debug functions globally available
            window.showCookieBanner = () => {
                this.showPreferences = true;
            };
            
            window.resetCookieConsent = () => {
                localStorage.removeItem('cookie-consent');
                localStorage.removeItem('cookie-consent-date');
                this.consent = { essential: undefined, analytics: undefined, marketing: undefined };
                this.showBanner = true;
                this.showPreferences = false;
                console.log('Cookie consent reset');
            };
            
            window.testCookieBanner = () => {
                console.log('Cookie banner state:', {
                    showBanner: this.showBanner,
                    showPreferences: this.showPreferences,
                    consent: this.consent,
                    hasValidConsent: this.hasValidConsent()
                });
            };
        },
        
        async loadConsentFromBackend() {
            try {
                const response = await fetch('/api/consent/status', {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });
                
                const result = await response.json();
                console.log('Backend consent response:', result);
                
                if (result.success && result.has_consented) {
                    this.consent = result.consents;
                    this.applyConsent();
                    console.log('Cookie consent loaded from backend:', this.consent);
                    return true;
                } else if (result.success && !result.has_consented) {
                    console.log('Backend says no consent given yet');
                    return false;
                }
            } catch (error) {
                console.log('Could not load consent from backend:', error);
            }
            return false;
        },
        
        hasValidConsent() {
            // Check if we have explicit consent (even if declined)
            return this.consent.essential !== undefined && this.consent.essential !== null;
        },
        
        openPreferences() {
            console.log('openPreferences called');
            this.showPreferences = true;
            console.log('showPreferences set to:', this.showPreferences);
        },
        
        acceptAll() {
            console.log('acceptAll called');
            this.consent = {
                essential: true,
                analytics: true,
                marketing: false
            };
            this.saveConsent();
        },
        
        acceptEssential() {
            console.log('acceptEssential called');
            this.consent = {
                essential: true,
                analytics: false,
                marketing: false
            };
            this.saveConsent();
        },
        
        savePreferences() {
            console.log('savePreferences called');
            this.saveConsent();
        },
        
        async saveConsent() {
            console.log('saveConsent called with:', this.consent);
            
            try {
                // Save to backend first
                const response = await fetch('/api/consent', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify(this.consent)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    console.log('Consent saved to backend successfully');
                    
                    // Save to localStorage as backup
                    localStorage.setItem('cookie-consent', JSON.stringify(this.consent));
                    localStorage.setItem('cookie-consent-date', new Date().toISOString());
                    
                    this.showBanner = false;
                    this.showPreferences = false;
                    this.applyConsent();
                    console.log('Banner and preferences hidden');
                } else {
                    console.error('Failed to save consent to backend:', result);
                    // Fallback to localStorage only
                    this.saveConsentLocal();
                }
            } catch (error) {
                console.error('Error saving consent:', error);
                // Fallback to localStorage only
                this.saveConsentLocal();
            }
        },
        
        saveConsentLocal() {
            console.log('Saving consent to localStorage only');
            localStorage.setItem('cookie-consent', JSON.stringify(this.consent));
            localStorage.setItem('cookie-consent-date', new Date().toISOString());
            this.showBanner = false;
            this.showPreferences = false;
            this.applyConsent();
        },
        
        applyConsent() {
            // Apply analytics consent
            if (this.consent.analytics) {
                this.enableAnalytics();
            } else {
                this.disableAnalytics();
            }
            
            // Apply marketing consent (if needed in future)
            if (this.consent.marketing) {
                this.enableMarketing();
            } else {
                this.disableMarketing();
            }
        },
        
        enableAnalytics() {
            console.log('Analytics enabled');
            if (typeof gtag !== 'undefined') {
                gtag('consent', 'update', {
                    'analytics_storage': 'granted'
                });
            }
        },
        
        disableAnalytics() {
            console.log('Analytics disabled');
            if (typeof gtag !== 'undefined') {
                gtag('consent', 'update', {
                    'analytics_storage': 'denied'
                });
            }
        },
        
        enableMarketing() {
            console.log('Marketing cookies enabled');
        },
        
        disableMarketing() {
            console.log('Marketing cookies disabled');
        }
    };
};
</script>

<style>
[x-cloak] { 
    display: none !important; 
}
</style>