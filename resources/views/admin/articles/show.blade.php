@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-neutral-50 to-neutral-100 dark:from-neutral-900 dark:to-neutral-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Header --}}
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.articles.index') }}" 
                       class="inline-flex items-center text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Torna agli articoli
                    </a>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('admin.articles.edit', $article) }}" 
                       class="inline-flex items-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                        ✏️ Modifica
                    </a>
                    @if($article->status === 'published')
                        <a href="{{ $article->url }}" 
                           target="_blank"
                           class="inline-flex items-center px-3 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                            🌐 Apri sul sito
                        </a>
                    @endif
                    <form method="POST" action="{{ route('admin.articles.duplicate', $article) }}" class="inline">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center px-3 py-2 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-700 dark:hover:bg-neutral-600 text-neutral-700 dark:text-neutral-300 font-medium rounded-lg transition-colors">
                            📋 Duplica
                        </button>
                    </form>
                </div>
            </div>
            @php
                $italianTranslation = $article->italianTranslation();
            @endphp
            <h1 class="text-3xl font-bold text-neutral-900 dark:text-white mt-4">
                {{ $italianTranslation?->title ?? 'Titolo mancante' }}
            </h1>
            <div class="flex items-center space-x-4 mt-2">
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-2 py-1 rounded text-sm font-medium" style="background-color: {{ $article->category->color }}20; color: {{ $article->category->color }};">
                        {{ $article->category->icon }} {{ $article->category->localized_name }}
                    </span>
                    @if($article->status === 'published')
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                            ✅ {{ __('app.editorial.published') }}
                        </span>
                    @elseif($article->status === 'draft')
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                            📝 {{ __('app.editorial.draft') }}
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                            📦 {{ __('app.editorial.archived') }}
                        </span>
                    @endif
                    @if($article->featured)
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                            ⭐ Featured
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- Article Content --}}
                <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                    @if($article->featured_image)
                        <div class="mb-6">
                            <img src="{{ $article->featured_image_url }}" 
                                 alt="{{ $italianTranslation?->title }}" 
                                 class="w-full h-64 object-cover rounded-lg">
                        </div>
                    @endif
                    
                    <div class="prose prose-neutral dark:prose-invert max-w-none">
                        @if($italianTranslation?->excerpt)
                            <div class="text-lg text-neutral-600 dark:text-neutral-300 font-medium mb-6 p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg border-l-4 border-primary-500">
                                {{ $italianTranslation->excerpt }}
                            </div>
                        @endif
                        
                        @if($italianTranslation?->content)
                            <div class="article-content">
                                {!! $italianTranslation->content !!}
                            </div>
                        @else
                            <p class="text-neutral-500 dark:text-neutral-400 italic">Nessun contenuto disponibile</p>
                        @endif
                    </div>
                </div>

                {{-- Translation Status --}}
                <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">🌍 Stato Traduzioni</h2>
                        @if($translationCompleteness['percentage'] < 100)
                            <form method="POST" action="{{ route('admin.articles.translate', $article) }}" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="inline-flex items-center px-3 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                                    🤖 {{ __('app.editorial.translate_now') }}
                                </button>
                            </form>
                        @endif
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach(['it' => 'Italiano', 'en' => 'English', 'de' => 'Deutsch', 'es' => 'Español'] as $locale => $language)
                            @php
                                $translation = $article->translation($locale);
                                $isComplete = $translationCompleteness['details'][$locale] ?? false;
                            @endphp
                            <div class="border border-neutral-200 dark:border-neutral-600 rounded-lg p-4 {{ $isComplete ? 'bg-green-50 dark:bg-green-900/20' : 'bg-neutral-50 dark:bg-neutral-700' }}">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="font-medium text-neutral-900 dark:text-white">{{ $language }}</h3>
                                    @if($isComplete)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            ✅ Completo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                            ⏳ Mancante
                                        </span>
                                    @endif
                                </div>
                                
                                @if($translation)
                                    <div class="space-y-1 text-sm text-neutral-600 dark:text-neutral-300">
                                        <div><strong>Titolo:</strong> {{ Str::limit($translation->title, 40) }}</div>
                                        <div><strong>Stato:</strong> 
                                            @if($translation->translation_status === 'approved')
                                                <span class="text-green-600 dark:text-green-400">✅ Approvato</span>
                                            @elseif($translation->translation_status === 'auto_translated')
                                                <span class="text-blue-600 dark:text-blue-400">🤖 Auto-tradotto</span>
                                            @elseif($translation->translation_status === 'human_reviewed')
                                                <span class="text-yellow-600 dark:text-yellow-400">👁️ In revisione</span>
                                            @else
                                                <span class="text-gray-600 dark:text-gray-400">⏳ In attesa</span>
                                            @endif
                                        </div>
                                        @if($translation->word_count)
                                            <div><strong>Parole:</strong> {{ number_format($translation->word_count) }}</div>
                                        @endif
                                        @if($translation->translated_at)
                                            <div><strong>Tradotto:</strong> {{ $translation->translated_at->format('d/m/Y H:i') }}</div>
                                        @endif
                                    </div>
                                @else
                                    <p class="text-sm text-neutral-500 dark:text-neutral-400">Non ancora tradotto</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-4">
                        <div class="flex items-center justify-between text-sm text-neutral-600 dark:text-neutral-300 mb-2">
                            <span>Completamento traduzioni</span>
                            <span>{{ $translationCompleteness['percentage'] }}%</span>
                        </div>
                        <div class="w-full bg-neutral-200 dark:bg-neutral-600 rounded-full h-2">
                            <div class="bg-gradient-to-r from-blue-500 to-green-500 h-2 rounded-full transition-all duration-300" 
                                 style="width: {{ $translationCompleteness['percentage'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                
                {{-- Article Info --}}
                <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">📊 Informazioni</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-neutral-600 dark:text-neutral-400">Autore:</span>
                            <span class="text-neutral-900 dark:text-white">{{ $article->author?->name ?? 'Sconosciuto' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-neutral-600 dark:text-neutral-400">Creato:</span>
                            <span class="text-neutral-900 dark:text-white">{{ $article->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-neutral-600 dark:text-neutral-400">Modificato:</span>
                            <span class="text-neutral-900 dark:text-white">{{ $article->updated_at->format('d/m/Y H:i') }}</span>
                        </div>
                        @if($article->published_at)
                            <div class="flex justify-between">
                                <span class="text-neutral-600 dark:text-neutral-400">Pubblicato:</span>
                                <span class="text-neutral-900 dark:text-white">{{ $article->published_at->format('d/m/Y H:i') }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-neutral-600 dark:text-neutral-400">Lettura:</span>
                            <span class="text-neutral-900 dark:text-white">{{ $article->reading_time_minutes }} {{ __('app.editorial.minutes') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-neutral-600 dark:text-neutral-400">Visualizzazioni:</span>
                            <span class="text-neutral-900 dark:text-white">{{ number_format($article->views_count) }}</span>
                        </div>
                        @if($article->last_viewed_at)
                            <div class="flex justify-between">
                                <span class="text-neutral-600 dark:text-neutral-400">Ultima visita:</span>
                                <span class="text-neutral-900 dark:text-white">{{ $article->last_viewed_at->format('d/m/Y H:i') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- SEO Info --}}
                @if($italianTranslation && ($italianTranslation->meta_title || $italianTranslation->meta_description))
                    <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">🔍 SEO</h3>
                        @if($italianTranslation->meta_title)
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-neutral-600 dark:text-neutral-300 mb-1">Meta Title:</label>
                                <p class="text-sm text-neutral-900 dark:text-white">{{ $italianTranslation->meta_title }}</p>
                            </div>
                        @endif
                        @if($italianTranslation->meta_description)
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-neutral-600 dark:text-neutral-300 mb-1">Meta Description:</label>
                                <p class="text-sm text-neutral-900 dark:text-white">{{ $italianTranslation->meta_description }}</p>
                            </div>
                        @endif
                        @if($article->tags && count($article->tags) > 0)
                            <div>
                                <label class="block text-sm font-medium text-neutral-600 dark:text-neutral-300 mb-1">Tags:</label>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($article->tags as $tag)
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200">
                                            #{{ $tag }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Settings --}}
                <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">⚙️ Impostazioni</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-neutral-600 dark:text-neutral-400">Traduzione automatica:</span>
                            <span class="{{ $article->auto_translate ? 'text-green-600 dark:text-green-400' : 'text-gray-600 dark:text-gray-400' }}">
                                {{ $article->auto_translate ? '✅ Attiva' : '❌ Disattiva' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-neutral-600 dark:text-neutral-400">In evidenza:</span>
                            <span class="{{ $article->featured ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-600 dark:text-gray-400' }}">
                                {{ $article->featured ? '⭐ Si' : '👁️ No' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">⚡ Azioni Rapide</h3>
                    <div class="space-y-2">
                        <a href="{{ route('admin.articles.edit', $article) }}" 
                           class="block w-full px-3 py-2 bg-blue-100 hover:bg-blue-200 dark:bg-blue-900 dark:hover:bg-blue-800 text-blue-700 dark:text-blue-300 font-medium rounded-lg text-center transition-colors">
                            ✏️ Modifica Articolo
                        </a>
                        @if($translationCompleteness['percentage'] < 100)
                            <form method="POST" action="{{ route('admin.articles.translate', $article) }}">
                                @csrf
                                <button type="submit" 
                                        class="block w-full px-3 py-2 bg-green-100 hover:bg-green-200 dark:bg-green-900 dark:hover:bg-green-800 text-green-700 dark:text-green-300 font-medium rounded-lg transition-colors">
                                    🤖 Traduci Ora
                                </button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('admin.articles.duplicate', $article) }}">
                            @csrf
                            <button type="submit" 
                                    class="block w-full px-3 py-2 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-700 dark:hover:bg-neutral-600 text-neutral-700 dark:text-neutral-300 font-medium rounded-lg transition-colors">
                                📋 Duplica Articolo
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.articles.destroy', $article) }}" 
                              onsubmit="return confirm('Sei sicuro di voler eliminare questo articolo?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="block w-full px-3 py-2 bg-red-100 hover:bg-red-200 dark:bg-red-900 dark:hover:bg-red-800 text-red-700 dark:text-red-300 font-medium rounded-lg transition-colors">
                                🗑️ Elimina Articolo
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.article-content h1,
.article-content h2,
.article-content h3,
.article-content h4,
.article-content h5,
.article-content h6 {
    margin-top: 1.5em;
    margin-bottom: 0.5em;
    font-weight: 600;
}

.article-content h1 { font-size: 1.875rem; }
.article-content h2 { font-size: 1.5rem; }
.article-content h3 { font-size: 1.25rem; }

.article-content p {
    margin-bottom: 1em;
    line-height: 1.7;
}

.article-content ul,
.article-content ol {
    margin-bottom: 1em;
    padding-left: 1.5em;
}

.article-content li {
    margin-bottom: 0.25em;
}

.article-content strong {
    font-weight: 600;
}

.article-content pre {
    background: #f3f4f6;
    padding: 1rem;
    border-radius: 0.5rem;
    overflow-x: auto;
    margin: 1em 0;
}

.dark .article-content pre {
    background: #374151;
}
</style>
@endsection
