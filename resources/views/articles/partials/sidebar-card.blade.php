@php
    $translation = $article->translation();
    $categoryColor = $article->category->color ?? '#3B82F6';
@endphp

<article class="group flex space-x-3 p-3 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-colors">
    {{-- Thumbnail --}}
    <div class="flex-shrink-0 w-16 h-16 rounded-lg overflow-hidden bg-gradient-to-br from-neutral-100 to-neutral-200 dark:from-neutral-600 dark:to-neutral-700">
        @if($article->featured_image_url)
            <img src="{{ $article->featured_image_url }}" 
                 alt="{{ $translation?->title }}" 
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
        @else
            <div class="w-full h-full flex items-center justify-center" style="background: linear-gradient(135deg, {{ $categoryColor }}20, {{ $categoryColor }}30);">
                <span class="text-lg" style="color: {{ $categoryColor }};">{{ $article->category->icon }}</span>
            </div>
        @endif
    </div>

    {{-- Content --}}
    <div class="flex-1 min-w-0">
        <h4 class="text-sm font-bold text-neutral-900 dark:text-white line-clamp-2 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors mb-1">
            <a href="{{ $article->url }}" class="block">
                {{ $translation?->title ?? 'Titolo non disponibile' }}
            </a>
        </h4>
        
        <div class="flex items-center space-x-2 text-xs text-neutral-500 dark:text-neutral-400">
            <span style="color: {{ $categoryColor }};">{{ $article->category->icon }}</span>
            @if($article->published_at)
                <span>{{ $article->published_at->format('d M') }}</span>
            @endif
            <span>•</span>
            <span>{{ $article->reading_time_minutes }} min</span>
        </div>
    </div>
</article>
