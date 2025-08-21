<x-site-layout 
    seo-title="{{ $category->localized_name }} - Articoli | PlaySudoku"
    seo-description="{{ $category->localized_description ?? 'Scopri tutti gli articoli nella categoria ' . $category->localized_name . ' su PlaySudoku.' }}"
    seo-keywords="sudoku, {{ strtolower($category->localized_name) }}, articoli, guide, PlaySudoku">
    
    <x-slot name="head">
        <!-- Open Graph -->
        <meta property="og:title" content="{{ $category->localized_name }} - Articoli | PlaySudoku">
        <meta property="og:description" content="{{ $category->localized_description ?? 'Scopri tutti gli articoli nella categoria ' . $category->localized_name . ' su PlaySudoku.' }}">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ request()->url() }}">

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary">
        <meta name="twitter:title" content="{{ $category->localized_name }} - Articoli | PlaySudoku">
        <meta name="twitter:description" content="{{ $category->localized_description ?? 'Scopri tutti gli articoli nella categoria ' . $category->localized_name . ' su PlaySudoku.' }}">

        <!-- Canonical URL -->
        <link rel="canonical" href="{{ route('localized.articles.category', ['locale' => app()->getLocale(), 'category' => $category->slug]) }}">
    </x-slot>

<!-- Schema.org JSON-LD -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "CollectionPage",
    "name": "{{ $category->localized_name }}",
    "description": "{{ $category->localized_description ?? 'Articoli nella categoria ' . $category->localized_name }}",
    "url": "{{ request()->url() }}",
    "mainEntity": {
        "@type": "ItemList",
        "name": "Articoli {{ $category->localized_name }}",
        "numberOfItems": {{ $articles->total() }},
        "itemListElement": [
            @foreach($articles->take(10) as $index => $article)
                @php $translation = $article->translation(); @endphp
                {
                    "@type": "ListItem",
                    "position": {{ $index + 1 }},
                    "item": {
                        "@type": "Article",
                        "name": "{{ $translation?->title ?? 'Articolo' }}",
                        "url": "{{ $article->url }}",
                        "datePublished": "{{ $article->published_at?->toISOString() }}",
                        "author": {
                            "@type": "Person",
                            "name": "{{ $article->author?->name ?? 'PlaySudoku Team' }}"
                        }
                    }
                }{{ $loop->last ? '' : ',' }}
            @endforeach
        ]
    }
}
</script>

