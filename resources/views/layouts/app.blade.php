<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- Favicon --}}
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
        <link rel="icon" href="{{ asset('favicon.svg') }}" sizes="any">
        <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.svg') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.svg') }}">
        <link rel="manifest" href="{{ asset('site.webmanifest') }}">

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

        <!-- Fonts: system-ui stack to avoid external font providers -->

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- Additional Styles -->
        @stack('styles')
        
        <!-- Analytics -->
        @include('partials.analytics')
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <livewire:layout.navigation />

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
        
        <!-- Additional Scripts -->
        @stack('scripts')
    </body>
</html>
