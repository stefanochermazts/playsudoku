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

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
            <div>
                <a href="/" wire:navigate>
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
