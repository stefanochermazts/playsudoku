@extends('layouts.site')

@section('head')
<meta name="description" content="Scopri articoli e guide su Sudoku: tecniche di risoluzione, aggiornamenti PlaySudoku e strategie per migliorare le tue abilità.">
<meta name="keywords" content="sudoku, articoli, guide, tecniche, risoluzione, PlaySudoku, news">

<!-- Open Graph -->
<meta property="og:title" content="Articoli Sudoku - Guide e Tecniche | PlaySudoku">
<meta property="og:description" content="Scopri articoli e guide su Sudoku: tecniche di risoluzione, aggiornamenti PlaySudoku e strategie per migliorare le tue abilità.">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ request()->url() }}">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="Articoli Sudoku - Guide e Tecniche | PlaySudoku">
<meta name="twitter:description" content="Scopri articoli e guide su Sudoku: tecniche di risoluzione, aggiornamenti PlaySudoku e strategie per migliorare le tue abilità.">
@endsection

@section('content')
<div class="min-h-screen bg-gradient-to-br from-neutral-50 via-white to-neutral-100 dark:from-neutral-900 dark:via-neutral-800 dark:to-neutral-900">
    
    {{-- Hero Section --}}
    <div class="relative overflow-hidden bg-gradient-to-r from-primary-600 via-primary-700 to-secondary-600 text-white py-16">
        <div class="absolute inset-0 bg-black/10"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl sm:text-5xl font-bold mb-4">
                📚 {{ __('app.editorial.latest_articles') }}
            </h1>
            <p class="text-xl text-blue-100 max-w-3xl mx-auto">
                Scopri guide, tecniche e le ultime novità del mondo Sudoku per migliorare le tue abilità di risoluzione
            </p>
        </div>
        {{-- Decorative pattern --}}
        <div class="absolute top-0 left-0 w-full h-full opacity-10">
            <div class="grid grid-cols-9 gap-4 h-full p-8">
                @for($i = 0; $i < 81; $i++)
                    <div class="bg-white/20 rounded-sm"></div>
                @endfor
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            {{-- Main Content --}}
            <div class="lg:col-span-3">
                
                {{-- Search & Filters --}}
                <div class="bg-white/80 dark:bg-neutral-800/80 backdrop-blur-sm rounded-2xl shadow-lg border border-neutral-200/50 dark:border-neutral-700/50 p-6 mb-8">
                    <form method="GET" action="{{ route('articles.index', ['locale' => app()->getLocale()]) }}" class="flex flex-col sm:flex-row gap-4">
                        {{-- Search --}}
                        <div class="flex-1">
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="{{ __('app.editorial.search_placeholder') }}"
                                   class="w-full px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white placeholder-neutral-500 dark:placeholder-neutral-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        </div>
                        
                        {{-- Category Filter --}}
                        <div class="sm:w-48">
                            <select name="category" class="w-full px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option value="">{{ __('app.editorial.all_categories') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->slug }}" {{ request('category') == $category->slug ? 'selected' : '' }}>
                                        {{ $category->icon }} {{ $category->localized_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        {{-- Submit Button --}}
                        <button type="submit" 
                                class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg shadow-sm transition-colors whitespace-nowrap">
                            🔍 Cerca
                        </button>
                    </form>
                </div>

                {{-- Featured Articles --}}
                @if($articles->where('featured', true)->count() > 0 && !request()->hasAny(['search', 'category']))
                    <div class="mb-12">
                        <h2 class="text-2xl font-bold text-neutral-900 dark:text-white mb-6 flex items-center">
                            ⭐ {{ __('app.editorial.featured_articles') }}
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($articles->where('featured', true)->take(2) as $article)
                                @include('articles.partials.featured-card', ['article' => $article])
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Articles Grid --}}
                @if($articles->count() > 0)
                    <div class="mb-8">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-bold text-neutral-900 dark:text-white">
                                @if(request('search'))
                                    🔍 {{ __('app.editorial.search_results') }}
                                @elseif(request('category'))
                                    {{ $categories->where('slug', request('category'))->first()?->icon }} 
                                    {{ $categories->where('slug', request('category'))->first()?->localized_name }}
                                @else
                                    📖 Tutti gli Articoli
                                @endif
                            </h2>
                            <p class="text-neutral-600 dark:text-neutral-300">
                                {{ __('app.editorial.articles_found', ['count' => $articles->total()]) }}
                            </p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                            @foreach($articles->where('featured', false) as $article)
                                @include('articles.partials.article-card', ['article' => $article])
                            @endforeach
                        </div>
                    </div>

                    {{-- Pagination --}}
                    <div class="flex justify-center">
                        {{ $articles->withQueryString()->links() }}
                    </div>
                @else
                    {{-- No Articles --}}
                    <div class="text-center py-16">
                        <div class="w-32 h-32 mx-auto mb-6 bg-neutral-100 dark:bg-neutral-700 rounded-full flex items-center justify-center">
                            <svg class="w-16 h-16 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-neutral-900 dark:text-white mb-2">
                            {{ __('app.editorial.no_results') }}
                        </h3>
                        <p class="text-neutral-600 dark:text-neutral-300 mb-6">
                            @if(request('search'))
                                Nessun articolo trovato per "{{ request('search') }}"
                            @else
                                {{ __('app.editorial.no_articles') }}
                            @endif
                        </p>
                        @if(request()->hasAny(['search', 'category']))
                            <a href="{{ route('articles.index', ['locale' => app()->getLocale()]) }}" 
                               class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg shadow-sm transition-colors">
                                📚 Vedi tutti gli articoli
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                
                {{-- Categories --}}
                <div class="bg-white/80 dark:bg-neutral-800/80 backdrop-blur-sm rounded-2xl shadow-lg border border-neutral-200/50 dark:border-neutral-700/50 p-6">
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white mb-4 flex items-center">
                        📂 Categorie
                    </h3>
                    <div class="space-y-2">
                        <a href="{{ route('articles.index', ['locale' => app()->getLocale()]) }}" 
                           class="flex items-center px-3 py-2 rounded-lg text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors {{ !request('category') ? 'bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300' : '' }}">
                            📚 Tutti gli articoli
                        </a>
                        @foreach($categories as $category)
                            <a href="{{ route('articles.category', ['locale' => app()->getLocale(), 'category' => $category->slug]) }}" 
                               class="flex items-center justify-between px-3 py-2 rounded-lg text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors {{ request('category') == $category->slug ? 'bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300' : '' }}">
                                <span class="flex items-center space-x-2">
                                    <span>{{ $category->icon }}</span>
                                    <span>{{ $category->localized_name }}</span>
                                </span>
                                <span class="text-xs bg-neutral-200 dark:bg-neutral-600 px-2 py-1 rounded-full">
                                    {{ $category->published_count ?? 0 }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Featured Articles Sidebar --}}
                @if($featuredArticles->count() > 0)
                    <div class="bg-white/80 dark:bg-neutral-800/80 backdrop-blur-sm rounded-2xl shadow-lg border border-neutral-200/50 dark:border-neutral-700/50 p-6">
                        <h3 class="text-lg font-bold text-neutral-900 dark:text-white mb-4 flex items-center">
                            ⭐ {{ __('app.editorial.featured_articles') }}
                        </h3>
                        <div class="space-y-4">
                            @foreach($featuredArticles->take(3) as $article)
                                @include('articles.partials.sidebar-card', ['article' => $article])
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
