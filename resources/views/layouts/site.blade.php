<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Favicon --}}
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('favicon.svg') }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.svg') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.svg') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    {{-- Resource Hints per Performance --}}
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//www.google-analytics.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    {{-- Preload Critical Resources --}}
    {{-- @vite(['resources/css/app.css'], 'preload') --}}
    
    {{-- SEO Meta Tags via MetaService --}}
    @php
        // Get MetaService singleton (may already be configured by controller)
        $metaService = app(App\Services\MetaService::class);
        
        // Handle backward compatibility with existing $seoTitle and $seoDescription
        // Only override if specific SEO data is provided AND MetaService hasn't been configured yet
        if ((isset($seoTitle) || isset($seoDescription)) && 
            !$metaService->isConfigured()) {
            $metaService->setPage(
                $seoTitle ?? __('app.app_name'),
                $seoDescription ?? __('app.meta.description'),
                ['url' => url()->current()]
            );
        }
        
        // PerformanceService disabled for now to simplify CSS loading
        // $performanceService = app(App\Services\PerformanceService::class);
    @endphp
    
    {{-- @include('partials.meta-tags') --}}
    
    {{-- Load CSS normally --}}
    @vite(['resources/css/app.css'])
    
    @vite(['resources/js/app.js'])
    @stack('styles')
    
    <!-- Analytics -->
    @include('partials.analytics')

    <script>
        // Espone lo stato di debug di Laravel a JS per silenziare i log in produzione
        window.APP_DEBUG = {{ config('app.debug') ? 'true' : 'false' }};
    </script>
    
    <style>
        /* Alpine.js cloak per nascondere elementi durante inizializzazione */
        [x-cloak] { 
            display: none !important; 
        }
    </style>
    
    {{-- Theme initialization - separate scripts for auth/guest to avoid Blade parsing issues --}}
    @auth
    <script>
        // Theme initialization with database sync for authenticated users
        (function() {
            // Per utenti autenticati, prova a caricare le preferenze dal database
            const savedLocalTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            let theme = savedLocalTheme || (prefersDark ? 'dark' : 'light');
            
            // Applica tema temporaneo mentre carichi le preferenze
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
            
            // Carica preferenze dal database in background
            const preferencesUrl = "{{ (request()->route() && str_starts_with(request()->route()->getName() ?? '', 'localized.'))
                ? route('localized.api.preferences.get', ['locale' => app()->getLocale()])
                : route('api.preferences.get') }}";
            
            fetch(preferencesUrl, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(preferences => {
                if (preferences.theme && preferences.theme !== 'auto') {
                    // Sincronizza con preferenze database se diverse da localStorage
                    if (preferences.theme !== savedLocalTheme) {
                        localStorage.setItem('theme', preferences.theme);
                        if (preferences.theme === 'dark') {
                            document.documentElement.classList.add('dark');
                        } else {
                            document.documentElement.classList.remove('dark');
                        }
                        window.currentTheme = preferences.theme;
                        
                        // Aggiorna icone se necessario
                        setTimeout(() => {
                            const lightIcon = document.getElementById('theme-icon-light');
                            const darkIcon = document.getElementById('theme-icon-dark');
                            if (lightIcon && darkIcon) {
                                if (preferences.theme === 'dark') {
                                    lightIcon.classList.add('hidden');
                                    darkIcon.classList.remove('hidden');
                                } else {
                                    lightIcon.classList.remove('hidden');
                                    darkIcon.classList.add('hidden');
                                }
                            }
                        }, 100);
                    }
                }
            })
            .catch(error => {
                console.warn('Impossibile caricare preferenze dal database:', error);
                // Continua con le preferenze locali
            });
            
            window.currentTheme = theme;
        })();
    </script>
    @endauth

    @guest
    <script>
        // Theme initialization for guest users
        (function() {
            // Per utenti non autenticati, usa solo localStorage e preferenze sistema
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const theme = savedTheme || (prefersDark ? 'dark' : 'light');
            
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
            
            window.currentTheme = theme;
        })();
    </script>
    @endguest
