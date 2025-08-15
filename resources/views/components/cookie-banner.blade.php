{{-- Cookie Banner Component --}}
<div id="cookie-banner" 
     x-data="cookieBanner()" 
     x-show="showBanner" 
     x-cloak
     class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t-2 border-gray-200 dark:border-gray-700 shadow-lg z-50 p-4 md:p-6">
    
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            
            {{-- Banner Content --}}
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                    🍪 {{ __('app.cookies.banner_title') }}
                </h3>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                    {{ __('app.cookies.banner_text') }}
                    <a href="{{ route('localized.cookie-policy', ['locale' => app()->getLocale()]) }}" 
                       class="text-blue-600 dark:text-blue-400 hover:underline font-medium">
                        {{ __('app.cookies.title') }}
                    </a>
                </p>
            </div>
            
            {{-- Banner Actions --}}
            <div class="flex flex-col sm:flex-row gap-3 min-w-fit">
                <button @click="acceptEssential()" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                    {{ __('app.cookies.accept_essential') }}
                </button>
                <button @click="showPreferences = true" 
                        class="px-4 py-2 text-sm font-medium text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg transition-colors">
                    {{ __('app.cookies.manage_preferences') }}
                </button>
                <button @click="acceptAll()" 
                        class="px-6 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">
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
     class="fixed inset-0 z-50 overflow-y-auto">
    
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
    
    {{-- Modal --}}
    <div class="flex min-h-full items-center justify-center p-4">
        <div @click.away="showPreferences = false"
             class="relative w-full max-w-2xl bg-white dark:bg-gray-800 rounded-xl shadow-xl">
            
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
</div>

<script>
function cookieBanner() {
    return {
        showBanner: false,
        showPreferences: false,
        consent: {
            essential: true,
            analytics: false,
            marketing: false
        },
        
        init() {
            // Check if consent has already been given
            const savedConsent = localStorage.getItem('cookie-consent');
            if (!savedConsent) {
                this.showBanner = true;
            } else {
                this.consent = JSON.parse(savedConsent);
                this.applyConsent();
            }
            
            // Make showCookieBanner globally available
            window.showCookieBanner = () => {
                this.showPreferences = true;
            };
        },
        
        acceptAll() {
            this.consent = {
                essential: true,
                analytics: true,
                marketing: false
            };
            this.saveConsent();
        },
        
        acceptEssential() {
            this.consent = {
                essential: true,
                analytics: false,
                marketing: false
            };
            this.saveConsent();
        },
        
        savePreferences() {
            this.saveConsent();
            this.showPreferences = false;
        },
        
        saveConsent() {
            localStorage.setItem('cookie-consent', JSON.stringify(this.consent));
            localStorage.setItem('cookie-consent-date', new Date().toISOString());
            this.showBanner = false;
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
            // Enable Google Analytics if consent given
            if (typeof gtag !== 'undefined') {
                gtag('consent', 'update', {
                    'analytics_storage': 'granted'
                });
            }
        },
        
        disableAnalytics() {
            // Disable Google Analytics if consent not given
            if (typeof gtag !== 'undefined') {
                gtag('consent', 'update', {
                    'analytics_storage': 'denied'
                });
            }
        },
        
        enableMarketing() {
            // Enable marketing cookies (not used currently)
            console.log('Marketing cookies enabled');
        },
        
        disableMarketing() {
            // Disable marketing cookies (not used currently)
            console.log('Marketing cookies disabled');
        }
    }
}
</script>

<style>
[x-cloak] { 
    display: none !important; 
}
</style>
