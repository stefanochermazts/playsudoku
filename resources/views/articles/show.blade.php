@extends('layouts.site')

@section('head')
{{-- SEO Meta Tags --}}
<title>{{ $translation->seo_title ?? $translation->title }} | PlaySudoku</title>
<meta name="description" content="{{ $translation->seo_description ?? $translation->excerpt }}">
@if($translation->meta_keywords && count($translation->meta_keywords) > 0)
    <meta name="keywords" content="{{ implode(', ', $translation->meta_keywords) }}">
@endif
<meta name="author" content="{{ $article->author?->name ?? 'PlaySudoku' }}">
<meta name="article:published_time" content="{{ $article->published_at?->toISOString() }}">
<meta name="article:modified_time" content="{{ $article->updated_at->toISOString() }}">
<meta name="article:section" content="{{ $article->category->localized_name }}">
@if($article->tags && count($article->tags) > 0)
    @foreach($article->tags as $tag)
        <meta name="article:tag" content="{{ $tag }}">
    @endforeach
@endif

{{-- Open Graph --}}
<meta property="og:title" content="{{ $translation->seo_title ?? $translation->title }}">
<meta property="og:description" content="{{ $translation->seo_description ?? $translation->excerpt }}">
<meta property="og:type" content="article">
<meta property="og:url" content="{{ request()->url() }}">
@if($article->featured_image_url)
    <meta property="og:image" content="{{ $article->featured_image_url }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="{{ $translation->title }}">
@endif
<meta property="article:published_time" content="{{ $article->published_at?->toISOString() }}">
<meta property="article:modified_time" content="{{ $article->updated_at->toISOString() }}">
<meta property="article:author" content="{{ $article->author?->name ?? 'PlaySudoku' }}">
<meta property="article:section" content="{{ $article->category->localized_name }}">

{{-- Twitter Card --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $translation->seo_title ?? $translation->title }}">
<meta name="twitter:description" content="{{ $translation->seo_description ?? $translation->excerpt }}">
@if($article->featured_image_url)
    <meta name="twitter:image" content="{{ $article->featured_image_url }}">
    <meta name="twitter:image:alt" content="{{ $translation->title }}">
@endif

{{-- Canonical URL --}}
<link rel="canonical" href="{{ $article->url }}">

{{-- Schema.org JSON-LD --}}
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Article",
    "headline": "{{ $translation->title }}",
    "description": "{{ $translation->excerpt }}",
    @if($article->featured_image_url)
    "image": {
        "@type": "ImageObject",
        "url": "{{ $article->featured_image_url }}",
        "width": 1200,
        "height": 630
    },
    @endif
    "author": {
        "@type": "Person",
        "name": "{{ $article->author?->name ?? 'PlaySudoku Team' }}"
    },
    "publisher": {
        "@type": "Organization",
        "name": "PlaySudoku",
        "logo": {
            "@type": "ImageObject",
            "url": "{{ asset('img/logo.png') }}"
        }
    },
    "datePublished": "{{ $article->published_at?->toISOString() }}",
    "dateModified": "{{ $article->updated_at->toISOString() }}",
    "articleSection": "{{ $article->category->localized_name }}",
    "keywords": [
        @if($article->tags && count($article->tags) > 0)
            @foreach($article->tags as $index => $tag)
                "{{ $tag }}"{{ $index < count($article->tags) - 1 ? ',' : '' }}
            @endforeach
        @endif
    ],
    "wordCount": {{ $translation->word_count ?? 0 }},
    "timeRequired": "PT{{ $article->reading_time_minutes }}M",
    "inLanguage": "{{ app()->getLocale() }}",
    "url": "{{ $article->url }}"
}
</script>

{{-- Article-specific styles --}}
<style>
.article-content {
    @apply text-neutral-700 dark:text-neutral-300 leading-relaxed;
}

.article-content h1,
.article-content h2,
.article-content h3,
.article-content h4,
.article-content h5,
.article-content h6 {
    @apply font-bold text-neutral-900 dark:text-white mt-8 mb-4 leading-tight;
}

.article-content h1 { @apply text-3xl; }
.article-content h2 { @apply text-2xl; }
.article-content h3 { @apply text-xl; }
.article-content h4 { @apply text-lg; }

.article-content p {
    @apply mb-4 leading-relaxed;
}

