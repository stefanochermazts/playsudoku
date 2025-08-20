@php
    $translation = $article->translation();
    $categoryColor = $article->category->color ?? '#3B82F6';
@endphp

<article class="group relative bg-white dark:bg-neutral-800 rounded-2xl shadow-lg hover:shadow-xl border border-neutral-200 dark:border-neutral-700 overflow-hidden transition-all duration-300 hover:scale-[1.02]">
    {{-- Featured Badge --}}
    <div class="absolute top-4 left-4 z-10">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gradient-to-r from-yellow-400 to-orange-500 text-white shadow-lg">
            ⭐ Featured
        </span>
    </div>

    {{-- Featured Image --}}
    <div class="aspect-video relative overflow-hidden bg-gradient-to-br from-primary-100 to-secondary-100 dark:from-primary-900 dark:to-secondary-900">
        @if($article->featured_image_url)
            <img src="{{ $article->featured_image_url }}" 
                 alt="{{ $translation?->title }}" 
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
        @else
            {{-- Placeholder with Sudoku pattern --}}
            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br" style="background: linear-gradient(135deg, {{ $categoryColor }}20, {{ $categoryColor }}40);">
                <div class="grid grid-cols-3 gap-1 opacity-20">
                    @for($i = 0; $i < 9; $i++)
                        <div class="w-4 h-4 border border-current"></div>
                    @endfor
                </div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-4xl opacity-60">{{ $article->category->icon }}</span>
                </div>
            </div>
        @endif
        
        {{-- Overlay --}}
        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
        
        {{-- Category Badge --}}
        <div class="absolute bottom-4 left-4">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium text-white" 
                  style="background-color: {{ $categoryColor }};">
                {{ $article->category->icon }} {{ $article->category->localized_name }}
            </span>
        </div>
        
        {{-- Reading Time --}}
        <div class="absolute bottom-4 right-4">
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-black/50 text-white backdrop-blur-sm">
                🕒 {{ $article->reading_time_minutes }} {{ __('app.editorial.minutes') }}
            </span>
        </div>
    </div>

    {{-- Content --}}
    <div class="p-6">
        <div class="flex items-start justify-between mb-3">
            <div class="flex-1">
                <h3 class="text-xl font-bold text-neutral-900 dark:text-white mb-2 line-clamp-2 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                    <a href="{{ $article->url }}" class="block">
                        {{ $translation?->title ?? 'Titolo non disponibile' }}
                    </a>
                </h3>
                
                @if($translation?->excerpt)
                    <p class="text-neutral-600 dark:text-neutral-300 text-sm line-clamp-3 mb-4">
                        {{ $translation->excerpt }}
                    </p>
                @endif
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-between text-sm text-neutral-500 dark:text-neutral-400">
            <div class="flex items-center space-x-4">
                @if($article->published_at)
                    <span class="flex items-center">
                        📅 {{ $article->published_at->format('d M Y') }}
                    </span>
                @endif
                @if($article->views_count > 0)
                    <span class="flex items-center">
                        👁️ {{ number_format($article->views_count) }}
                    </span>
                @endif
            </div>
            
            <a href="{{ $article->url }}" 
               class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors group">
                {{ __('app.editorial.read_more') }}
                <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    </div>
</article>
