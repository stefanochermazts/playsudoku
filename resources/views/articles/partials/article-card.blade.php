@php
    $translation = $article->translation();
    $categoryColor = $article->category->color ?? '#3B82F6';
@endphp

<article class="group bg-white/80 dark:bg-neutral-800/80 backdrop-blur-sm rounded-xl shadow-lg hover:shadow-xl border border-neutral-200/50 dark:border-neutral-700/50 overflow-hidden transition-all duration-300 hover:scale-[1.02]">
    
    {{-- Image/Thumbnail --}}
    <div class="aspect-video relative overflow-hidden bg-gradient-to-br from-neutral-100 to-neutral-200 dark:from-neutral-700 dark:to-neutral-800">
        @if($article->featured_image_url)
            <img src="{{ $article->featured_image_url }}" 
                 alt="{{ $translation?->title }}" 
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
        @else
            {{-- Category-themed placeholder --}}
            <div class="w-full h-full flex items-center justify-center relative" style="background: linear-gradient(135deg, {{ $categoryColor }}15, {{ $categoryColor }}25);">
                <div class="absolute inset-0 opacity-10">
                    <div class="grid grid-cols-6 gap-1 h-full p-4">
                        @for($i = 0; $i < 36; $i++)
                            <div class="bg-current rounded-sm"></div>
                        @endfor
                    </div>
                </div>
                <span class="text-4xl" style="color: {{ $categoryColor }};">{{ $article->category->icon }}</span>
            </div>
        @endif
        
        {{-- Category Badge --}}
        <div class="absolute top-3 left-3">
            <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium text-white backdrop-blur-sm" 
                  style="background-color: {{ $categoryColor }};">
                {{ $article->category->icon }} {{ $article->category->localized_name }}
            </span>
        </div>
        
        {{-- Reading Time --}}
        <div class="absolute top-3 right-3">
            <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium bg-black/50 text-white backdrop-blur-sm">
                🕒 {{ $article->reading_time_minutes }} min
            </span>
        </div>
    </div>

    {{-- Content --}}
    <div class="p-5">
        <h3 class="text-lg font-bold text-neutral-900 dark:text-white mb-2 line-clamp-2 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
            <a href="{{ $article->url }}" class="block">
                {{ $translation?->title ?? 'Titolo non disponibile' }}
            </a>
        </h3>
        
        @if($translation?->excerpt)
            <p class="text-neutral-600 dark:text-neutral-300 text-sm line-clamp-3 mb-4">
                {{ $translation->excerpt }}
            </p>
        @endif

        {{-- Footer --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3 text-xs text-neutral-500 dark:text-neutral-400">
                @if($article->published_at)
                    <span class="flex items-center">
                        📅 {{ $article->published_at->format('d M') }}
                    </span>
                @endif
                @if($article->views_count > 0)
                    <span class="flex items-center">
                        👁️ {{ $article->views_count }}
                    </span>
                @endif
            </div>
            
            <a href="{{ $article->url }}" 
               class="inline-flex items-center text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 text-sm font-medium transition-colors group">
                {{ __('app.editorial.read_more') }}
                <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    </div>
</article>