.article-content ul,
.article-content ol {
    @apply mb-4 pl-6;
}

.article-content li {
    @apply mb-2;
}

.article-content strong {
    @apply font-semibold text-neutral-900 dark:text-white;
}

.article-content blockquote {
    @apply border-l-4 border-primary-500 pl-4 italic text-neutral-600 dark:text-neutral-400 my-6 bg-neutral-50 dark:bg-neutral-800 p-4 rounded-r-lg;
}

.article-content pre {
    @apply bg-neutral-100 dark:bg-neutral-800 p-4 rounded-lg overflow-x-auto my-6 text-sm;
}

.article-content code {
    @apply bg-neutral-100 dark:bg-neutral-800 px-2 py-1 rounded text-sm font-mono;
}

.article-content a {
    @apply text-primary-600 dark:text-primary-400 hover:underline;
}

.article-content img {
    @apply rounded-lg my-6 max-w-full h-auto;
}

.article-content table {
    @apply w-full border-collapse border border-neutral-300 dark:border-neutral-600 my-6;
}

.article-content th,
.article-content td {
    @apply border border-neutral-300 dark:border-neutral-600 px-4 py-2;
}

.article-content th {
    @apply bg-neutral-100 dark:bg-neutral-800 font-semibold;
}
</style>
@endsection