<div class="min-h-screen bg-gradient-to-br from-neutral-50 via-white to-neutral-100 dark:from-neutral-900 dark:via-neutral-800 dark:to-neutral-900">
    
    {{-- Breadcrumbs --}}
    <div class="bg-white/50 dark:bg-neutral-900/50 border-b border-neutral-200 dark:border-neutral-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="flex items-center space-x-2 text-sm text-neutral-600 dark:text-neutral-300">
                <a href="{{ route('localized.home', ['locale' => app()->getLocale()]) }}" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                    🏠 Home
                </a>
                <span>›</span>
                <a href="{{ route('localized.articles.index', ['locale' => app()->getLocale()]) }}" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                    📚 Articoli
                </a>
                <span>›</span>
                <span class="text-neutral-900 dark:text-white font-medium">{{ $category->icon }} {{ $category->localized_name }}</span>
            </nav>
        </div>
    </div>

    {{-- Category Hero --}}
    <div class="relative overflow-hidden text-white py-16" style="background: linear-gradient(135deg, {{ $category->color ?? '#3B82F6' }}, {{ $category->color ?? '#3B82F6' }}dd);">
        <div class="absolute inset-0 bg-black/20"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-4xl mx-auto">
                <div class="text-6xl mb-4">{{ $category->icon }}</div>
                <h1 class="text-4xl sm:text-5xl font-bold mb-4">
                    {{ $category->localized_name }}
                </h1>
                @if($category->localized_description)
                    <p class="text-xl text-white/90 leading-relaxed max-w-3xl mx-auto">
                        {{ $category->localized_description }}
                    </p>
                @endif
                <div class="mt-6 text-white/80">
                    <span class="inline-flex items-center px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full text-sm font-medium">
                        📖 {{ $articles->total() }} {{ $articles->total() === 1 ? 'articolo' : 'articoli' }}
                    </span>
                </div>
            </div>
        </div>
        
        {{-- Decorative pattern --}}
        <div class="absolute top-0 right-0 w-1/3 h-full opacity-10">
            <div class="grid grid-cols-4 gap-2 h-full p-8">
                @for($i = 0; $i < 32; $i++)
                    <div class="bg-white/30 rounded"></div>
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
                    <form method="GET" action="{{ route('localized.articles.category', ['locale' => app()->getLocale(), 'category' => $category->slug]) }}" class="flex flex-col sm:flex-row gap-4">
                        {{-- Search --}}
                        <div class="flex-1">
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Cerca in {{ $category->localized_name }}..."
                                   class="w-full px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white placeholder-neutral-500 dark:placeholder-neutral-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        </div>
                        
                        {{-- Submit Button --}}
                        <button type="submit" 
                                class="px-6 py-2 text-white font-medium rounded-lg shadow-sm transition-colors whitespace-nowrap"
                                style="background-color: {{ $category->color ?? '#3B82F6' }};">
                            🔍 Cerca
                        </button>
                    </form>
                </div>

                {{-- Articles Grid --}}
                @if($articles->count() > 0)
                    <div class="mb-8">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-bold text-neutral-900 dark:text-white">
                                @if(request('search'))
                                    🔍 Risultati per "{{ request('search') }}"
                                @else
                                    📖 Tutti gli Articoli
                                @endif
                            </h2>
                            <p class="text-neutral-600 dark:text-neutral-300">
                                {{ $articles->total() }} {{ $articles->total() === 1 ? 'articolo trovato' : 'articoli trovati' }}
                            </p>
                        </div>
                        
                        {{-- Featured Article First --}}
                        @php
                            $featuredArticle = $articles->where('featured', true)->first();
                            $regularArticles = $articles->where('featured', false);
                        @endphp
                        
                        @if($featuredArticle && !request('search'))
                            <div class="mb-8">
                                <h3 class="text-xl font-bold text-neutral-900 dark:text-white mb-4 flex items-center">
                                    ⭐ Articolo in Evidenza
                                </h3>
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    @include('articles.partials.featured-card', ['article' => $featuredArticle])
                                </div>
                            </div>
                            
                            @if($regularArticles->count() > 0)
                                <div class="border-t border-neutral-200 dark:border-neutral-700 pt-8">
                                    <h3 class="text-xl font-bold text-neutral-900 dark:text-white mb-6">Altri Articoli</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                                        @foreach($regularArticles as $article)
                                            @include('articles.partials.article-card', ['article' => $article])
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                                @foreach($articles as $article)
                                    @include('articles.partials.article-card', ['article' => $article])
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Pagination --}}
                    <div class="flex justify-center">
                        {{ $articles->withQueryString()->links() }}
                    </div>
                @else
                    {{-- No Articles --}}
                    <div class="text-center py-16">
                        <div class="w-32 h-32 mx-auto mb-6 rounded-full flex items-center justify-center text-6xl" style="background: linear-gradient(135deg, {{ $category->color ?? '#3B82F6' }}20, {{ $category->color ?? '#3B82F6' }}30);">
                            {{ $category->icon }}
                        </div>
                        <h3 class="text-xl font-bold text-neutral-900 dark:text-white mb-2">
                            @if(request('search'))
                                Nessun risultato trovato
                            @else
                                Nessun articolo in questa categoria
                            @endif
                        </h3>
                        <p class="text-neutral-600 dark:text-neutral-300 mb-6">
                            @if(request('search'))
                                Nessun articolo trovato per "{{ request('search') }}" in {{ $category->localized_name }}
                            @else
                                Non ci sono ancora articoli pubblicati in questa categoria.
                            @endif
                        </p>
                        <div class="space-y-3">
                            @if(request('search'))
                                <a href="{{ route('localized.articles.category', ['locale' => app()->getLocale(), 'category' => $category->slug]) }}" 
                                   class="inline-flex items-center px-4 py-2 text-white font-medium rounded-lg shadow-sm transition-colors"
                                   style="background-color: {{ $category->color ?? '#3B82F6' }};">
                                    Vedi tutti in {{ $category->localized_name }}
                                </a>
                            @endif
                            <a href="{{ route('localized.articles.index', ['locale' => app()->getLocale()]) }}" 
                               class="inline-flex items-center px-4 py-2 bg-neutral-600 hover:bg-neutral-700 text-white font-medium rounded-lg shadow-sm transition-colors">
                                📚 Vedi tutti gli articoli
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                
                {{-- Category Stats --}}
                <div class="bg-white/80 dark:bg-neutral-800/80 backdrop-blur-sm rounded-2xl shadow-lg border border-neutral-200/50 dark:border-neutral-700/50 p-6">
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white mb-4 flex items-center">
                        📊 Statistiche Categoria
                    </h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-neutral-600 dark:text-neutral-300">Articoli totali:</span>
                            <span class="font-bold text-neutral-900 dark:text-white">{{ $articles->total() }}</span>
                        </div>
                        @if($articles->count() > 0)
                            <div class="flex justify-between items-center">
                                <span class="text-neutral-600 dark:text-neutral-300">Ultimo aggiornamento:</span>
                                <span class="font-medium text-neutral-900 dark:text-white">{{ $articles->first()->published_at?->format('d M Y') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Other Categories --}}
                <div class="bg-white/80 dark:bg-neutral-800/80 backdrop-blur-sm rounded-2xl shadow-lg border border-neutral-200/50 dark:border-neutral-700/50 p-6">
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white mb-4 flex items-center">
                        📂 Altre Categorie
                    </h3>
                    <div class="space-y-2">
                        <a href="{{ route('localized.articles.index', ['locale' => app()->getLocale()]) }}" 
                           class="flex items-center px-3 py-2 rounded-lg text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors">
                            📚 Tutti gli articoli
                        </a>
                        @foreach($categories as $cat)
                            @if($cat->id !== $category->id)
                                <a href="{{ route('localized.articles.category', ['locale' => app()->getLocale(), 'category' => $cat->slug]) }}" 
                                   class="flex items-center justify-between px-3 py-2 rounded-lg text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors">
                                    <span class="flex items-center space-x-2">
                                        <span>{{ $cat->icon }}</span>
                                        <span>{{ $cat->localized_name }}</span>
                                    </span>
                                    <span class="text-xs bg-neutral-200 dark:bg-neutral-600 px-2 py-1 rounded-full">
                                        {{ $cat->published_count ?? 0 }}
                                    </span>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- Quick Links --}}
                <div class="bg-white/80 dark:bg-neutral-800/80 backdrop-blur-sm rounded-2xl shadow-lg border border-neutral-200/50 dark:border-neutral-700/50 p-6">
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white mb-4 flex items-center">
                        🔗 Link Utili
                    </h3>
                    <div class="space-y-2">
                        <a href="{{ route('localized.sudoku.training', ['locale' => app()->getLocale()]) }}" 
                           class="flex items-center px-3 py-2 rounded-lg text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors">
                            🎯 Modalità Allenamento
                        </a>
                        <a href="{{ route('localized.sudoku.analyzer', ['locale' => app()->getLocale()]) }}" 
                           class="flex items-center px-3 py-2 rounded-lg text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors">
                            🔍 Analizzatore Puzzle
                        </a>
                        @auth
                            <a href="{{ route('localized.challenges.index', ['locale' => app()->getLocale()]) }}" 
                               class="flex items-center px-3 py-2 rounded-lg text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors">
                                🏆 Sfide Competitive
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</x-site-layout>
