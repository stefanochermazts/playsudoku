<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $seoTitle ?? config('app.name', __('app.app_name')) }}</title>
    <meta name="description" content="{{ $seoDescription ?? __('app.meta.description') }}">
    <meta name="keywords" content="{{ __('app.meta.keywords') }}">
    <meta name="author" content="PlaySudoku">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">
    
    <!-- Open Graph -->
    <meta property="og:title" content="{{ $seoTitle ?? __('app.app_name') }}">
    <meta property="og:description" content="{{ $seoDescription ?? __('app.meta.description') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoTitle ?? __('app.app_name') }}">
    <meta name="twitter:description" content="{{ $seoDescription ?? __('app.meta.description') }}">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <script>
        // Theme initialization
        (function() {
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
</head>
<body class="font-sans antialiased bg-gradient-to-br from-primary-50 via-neutral-50 to-secondary-50 dark:from-neutral-900 dark:via-neutral-950 dark:to-neutral-900 min-h-screen">
    <!-- Floating background decorations -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-0 -left-4 w-72 h-72 bg-primary-200/30 dark:bg-primary-900/20 rounded-full mix-blend-multiply dark:mix-blend-lighten filter blur-xl animate-pulse"></div>
        <div class="absolute top-0 -right-4 w-72 h-72 bg-secondary-200/30 dark:bg-secondary-900/20 rounded-full mix-blend-multiply dark:mix-blend-lighten filter blur-xl animate-pulse" style="animation-delay: 2s;"></div>
        <div class="absolute -bottom-8 left-20 w-72 h-72 bg-accent-200/30 dark:bg-accent-900/20 rounded-full mix-blend-multiply dark:mix-blend-lighten filter blur-xl animate-pulse" style="animation-delay: 4s;"></div>
    </div>
    <header class="relative z-50 bg-white/95 dark:bg-neutral-900/95 backdrop-blur-md border-b border-neutral-200/80 dark:border-neutral-700/80 sticky top-0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <x-site-logo />
                </div>

                <!-- Desktop Navigation -->
                <nav class="hidden md:flex items-center space-x-8">
                    @guest
                        <a href="{{ url('/' . app()->getLocale()) }}#features" class="text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors font-medium">{{ __('app.nav.features') }}</a>
                        <a href="{{ url('/' . app()->getLocale()) }}#how-it-works" class="text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors font-medium">{{ __('app.nav.how_it_works') }}</a>
                    @endguest
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors font-medium">{{ __('app.nav.dashboard') }}</a>
                    @endauth
                </nav>

                <!-- Right side controls -->
                <div class="flex items-center space-x-4">
                    <!-- Language switcher -->
                    <div class="flex items-center space-x-1 bg-neutral-100 dark:bg-neutral-800 rounded-lg p-1">
                        <a href="{{ url('/en') }}" class="px-3 py-1 text-sm font-medium rounded-md {{ app()->getLocale() === 'en' ? 'bg-white dark:bg-neutral-700 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400' }} transition-all">EN</a>
                        <a href="{{ url('/it') }}" class="px-3 py-1 text-sm font-medium rounded-md {{ app()->getLocale() === 'it' ? 'bg-white dark:bg-neutral-700 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400' }} transition-all">IT</a>
                    </div>

                    <!-- Theme toggle -->
                    <button onclick="toggleTheme()" 
                            aria-label="Toggle theme"
                            class="p-2 rounded-lg bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors">
                        <svg id="theme-icon-light" class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path>
                        </svg>
                        <svg id="theme-icon-dark" class="h-5 w-5 hidden" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                    </button>

                    <!-- Auth buttons -->
                    @guest
                        <div class="flex items-center space-x-3">
                            <a href="{{ route('localized.login', ['locale' => app()->getLocale()]) }}" class="text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400 font-medium transition-colors">{{ __('auth.Log in') }}</a>
                            <a href="{{ route('localized.register', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-primary-600 to-secondary-600 text-white font-medium rounded-lg hover:from-primary-700 hover:to-secondary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900 transition-all transform hover:scale-105">
                                {{ __('auth.Register') }}
                            </a>
                        </div>
                    @endguest
                    @auth
                        <!-- User dropdown menu -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" 
                                    class="flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-neutral-600 dark:text-neutral-300 bg-white dark:bg-neutral-800 hover:text-neutral-700 dark:hover:text-neutral-300 focus:outline-none transition ease-in-out duration-150">
                                <div class="me-1">{{ auth()->user()->name }}</div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>

                            <div x-show="open" 
                                 @click.outside="open = false"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-48 bg-white dark:bg-neutral-800 rounded-md overflow-hidden shadow-lg z-50 border border-neutral-200 dark:border-neutral-700">
                                
                                @if(auth()->user() && auth()->user()->isAdmin())
                                    <a href="{{ route('admin.dashboard') }}" 
                                       class="block px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors">
                                        👑 Dashboard Admin
                                    </a>
                                @endif
                                
                                <a href="{{ route('dashboard') }}" 
                                   class="block px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors">
                                    📊 Dashboard
                                </a>
                                
                                <a href="{{ route('profile') }}" 
                                   class="block px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors">
                                    👤 Profilo
                                </a>
                                
                                <hr class="border-neutral-200 dark:border-neutral-700">
                                
                                <button onclick="logout()" 
                                        class="w-full text-left px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors">
                                    🚪 Logout
                                </button>
                            </div>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </header>

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
                        <li><a href="#" class="hover:text-primary-400 transition-colors">{{ __('app.footer.daily_challenges') }}</a></li>
                        <li><a href="#" class="hover:text-primary-400 transition-colors">{{ __('app.footer.leaderboards') }}</a></li>
                        <li><a href="#" class="hover:text-primary-400 transition-colors">{{ __('app.footer.solver') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">{{ __('app.footer.support') }}</h4>
                    <ul class="space-y-2 text-neutral-300">
                        <li><a href="#" class="hover:text-primary-400 transition-colors">{{ __('app.footer.help') }}</a></li>
                        <li><a href="#" class="hover:text-primary-400 transition-colors">{{ __('app.footer.contact') }}</a></li>
                        <li><a href="#" class="hover:text-primary-400 transition-colors">{{ __('app.footer.privacy') }}</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-neutral-800 mt-8 pt-8 text-center text-neutral-400">
                <p>© {{ date('Y') }} {{ __('app.app_name') }}. {{ __('app.footer.rights') }}</p>
            </div>
        </div>
    </footer>
    
            <script>
            function toggleTheme() {
                const isDark = document.documentElement.classList.contains('dark');
                const lightIcon = document.getElementById('theme-icon-light');
                const darkIcon = document.getElementById('theme-icon-dark');
                
                if (isDark) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                    lightIcon.classList.remove('hidden');
                    darkIcon.classList.add('hidden');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                    lightIcon.classList.add('hidden');
                    darkIcon.classList.remove('hidden');
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
</body>
</html>