@section('content')
<div class="min-h-screen bg-gradient-to-br from-neutral-50 via-white to-neutral-100 dark:from-neutral-900 dark:via-neutral-800 dark:to-neutral-900">
    
    {{-- Breadcrumbs --}}
    <div class="bg-white/50 dark:bg-neutral-900/50 border-b border-neutral-200 dark:border-neutral-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="flex items-center space-x-2 text-sm text-neutral-600 dark:text-neutral-300">
                <a href="{{ route('localized.home', ['locale' => app()->getLocale()]) }}" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                    🏠 Home
                </a>
                <span>›</span>
                <a href="{{ route('articles.index', ['locale' => app()->getLocale()]) }}" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                    📚 Articoli
                </a>
                <span>›</span>
                <a href="{{ route('articles.category', ['locale' => app()->getLocale(), 'category' => $category->slug]) }}" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
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
                                🕒 {{ $article->reading_time_minutes }} {{ __('app.editorial.minutes') }}
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
                        <div class="text-xl text-neutral-600 dark:text-neutral-300 font-medium leading-relaxed p-6 bg-gradient-to-r from-primary-50 to-secondary-50 dark:from-primary-900/20 dark:to-secondary-900/20 rounded-xl border-l-4 border-primary-500">
                            {{ $translation->excerpt }}
                        </div>
                    @endif

                    {{-- Author & Social Share --}}
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mt-6 pt-6 border-t border-neutral-200 dark:border-neutral-700">
                        <div class="flex items-center space-x-3 mb-4 sm:mb-0">
                            <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-secondary-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                                {{ strtoupper(substr($article->author?->name ?? 'P', 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-neutral-900 dark:text-white">
                                    {{ $article->author?->name ?? 'PlaySudoku Team' }}
                                </p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                    {{ __('app.editorial.published_on', ['date' => $article->published_at?->format('d F Y')]) }}
                                </p>
                            </div>
                        </div>
                        
                        {{-- Social Share --}}
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-neutral-600 dark:text-neutral-300 mr-2">{{ __('app.editorial.share_article') }}:</span>
                            <a href="https://twitter.com/intent/tweet?text={{ urlencode($translation->title) }}&url={{ urlencode(request()->url()) }}" 
                               target="_blank" 
                               class="inline-flex items-center justify-center w-8 h-8 bg-blue-500 hover:bg-blue-600 text-white rounded-full transition-colors">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                </svg>
                            </a>
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" 
                               target="_blank"
                               class="inline-flex items-center justify-center w-8 h-8 bg-blue-600 hover:bg-blue-700 text-white rounded-full transition-colors">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                            </a>
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(request()->url()) }}" 
                               target="_blank"
                               class="inline-flex items-center justify-center w-8 h-8 bg-blue-700 hover:bg-blue-800 text-white rounded-full transition-colors">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
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

                {{-- Article Navigation --}}
                @if($previousArticle || $nextArticle)
                    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if($previousArticle)
                            @php $prevTranslation = $previousArticle->translation(); @endphp
                            <a href="{{ $previousArticle->url }}" 
                               class="group bg-white/80 dark:bg-neutral-800/80 backdrop-blur-sm rounded-xl shadow-lg hover:shadow-xl border border-neutral-200/50 dark:border-neutral-700/50 p-6 transition-all duration-300 hover:scale-[1.02]">
                                <div class="flex items-center space-x-3 mb-2">
                                    <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                    </svg>
                                    <span class="text-sm text-neutral-600 dark:text-neutral-300">{{ __('app.editorial.previous_article') }}</span>
                                </div>
                                <h3 class="font-bold text-neutral-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors line-clamp-2">
                                    {{ $prevTranslation?->title ?? 'Articolo precedente' }}
                                </h3>
                            </a>
                        @endif
                        
                        @if($nextArticle)
                            @php $nextTranslation = $nextArticle->translation(); @endphp
                            <a href="{{ $nextArticle->url }}" 
                               class="group bg-white/80 dark:bg-neutral-800/80 backdrop-blur-sm rounded-xl shadow-lg hover:shadow-xl border border-neutral-200/50 dark:border-neutral-700/50 p-6 transition-all duration-300 hover:scale-[1.02] {{ !$previousArticle ? 'md:col-start-2' : '' }}">
                                <div class="flex items-center justify-end space-x-3 mb-2">
                                    <span class="text-sm text-neutral-600 dark:text-neutral-300">{{ __('app.editorial.next_article') }}</span>
                                    <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </div>
                                <h3 class="font-bold text-neutral-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors text-right line-clamp-2">
                                    {{ $nextTranslation?->title ?? 'Articolo successivo' }}
                                </h3>
                            </a>
                        @endif
                    </div>
                @endif
            </article>

            {{-- Sidebar --}}
            <aside class="space-y-6">
                
                {{-- Table of Contents --}}
                <div class="bg-white/80 dark:bg-neutral-800/80 backdrop-blur-sm rounded-2xl shadow-lg border border-neutral-200/50 dark:border-neutral-700/50 p-6">
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white mb-4 flex items-center">
                        📋 Indice
                    </h3>
                    <div id="table-of-contents" class="space-y-2 text-sm">
                        {{-- TOC will be generated by JavaScript --}}
                    </div>
                </div>

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
                    <a href="{{ route('articles.category', ['locale' => app()->getLocale(), 'category' => $category->slug]) }}" 
                       class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                        {{ __('app.editorial.back_to_category') }}
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>

                {{-- Related Articles --}}
                @if($relatedArticles->count() > 0)
                    <div class="bg-white/80 dark:bg-neutral-800/80 backdrop-blur-sm rounded-2xl shadow-lg border border-neutral-200/50 dark:border-neutral-700/50 p-6">
                        <h3 class="text-lg font-bold text-neutral-900 dark:text-white mb-4 flex items-center">
                            🔗 {{ __('app.editorial.related_articles') }}
                        </h3>
                        <div class="space-y-4">
                            @foreach($relatedArticles as $relatedArticle)
                                @include('articles.partials.sidebar-card', ['article' => $relatedArticle])
                            @endforeach
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </div>
</div>

{{-- Table of Contents JavaScript --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Generate Table of Contents
    const content = document.querySelector('.article-content');
    const toc = document.getElementById('table-of-contents');
    
    if (content && toc) {
        const headings = content.querySelectorAll('h1, h2, h3, h4, h5, h6');
        
        if (headings.length > 0) {
            headings.forEach((heading, index) => {
                // Add ID to heading if it doesn't have one
                if (!heading.id) {
                    heading.id = 'heading-' + index;
                }
                
                // Create TOC link
                const link = document.createElement('a');
                link.href = '#' + heading.id;
                link.textContent = heading.textContent;
                link.className = 'block py-1 text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors';
                
                // Add indentation based on heading level
                const level = parseInt(heading.tagName.charAt(1));
                if (level > 2) {
                    link.style.paddingLeft = (level - 2) * 16 + 'px';
                    link.className += ' text-sm';
                }
                
                // Smooth scroll
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    heading.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
                
                toc.appendChild(link);
            });
        } else {
            toc.innerHTML = '<p class="text-neutral-500 dark:text-neutral-400 text-sm italic">Nessun titolo trovato</p>';
        }
    }
});
</script>
@endsection
