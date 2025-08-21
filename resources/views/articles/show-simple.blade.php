<x-site-layout 
    seo-title="{{ $translation->seo_title ?? $translation->title }} | PlaySudoku"
    seo-description="{{ $translation->seo_description ?? $translation->excerpt }}">

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
                <a href="{{ route('localized.articles.category', ['locale' => app()->getLocale(), 'category' => $category->slug]) }}" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                    {{ $category->icon }} {{ $category->localized_name }}
                </a>
                <span>›</span>
                <span class="text-neutral-900 dark:text-white font-medium">{{ $translation->title }}</span>
            </nav>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            {{-- Main Article Content --}}
            <article class="lg:col-span-3">
                
                {{-- Article Header --}}
                <header class="bg-white/80 dark:bg-neutral-800/80 backdrop-blur-sm rounded-2xl shadow-lg border border-neutral-200/50 dark:border-neutral-700/50 p-8 mb-8">
                    
                    {{-- Category & Meta Info --}}
                    <div class="flex flex-wrap items-center gap-3 mb-6">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium text-white" 
                              style="background-color: {{ $category->color }};">
                            {{ $category->icon }} {{ $category->localized_name }}
                        </span>
                        
                        <div class="flex items-center space-x-4 text-sm text-neutral-600 dark:text-neutral-300">
                            @if($article->published_at)
                                <span class="flex items-center">
                                    📅 {{ $article->published_at->format('d F Y') }}
                                </span>
                            @endif
                            <span class="flex items-center">
                                🕒 {{ $article->reading_time_minutes }} minuti
                            </span>
                            @if($article->views_count > 0)
                                <span class="flex items-center">
                                    👁️ {{ number_format($article->views_count) }} visite
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Title --}}
                    <h1 class="text-3xl sm:text-4xl font-bold text-neutral-900 dark:text-white mb-4 leading-tight">
                        {{ $translation->title }}
                    </h1>
                    
                    {{-- Excerpt --}}
                    @if($translation->excerpt)
                        <p class="text-xl text-neutral-600 dark:text-neutral-300 leading-relaxed">
                            {{ $translation->excerpt }}
                        </p>
                    @endif
                    
                </header>

                {{-- Featured Image --}}
                @if($article->featured_image_url)
                    <div class="mb-8">
                        <div class="bg-white/80 dark:bg-neutral-800/80 backdrop-blur-sm rounded-2xl shadow-lg border border-neutral-200/50 dark:border-neutral-700/50 p-2">
                            <img src="{{ $article->featured_image_url }}" 
                                 alt="{{ $translation->title }}" 
                                 class="w-full h-64 sm:h-80 lg:h-96 object-cover rounded-xl">
                        </div>
                    </div>
                @endif

                {{-- Article Content --}}
                <div class="bg-white/80 dark:bg-neutral-800/80 backdrop-blur-sm rounded-2xl shadow-lg border border-neutral-200/50 dark:border-neutral-700/50 p-8">
                    <div class="article-content prose prose-lg prose-neutral dark:prose-invert max-w-none">
                        {!! $translation->content !!}
                    </div>
                    
                    {{-- Tags --}}
                    @if($article->tags && count($article->tags) > 0)
                        <div class="mt-8 pt-8 border-t border-neutral-200 dark:border-neutral-700">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm font-medium text-neutral-600 dark:text-neutral-300">🏷️ Tags:</span>
                                @foreach($article->tags as $tag)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200">
                                        #{{ $tag }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

            </article>

            {{-- Sidebar --}}
            <aside class="space-y-6">
                
                {{-- Category Info --}}
                <div class="bg-white/80 dark:bg-neutral-800/80 backdrop-blur-sm rounded-2xl shadow-lg border border-neutral-200/50 dark:border-neutral-700/50 p-6">
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white mb-4 flex items-center">
                        {{ $category->icon }} {{ $category->localized_name }}
                    </h3>
                    @if($category->localized_description)
                        <p class="text-neutral-600 dark:text-neutral-300 text-sm mb-4">
                            {{ $category->localized_description }}
                        </p>
                    @endif
                    <a href="{{ route('localized.articles.category', ['locale' => app()->getLocale(), 'category' => $category->slug]) }}" 
                       class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                        Tutti gli articoli
                    </a>
                </div>
                
            </aside>
        </div>
    </div>

</div>
</x-site-layout>
