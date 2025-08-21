<x-site-layout 
    seo-title="{{ $category->localized_name }} - Articoli | PlaySudoku"
    seo-description="{{ $category->localized_description ?? 'Scopri tutti gli articoli nella categoria ' . $category->localized_name . ' su PlaySudoku.' }}">

<div class="min-h-screen bg-gradient-to-br from-neutral-50 via-white to-neutral-100 dark:from-neutral-900 dark:via-neutral-800 dark:to-neutral-900">
    
    {{-- Hero Section --}}
    <div class="relative overflow-hidden bg-gradient-to-r from-primary-600 via-primary-700 to-secondary-600 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl sm:text-5xl font-bold mb-4">
                {{ $category->icon }} {{ $category->localized_name }}
            </h1>
            @if($category->localized_description)
                <p class="text-xl text-white/90 leading-relaxed max-w-3xl mx-auto">
                    {{ $category->localized_description }}
                </p>
            @endif
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            {{-- Main Content --}}
            <div class="lg:col-span-3">
                
                {{-- Articles Grid --}}
                @if($articles->count() > 0)
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-neutral-900 dark:text-white mb-6">
                            📖 Articoli in {{ $category->localized_name }}
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                            @foreach($articles as $article)
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
                        <h3 class="text-xl font-bold text-neutral-900 dark:text-white mb-2">
                            Nessun articolo in questa categoria
                        </h3>
                        <p class="text-neutral-600 dark:text-neutral-300 mb-6">
                            Non ci sono ancora articoli pubblicati in questa categoria.
                        </p>
                    </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <aside class="space-y-6">
                <div class="bg-white/80 dark:bg-neutral-800/80 backdrop-blur-sm rounded-2xl shadow-lg border border-neutral-200/50 dark:border-neutral-700/50 p-6">
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white mb-4">
                        📊 Statistiche
                    </h3>
                    <p class="text-neutral-600 dark:text-neutral-300">
                        Articoli totali: <strong>{{ $articles->total() }}</strong>
                    </p>
                </div>
            </aside>
        </div>
    </div>

</div>
</x-site-layout>