</head>
<body class="font-sans antialiased bg-gradient-to-br from-primary-50 via-neutral-50 to-secondary-50 dark:from-neutral-900 dark:via-neutral-950 dark:to-neutral-900 min-h-screen">
    @if(session()->has('impersonator_id'))
        <div class="bg-warning-100 dark:bg-yellow-900/40 text-warning-900 dark:text-yellow-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 flex items-center justify-between">
                <div class="text-sm font-medium">🔄 Impersonazione attiva</div>
                <form action="{{ route('impersonation.stop') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-3 py-1 bg-warning-600 hover:bg-warning-700 text-white rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-warning-500" aria-label="Termina impersonazione e torna al mio account">
                        {{ __('app.aria.return_to_account') }}
                    </button>
                </form>
            </div>
        </div>
    @endif
    <!-- Floating background decorations -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-0 -left-4 w-72 h-72 bg-primary-200/30 dark:bg-primary-900/20 rounded-full mix-blend-multiply dark:mix-blend-lighten filter blur-xl animate-pulse"></div>
        <div class="absolute top-0 -right-4 w-72 h-72 bg-secondary-200/30 dark:bg-secondary-900/20 rounded-full mix-blend-multiply dark:mix-blend-lighten filter blur-xl animate-pulse" style="animation-delay: 2s;"></div>
        <div class="absolute -bottom-8 left-20 w-72 h-72 bg-accent-200/30 dark:bg-accent-900/20 rounded-full mix-blend-multiply dark:mix-blend-lighten filter blur-xl animate-pulse" style="animation-delay: 4s;"></div>
    </div>
    <header class="relative z-50 bg-white/95 dark:bg-neutral-900/95 backdrop-blur-md border-b border-neutral-200/80 dark:border-neutral-700/80" 
            x-data="{ mobileMenuOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <x-site-logo />
                </div>

                <!-- Desktop Navigation - Solo per guest -->
                @guest
                <nav class="hidden md:flex items-center space-x-4">
                    <a href="{{ route('localized.sudoku.training', ['locale' => app()->getLocale()]) }}" class="text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors font-medium whitespace-nowrap">{{ __('app.nav.training') }}</a>
                    <a href="{{ route('localized.sudoku.analyzer', ['locale' => app()->getLocale()]) }}" class="text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors font-medium whitespace-nowrap">{{ __('app.nav.analyzer') }}</a>
                    
                    {{-- Editorial Categories --}}
                    <div class="relative group">
                        <a href="{{ route('localized.articles.index', ['locale' => app()->getLocale()]) }}" class="text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors font-medium whitespace-nowrap flex items-center">
                            📚 {{ __('app.nav.articles') }}
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </a>
                        
                        <!-- Dropdown Menu -->
                        <div class="absolute left-0 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-neutral-800 ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                            <div class="py-1">
                                <a href="{{ route('localized.articles.category', ['locale' => app()->getLocale(), 'category' => 'news']) }}" 
                                   class="flex items-center px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                    📰 {{ __('app.nav.news') }}
                                </a>
                                <a href="{{ route('localized.articles.category', ['locale' => app()->getLocale(), 'category' => 'techniques']) }}" 
                                   class="flex items-center px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                    🧩 {{ __('app.nav.techniques') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </nav>
                @endguest

                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" 
                            class="inline-flex items-center justify-center p-2 rounded-md text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500 transition-colors"
                            aria-expanded="false"
                            :aria-expanded="mobileMenuOpen"
                            aria-label="{{ __('app.aria.open_main_menu') }}">
                        <!-- Hamburger icon when menu is closed -->
                        <svg :class="{'hidden': mobileMenuOpen, 'block': !mobileMenuOpen}" class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <!-- X icon when menu is open -->
                        <svg :class="{'block': mobileMenuOpen, 'hidden': !mobileMenuOpen}" class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Right side controls -->
                <div class="hidden md:flex items-center space-x-4">
                    @guest
                        <!-- Language switcher per guest -->
                        <div class="flex items-center space-x-1 bg-neutral-100 dark:bg-neutral-800 rounded-lg p-1">
                            @php($currentPath = request()->path())
                            @php($startsWithLocale = preg_match('/^(en|it|de|es)(\/?|$)/', $currentPath) === 1)
                            @php($pathEn = $startsWithLocale ? preg_replace('/^(en|it|de|es)(?=\/|$)/', 'en', $currentPath) : 'en')
                            @php($pathIt = $startsWithLocale ? preg_replace('/^(en|it|de|es)(?=\/|$)/', 'it', $currentPath) : 'it')
                            @php($pathDe = $startsWithLocale ? preg_replace('/^(en|it|de|es)(?=\/|$)/', 'de', $currentPath) : 'de')
                            @php($pathEs = $startsWithLocale ? preg_replace('/^(en|it|de|es)(?=\/|$)/', 'es', $currentPath) : 'es')
                            <a href="{{ url($pathEn) }}" class="px-2 py-1 text-xs font-medium rounded-md {{ app()->getLocale() === 'en' ? 'bg-white dark:bg-neutral-700 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400' }} transition-all">EN</a>
                            <a href="{{ url($pathIt) }}" class="px-2 py-1 text-xs font-medium rounded-md {{ app()->getLocale() === 'it' ? 'bg-white dark:bg-neutral-700 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400' }} transition-all">IT</a>
                            <a href="{{ url($pathDe) }}" class="px-2 py-1 text-xs font-medium rounded-md {{ app()->getLocale() === 'de' ? 'bg-white dark:bg-neutral-700 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400' }} transition-all">DE</a>
                            <a href="{{ url($pathEs) }}" class="px-2 py-1 text-xs font-medium rounded-md {{ app()->getLocale() === 'es' ? 'bg-white dark:bg-neutral-700 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400' }} transition-all">ES</a>
                        </div>

                        <!-- Theme toggle per guest -->
                        <button onclick="toggleTheme()" 
                                aria-label="{{ __('app.aria.toggle_theme') }}"
                                class="p-2 rounded-lg bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors">
                            <svg id="theme-icon-light" class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path>
                            </svg>
                            <svg id="theme-icon-dark" class="h-5 w-5 hidden" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                            </svg>
                        </button>

                        <!-- Auth buttons -->
                        <div class="flex items-center space-x-3">
                            <a href="{{ route('localized.login', ['locale' => app()->getLocale()]) }}" class="text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400 font-medium transition-colors">{{ __('app.nav.login') }}</a>
                            <a href="{{ route('localized.register', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-primary-600 to-secondary-600 text-white font-medium rounded-lg hover:from-primary-700 hover:to-secondary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900 transition-all transform hover:scale-105">
                                {{ __('app.nav.register') }}
                            </a>
                        </div>
                    @endguest
                    
                    @auth
                        <!-- Hamburger Menu (tutto incluso nell'hamburger) -->
                        <x-hamburger-menu />
                    @endauth
                </div>
            </div>
        </div>

        <!-- Mobile Navigation Menu -->
        <div :class="{'block': mobileMenuOpen, 'hidden': !mobileMenuOpen}" 
             class="hidden md:hidden bg-white/95 dark:bg-neutral-900/95 backdrop-blur-md border-b border-neutral-200/80 dark:border-neutral-700/80"
             x-show="mobileMenuOpen"
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="transform opacity-0 scale-95"
             x-transition:enter-end="transform opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="transform opacity-100 scale-100"
             x-transition:leave-end="transform opacity-0 scale-95"
             @click.away="mobileMenuOpen = false">
            <div class="px-4 pt-2 pb-3 space-y-1">
                @auth
                    <a href="{{ route('localized.dashboard', ['locale' => app()->getLocale()]) }}" 
                       class="block px-3 py-2 rounded-md text-base font-medium text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
                       @click="mobileMenuOpen = false">{{ __('app.nav.dashboard') }}</a>
                    <a href="{{ route('localized.challenges.index', ['locale' => app()->getLocale()]) }}" 
                       class="block px-3 py-2 rounded-md text-base font-medium text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
                       @click="mobileMenuOpen = false">{{ __('app.nav.challenges') }}</a>
                    <a href="{{ route('localized.friends.index', ['locale' => app()->getLocale()]) }}" 
                       class="block px-3 py-2 rounded-md text-base font-medium text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
                       @click="mobileMenuOpen = false">{{ __('app.nav.friends') }}</a>
                    <a href="{{ route('localized.daily-board.index', ['locale' => app()->getLocale()]) }}" 
                       class="block px-3 py-2 rounded-md text-base font-medium text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
                       @click="mobileMenuOpen = false">{{ __('app.daily_board') }}</a>
                    <a href="{{ route('localized.weekly-board.index', ['locale' => app()->getLocale()]) }}" 
                       class="block px-3 py-2 rounded-md text-base font-medium text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
                       @click="mobileMenuOpen = false">{{ __('app.weekly_board') }}</a>
                @endauth
                
                {{-- Articoli - visibili anche per utenti autenticati --}}
                @auth
                <div class="border-t border-neutral-200 dark:border-neutral-700 pt-4 mt-4">
                    <h3 class="px-3 text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">{{ __('app.nav.articles') }}</h3>
                    <div class="mt-2 space-y-1">
                        <a href="{{ route('localized.articles.index', ['locale' => app()->getLocale()]) }}" 
                           class="flex items-center px-3 py-2 rounded-md text-base font-medium text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
                           @click="mobileMenuOpen = false">
                            📚 {{ __('app.nav.articles') }}
                        </a>
                        <a href="{{ route('localized.articles.category', ['locale' => app()->getLocale(), 'category' => 'news']) }}" 
                           class="flex items-center px-6 py-2 rounded-md text-sm font-medium text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
                           @click="mobileMenuOpen = false">
                            📰 {{ __('app.nav.news') }}
                        </a>
                        <a href="{{ route('localized.articles.category', ['locale' => app()->getLocale(), 'category' => 'techniques']) }}" 
                           class="flex items-center px-6 py-2 rounded-md text-sm font-medium text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
                           @click="mobileMenuOpen = false">
                            🧩 {{ __('app.nav.techniques') }}
                        </a>
                    </div>
                </div>
                @endauth
                
                {{-- Training e Analyzer - visibili sempre --}}
                <a href="{{ route('localized.sudoku.training', ['locale' => app()->getLocale()]) }}" 
                   class="block px-3 py-2 rounded-md text-base font-medium text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
                   @click="mobileMenuOpen = false">{{ __('app.nav.training') }}</a>
                <a href="{{ route('localized.sudoku.analyzer', ['locale' => app()->getLocale()]) }}" 
                   class="block px-3 py-2 rounded-md text-base font-medium text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
                   @click="mobileMenuOpen = false">{{ __('app.nav.analyzer') }}</a>

                {{-- Editorial Categories --}}
                <div class="border-t border-neutral-200 dark:border-neutral-700 pt-4 mt-4">
                    <h3 class="px-3 text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">{{ __('app.nav.articles') }}</h3>
                    <div class="mt-2 space-y-1">
                        <a href="{{ route('localized.articles.index', ['locale' => app()->getLocale()]) }}" 
                           class="flex items-center px-3 py-2 rounded-md text-base font-medium text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
                           @click="mobileMenuOpen = false">
                            📚 {{ __('app.nav.articles') }}
                        </a>
                        <a href="{{ route('localized.articles.category', ['locale' => app()->getLocale(), 'category' => 'news']) }}" 
                           class="flex items-center px-6 py-2 rounded-md text-sm font-medium text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
                           @click="mobileMenuOpen = false">
                            📰 {{ __('app.nav.news') }}
                        </a>
                        <a href="{{ route('localized.articles.category', ['locale' => app()->getLocale(), 'category' => 'techniques']) }}" 
                           class="flex items-center px-6 py-2 rounded-md text-sm font-medium text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
                           @click="mobileMenuOpen = false">
                            🧩 {{ __('app.nav.techniques') }}
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Mobile controls section -->
            <div class="pt-4 pb-3 border-t border-neutral-200/80 dark:border-neutral-700/80">
                <div class="px-4 space-y-3">
                    <!-- Language switcher - mobile -->
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-neutral-600 dark:text-neutral-300">{{ __('app.nav.language') }}</span>
                        <div class="flex items-center space-x-1 bg-neutral-100 dark:bg-neutral-800 rounded-lg p-1">
                            <a href="{{ url('/en') }}" class="px-2 py-1 text-xs font-medium rounded-md {{ app()->getLocale() === 'en' ? 'bg-white dark:bg-neutral-700 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400' }} transition-all">EN</a>
                            <a href="{{ url('/it') }}" class="px-2 py-1 text-xs font-medium rounded-md {{ app()->getLocale() === 'it' ? 'bg-white dark:bg-neutral-700 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400' }} transition-all">IT</a>
                            <a href="{{ url('/de') }}" class="px-2 py-1 text-xs font-medium rounded-md {{ app()->getLocale() === 'de' ? 'bg-white dark:bg-neutral-700 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400' }} transition-all">DE</a>
                            <a href="{{ url('/es') }}" class="px-2 py-1 text-xs font-medium rounded-md {{ app()->getLocale() === 'es' ? 'bg-white dark:bg-neutral-700 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400' }} transition-all">ES</a>
                        </div>
                    </div>
                    
                    <!-- Theme toggle - mobile -->
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-neutral-600 dark:text-neutral-300">{{ __('app.nav.theme') }}</span>
                        <button onclick="toggleTheme()" 
                                aria-label="{{ __('app.aria.toggle_theme') }}"
                                class="p-2 rounded-lg bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors">
                            <svg id="theme-icon-light-mobile" class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path>
                            </svg>
                            <svg id="theme-icon-dark-mobile" class="h-5 w-5 hidden" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                            </svg>
                        </button>
                    </div>
                    
                    @guest
                        <!-- Auth buttons - mobile -->
                        <div class="space-y-2 pt-2">
                            <a href="{{ route('localized.login', ['locale' => app()->getLocale()]) }}" 
                               class="block w-full text-center px-4 py-2 border border-primary-600 text-primary-600 dark:text-primary-400 font-medium rounded-lg hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors"
                               @click="mobileMenuOpen = false">{{ __('app.nav.login') }}</a>
                            <a href="{{ route('localized.register', ['locale' => app()->getLocale()]) }}" 
                               class="block w-full text-center px-4 py-2 bg-gradient-to-r from-primary-600 to-secondary-600 text-white font-medium rounded-lg hover:from-primary-700 hover:to-secondary-700 transition-all"
                               @click="mobileMenuOpen = false">{{ __('app.nav.register') }}</a>
                        </div>
                    @endguest
                    
                    @auth
                        <!-- User menu - mobile -->
                        <div class="pt-2 border-t border-neutral-200/80 dark:border-neutral-700/80">
                            <div class="flex items-center px-3 py-2">
                                <div class="flex-shrink-0">
                                    <div class="h-8 w-8 rounded-full bg-gradient-to-r from-primary-500 to-secondary-500 flex items-center justify-center text-white font-medium text-sm">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-neutral-900 dark:text-white">{{ auth()->user()->name }}</div>
                                    <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ auth()->user()->email }}</div>
                                </div>
                            </div>
                            <div class="mt-2 space-y-1">
                                @if(auth()->user() && auth()->user()->isAdmin())
                                    <a href="{{ route('admin.dashboard') }}" 
                                       class="block px-3 py-2 rounded-md text-base font-medium text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
                                       @click="mobileMenuOpen = false">👑 Dashboard Admin</a>
                                @endif
                                <a href="{{ route('localized.profile', ['locale' => app()->getLocale()]) }}" 
                                   class="block px-3 py-2 rounded-md text-base font-medium text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
                                   @click="mobileMenuOpen = false">👤 {{ __('app.nav.profile') }}</a>
                                <button onclick="logout(); mobileMenuOpen = false;" 
                                        class="block w-full text-left px-3 py-2 rounded-md text-base font-medium text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">
                                    🚪 {{ __('app.nav.logout') }}
                                </button>
                            </div>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    {{-- Breadcrumb Navigation --}}
    <div class="relative z-10 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <x-breadcrumbs class="min-h-[24px]" />
        </div>
    </div>

    <main class="relative z-10">
        {{ $slot }}
    </main>

    <footer class="relative z-10 bg-neutral-900 dark:bg-neutral-950 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid md:grid-cols-4 gap-8">
                <div class="md:col-span-2">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-8 h-8 bg-gradient-to-r from-primary-500 to-secondary-500 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-sm">S</span>
                        </div>
                        <span class="text-xl font-bold">{{ __('app.app_name') }}</span>
                    </div>
                    <p class="text-neutral-300 max-w-md">{{ __('app.footer.description') }}</p>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">{{ __('app.footer.game') }}</h4>
                    <ul class="space-y-2 text-neutral-300">
                        <li><a href="{{ route('localized.sudoku.training', ['locale' => app()->getLocale()]) }}" class="hover:text-primary-400 transition-colors">{{ __('app.footer.training') }}</a></li>
                        <li><a href="{{ route('localized.sudoku.analyzer', ['locale' => app()->getLocale()]) }}" class="hover:text-primary-400 transition-colors">{{ __('app.footer.analyzer') }}</a></li>
                        <li><a href="{{ route('localized.daily-board.index', ['locale' => app()->getLocale()]) }}" class="hover:text-primary-400 transition-colors">{{ __('app.footer.daily_challenges') }}</a></li>
                        <li><a href="{{ route('localized.weekly-board.index', ['locale' => app()->getLocale()]) }}" class="hover:text-primary-400 transition-colors">{{ __('app.footer.leaderboards') }}</a></li>
                        <li><a href="{{ route('localized.sudoku.analyzer', ['locale' => app()->getLocale()]) }}" class="hover:text-primary-400 transition-colors">{{ __('app.footer.solver') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">{{ __('app.footer.support') }}</h4>
                    <ul class="space-y-2 text-neutral-300">
                        <li><a href="{{ route('localized.help', ['locale' => app()->getLocale()]) }}" class="hover:text-primary-400 transition-colors">{{ __('app.footer.help') }}</a></li>
                        <li><a href="{{ route('localized.contact', ['locale' => app()->getLocale()]) }}" class="hover:text-primary-400 transition-colors">{{ __('app.footer.contact') }}</a></li>
                        <li><a href="{{ route('localized.privacy', ['locale' => app()->getLocale()]) }}" class="hover:text-primary-400 transition-colors">{{ __('app.footer.privacy') }}</a></li>
                        <li><a href="{{ route('localized.cookie-policy', ['locale' => app()->getLocale()]) }}" class="hover:text-primary-400 transition-colors">Cookie Policy</a></li>
                        <li><button onclick="window.showCookieBanner()" class="text-left hover:text-primary-400 transition-colors">{{ __('app.footer.cookie_preferences') }}</button></li>
                        <li><a href="{{ route('localized.terms', ['locale' => app()->getLocale()]) }}" class="hover:text-primary-400 transition-colors">{{ __('app.footer.terms') }}</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-neutral-800 mt-8 pt-8 text-center text-neutral-400">
                <p>© {{ date('Y') }} {{ __('app.app_name') }}. {{ __('app.footer.rights') }}</p>
            </div>
        </div>
    </footer>

    {{-- Floating Cookie Preferences Button --}}
    <div x-data="{ showFloatingButton: false }" 
         x-init="
            // Show floating button only if user has already given consent
            setTimeout(() => {
                const hasConsent = localStorage.getItem('cookie-consent');
                if (hasConsent) {
                    showFloatingButton = true;
                }
            }, 2000); // Wait 2 seconds after page load
         "
         x-show="showFloatingButton"
         x-cloak
         class="fixed bottom-6 right-6 z-50">
        <button 
            onclick="window.showCookieBanner()"
            class="bg-neutral-800 hover:bg-neutral-700 dark:bg-neutral-700 dark:hover:bg-neutral-600 text-white p-3 rounded-full shadow-lg transition-all duration-200 hover:scale-105 group"
            title="{{ __('app.cookie_preferences.floating_button_title') }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            {{-- Tooltip on hover --}}
            <div class="absolute bottom-full right-0 mb-2 px-2 py-1 bg-black text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap">
                {{ __('app.cookie_preferences.floating_button_tooltip') }}
            </div>
        </button>
    </div>
    
            <script>
            // Theme configuration (set outside function to avoid Blade parsing issues)
            @auth
            window.themeUrl = "{{ (request()->route() && str_starts_with(request()->route()->getName() ?? '', 'localized.'))
                ? route('localized.api.preferences.theme', ['locale' => app()->getLocale()])
                : route('api.preferences.theme') }}";
            window.isAuthenticated = true;
            @endauth
            
            @guest
            window.isAuthenticated = false;
            @endguest
            
            async function toggleTheme() {
                const isDark = document.documentElement.classList.contains('dark');
                const lightIcon = document.getElementById('theme-icon-light');
                const darkIcon = document.getElementById('theme-icon-dark');
                const newTheme = isDark ? 'light' : 'dark';
                
                // Applica il tema immediatamente nell'UI
                if (isDark) {
                    document.documentElement.classList.remove('dark');
                    if (lightIcon) lightIcon.classList.remove('hidden');
                    if (darkIcon) darkIcon.classList.add('hidden');
                } else {
                    document.documentElement.classList.add('dark');
                    if (lightIcon) lightIcon.classList.add('hidden');
                    if (darkIcon) darkIcon.classList.remove('hidden');
                }
                
                // Aggiorna la variabile globale
                window.currentTheme = newTheme;
                
                // Salva sempre nel localStorage
                localStorage.setItem('theme', newTheme);
                
                // Se utente autenticato, salva anche nel database
                if (window.isAuthenticated && window.themeUrl) {
                    try {
                        const response = await fetch(window.themeUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ theme: newTheme })
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            console.log('Tema salvato:', data.message);
                        } else {
                            console.warn('Errore risposta server per salvataggio tema');
                        }
                    } catch (error) {
                        console.warn('Errore salvataggio tema nel database:', error);
                        // Il tema rimane comunque applicato localmente
                    }
                }
            }
            
            // Logout function
            function logout() {
                // Get CSRF token
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                // Create form and submit
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("logout") }}';
                
                const tokenInput = document.createElement('input');
                tokenInput.type = 'hidden';
                tokenInput.name = '_token';
                tokenInput.value = token;
                
                form.appendChild(tokenInput);
                document.body.appendChild(form);
                form.submit();
            }
            
            // Initialize icon state
            document.addEventListener('DOMContentLoaded', function() {
                const isDark = document.documentElement.classList.contains('dark');
                const lightIcon = document.getElementById('theme-icon-light');
                const darkIcon = document.getElementById('theme-icon-dark');
                
                if (isDark) {
                    lightIcon.classList.add('hidden');
                    darkIcon.classList.remove('hidden');
                } else {
                    lightIcon.classList.remove('hidden');
                    darkIcon.classList.add('hidden');
                }
            });
        </script>
        
        <!-- Debug Alpine.js -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (window.APP_DEBUG) {
                    console.log('DOM caricato');
                    setTimeout(() => {
                        if (window.Alpine) {
                            console.log('Alpine.js è disponibile');
                        } else {
                            console.error('Alpine.js NON è disponibile');
                        }
                    }, 1000);
                }
            });
        </script>
    @stack('scripts')
    
    {{-- Cookie Banner --}}
    <x-cookie-banner />
</body>
</html>