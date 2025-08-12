<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', __('app.app_name')) }}</title>
        @php($supported = config('app.supported_locales', ['en','it']))
        @foreach($supported as $loc)
            <link rel="alternate" href="{{ url('/'.$loc) }}" hreflang="{{ $loc }}">
        @endforeach
        <link rel="alternate" href="{{ url('/') }}" hreflang="x-default">
        <meta name="description" content="{{ __('app.meta.description') }}">
        <meta property="og:title" content="{{ __('app.app_name') }}">
        <meta property="og:description" content="{{ __('app.meta.description') }}">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-surface-50 dark:bg-gray-900">
        <header class="py-4 md:py-6 bg-white/80 dark:bg-gray-900/80 backdrop-blur supports-[backdrop-filter]:bg-white/60 dark:supports-[backdrop-filter]:bg-gray-900/60 border-b border-gray-100 dark:border-gray-800 sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
                <x-site-logo />

                <div class="flex items-center gap-3">
                    <!-- Lang switcher -->
                    <nav aria-label="{{ __('app.nav.language') }}" class="hidden sm:flex items-center gap-1">
                        <a href="{{ url('/en') }}" class="px-2 py-1 text-xs rounded-md text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus-visible:ring-2 focus-visible:ring-brand-400">EN</a>
                        <a href="{{ url('/it') }}" class="px-2 py-1 text-xs rounded-md text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus-visible:ring-2 focus-visible:ring-brand-400">IT</a>
                    </nav>

                    <!-- Theme toggle -->
                    <button x-data="{ mode: localStorage.theme ?? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light') }"
                            @click="mode = (mode==='dark'?'light':'dark'); document.documentElement.classList.toggle('dark', mode==='dark'); localStorage.theme = mode"
                            :aria-pressed="mode==='dark'" aria-label="Toggle theme"
                            class="rounded-md p-2 text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800 focus-visible:ring-2 focus-visible:ring-brand-400">
                        <svg x-show="mode==='light'" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 18a6 6 0 1 0 0-12 6 6 0 0 0 0 12Z"/><path d="M12 2.25v1.5M12 20.25v1.5M3.182 3.182l1.06 1.06M19.758 19.758l1.06 1.06M.75 12h1.5M21.75 12h1.5M3.182 20.818l1.06-1.06M19.758 4.242l1.06-1.06"/></svg>
                        <svg x-show="mode==='dark'" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M21.752 15.002A9 9 0 1 1 8.998 2.248 7.5 7.5 0 1 0 21.752 15Z"/></svg>
                    </button>

                    <a href="{{ route('login') }}" class="hidden sm:inline text-sm text-gray-700 dark:text-gray-300 hover:underline">{{ __('auth.Log in') }}</a>
                    <a href="{{ route('register') }}" class="inline-flex items-center rounded-md bg-brand-600 px-3 md:px-4 py-2 text-white hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-400 dark:focus:ring-offset-gray-900">{{ __('auth.Register') }}</a>
                </div>
            </div>
            @auth
                <livewire:layout.navigation />
            @endauth
        </header>

        <main>
            {{ $slot }}
        </main>

        <footer class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-xs text-gray-500 dark:text-gray-400">
                &copy; {{ date('Y') }} {{ __('app.app_name') }}
            </div>
        </footer>
    </body>
    </html>


