<x-site-layout>
    <div class="min-h-screen bg-gradient-to-br from-primary-50 via-neutral-50 to-secondary-50 dark:from-neutral-900 dark:via-neutral-950 dark:to-neutral-900">
        <!-- Header -->
        <div class="bg-white/80 dark:bg-neutral-900/80 backdrop-blur-sm border-b border-neutral-200 dark:border-neutral-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">🏷️ Gestione Categorie Articoli</h1>
                        <p class="text-neutral-600 dark:text-neutral-300 mt-2">Gestisci le categorie per organizzare gli articoli del sistema editoriale</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-white/10 dark:bg-neutral-800/50 border border-neutral-300 dark:border-neutral-600 text-neutral-900 dark:text-neutral-100 font-medium rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-all">
                            ← Dashboard Admin
                        </a>
                
                <a href="{{ route('admin.article-categories.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Nuova Categoria
                </a>
            </div>
            <p class="mt-2 text-neutral-600 dark:text-neutral-300">
                Gestisci le categorie per organizzare gli articoli del sistema editoriale
            </p>
        </div>

        {{-- Success Message --}}
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg dark:bg-green-800/20 dark:border-green-700 dark:text-green-300">
                {{ session('success') }}
            </div>
        @endif

        {{-- Categories List --}}
        <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden">
            
            {{-- Table Header --}}
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-750">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">
                    Categorie Attive ({{ $categories->count() }})
                </h2>
            </div>

            {{-- Table --}}
            @if($categories->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-neutral-100 dark:bg-neutral-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">Categoria</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">Traduzioni</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">Articoli</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">Ordine</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">Stato</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-600">
                            @foreach($categories as $category)
                                <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors">
                                    {{-- Category Info --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <span class="text-2xl mr-3">{{ $category->icon }}</span>
                                            <div>
                                                <div class="text-sm font-medium text-neutral-900 dark:text-white">
                                                    {{ $category->name_it }}
                                                </div>
                                                <div class="text-sm text-neutral-500 dark:text-neutral-400">
                                                    {{ $category->slug }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Translations --}}
                                    <td class="px-6 py-4">
                                        <div class="flex space-x-1">
                                            @foreach(['it', 'en', 'de', 'es'] as $locale)
                                                @php
                                                    $hasTranslation = !empty($category->{"name_$locale"});
                                                @endphp
                                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium 
                                                    {{ $hasTranslation 
                                                        ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' 
                                                        : 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100' }}">
                                                    {{ strtoupper($locale) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>

                                    {{-- Articles Count --}}
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                            {{ $category->articles_count ?? 0 }} articoli
                                        </span>
                                    </td>

                                    {{-- Order --}}
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-neutral-900 dark:text-white font-mono">
                                            {{ $category->sort_order }}
                                        </span>
                                    </td>

                                    {{-- Status --}}
                                    <td class="px-6 py-4">
                                        @if($category->active)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                                ✅ Attiva
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                                                ❌ Disattiva
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Actions --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('admin.article-categories.edit', $category) }}" 
                                               class="inline-flex items-center px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 text-sm font-medium rounded transition-colors">
                                                ✏️ Modifica
                                            </a>
                                            
                                            @if($category->articles_count == 0)
                                                <form action="{{ route('admin.article-categories.destroy', $category) }}" 
                                                      method="POST" 
                                                      class="inline"
                                                      onsubmit="return confirm('Sei sicuro di voler eliminare questa categoria?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="inline-flex items-center px-3 py-1 bg-red-100 hover:bg-red-200 text-red-700 text-sm font-medium rounded transition-colors">
                                                        🗑️ Elimina
                                                    </button>
                                                </form>
                                            @else
                                                <span class="inline-flex items-center px-3 py-1 bg-neutral-100 text-neutral-500 text-sm font-medium rounded cursor-not-allowed" 
                                                      title="Non puoi eliminare una categoria con articoli">
                                                    🔒 Protetta
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                {{-- Empty State --}}
                <div class="px-6 py-12 text-center">
                    <div class="text-6xl mb-4">🏷️</div>
                    <h3 class="text-lg font-medium text-neutral-900 dark:text-white mb-2">
                        Nessuna categoria trovata
                    </h3>
                    <p class="text-neutral-500 dark:text-neutral-400 mb-6">
                        Inizia creando la tua prima categoria per organizzare gli articoli
                    </p>
                    <a href="{{ route('admin.article-categories.create') }}" 
                       class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Crea Prima Categoria
                    </a>
                </div>
            @endif
        </div>

        {{-- Quick Stats --}}
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-800 rounded-lg flex items-center justify-center">
                            <span class="text-blue-600 dark:text-blue-300 font-bold">📊</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-neutral-500 dark:text-neutral-400">
                            Categorie Totali
                        </div>
                        <div class="text-2xl font-bold text-neutral-900 dark:text-white">
                            {{ $categories->count() }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 dark:bg-green-800 rounded-lg flex items-center justify-center">
                            <span class="text-green-600 dark:text-green-300 font-bold">✅</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-neutral-500 dark:text-neutral-400">
                            Categorie Attive
                        </div>
                        <div class="text-2xl font-bold text-neutral-900 dark:text-white">
                            {{ $categories->where('active', true)->count() }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 dark:bg-purple-800 rounded-lg flex items-center justify-center">
                            <span class="text-purple-600 dark:text-purple-300 font-bold">📝</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-neutral-500 dark:text-neutral-400">
                            Articoli Totali
                        </div>
                        <div class="text-2xl font-bold text-neutral-900 dark:text-white">
                            {{ $categories->sum('articles_count') ?? 0 }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
