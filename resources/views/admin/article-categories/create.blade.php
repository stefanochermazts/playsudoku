@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-neutral-50 to-neutral-100 dark:from-neutral-900 dark:to-neutral-800">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Header --}}
        <div class="mb-8">
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.article-categories.index') }}" 
                   class="inline-flex items-center text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Torna alle categorie
                </a>
            </div>
            <h1 class="text-3xl font-bold text-neutral-900 dark:text-white mt-4">
                🏷️ Nuova Categoria Articoli
            </h1>
            <p class="mt-2 text-neutral-600 dark:text-neutral-300">
                Crea una nuova categoria per organizzare gli articoli del sistema editoriale
            </p>
        </div>

        {{-- Form --}}
        <form method="POST" action="{{ route('admin.article-categories.store') }}" class="space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Main Content --}}
                <div class="lg:col-span-2 space-y-6">
                    
                    {{-- Basic Info --}}
                    <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                        <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Informazioni Base</h2>
                        
                        {{-- Slug --}}
                        <div class="mb-6">
                            <label for="slug" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Slug (Identificativo URL) *
                            </label>
                            <input type="text" 
                                   id="slug" 
                                   name="slug" 
                                   value="{{ old('slug') }}"
                                   required
                                   pattern="[a-z0-9\-]+"
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white placeholder-neutral-500 dark:placeholder-neutral-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('slug') border-red-300 @enderror"
                                   placeholder="es: tecniche-sudoku">
                            @error('slug')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                Solo lettere minuscole, numeri e trattini. Deve essere unico.
                            </p>
                        </div>

                        {{-- Icon --}}
                        <div class="mb-6">
                            <label for="icon" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Icona Emoji
                            </label>
                            <input type="text" 
                                   id="icon" 
                                   name="icon" 
                                   value="{{ old('icon') }}"
                                   maxlength="2"
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white placeholder-neutral-500 dark:placeholder-neutral-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('icon') border-red-300 @enderror"
                                   placeholder="📰">
                            @error('icon')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                Emoji che rappresenta la categoria (es: 📰, 🧩, 🎯)
                            </p>
                        </div>
                    </div>

                    {{-- Translations --}}
                    <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                        <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Traduzioni</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Italian --}}
                            <div>
                                <label for="name_it" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                    🇮🇹 Nome Italiano *
                                </label>
                                <input type="text" 
                                       id="name_it" 
                                       name="name_it" 
                                       value="{{ old('name_it') }}"
                                       required
                                       class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white placeholder-neutral-500 dark:placeholder-neutral-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('name_it') border-red-300 @enderror"
                                       placeholder="Notizie">
                                @error('name_it')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- English --}}
                            <div>
                                <label for="name_en" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                    🇬🇧 Nome Inglese *
                                </label>
                                <input type="text" 
                                       id="name_en" 
                                       name="name_en" 
                                       value="{{ old('name_en') }}"
                                       required
                                       class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white placeholder-neutral-500 dark:placeholder-neutral-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('name_en') border-red-300 @enderror"
                                       placeholder="News">
                                @error('name_en')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- German --}}
                            <div>
                                <label for="name_de" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                    🇩🇪 Nome Tedesco *
                                </label>
                                <input type="text" 
                                       id="name_de" 
                                       name="name_de" 
                                       value="{{ old('name_de') }}"
                                       required
                                       class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white placeholder-neutral-500 dark:placeholder-neutral-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('name_de') border-red-300 @enderror"
                                       placeholder="Nachrichten">
                                @error('name_de')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Spanish --}}
                            <div>
                                <label for="name_es" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                    🇪🇸 Nome Spagnolo *
                                </label>
                                <input type="text" 
                                       id="name_es" 
                                       name="name_es" 
                                       value="{{ old('name_es') }}"
                                       required
                                       class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white placeholder-neutral-500 dark:placeholder-neutral-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('name_es') border-red-300 @enderror"
                                       placeholder="Noticias">
                                @error('name_es')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    
                    {{-- Settings --}}
                    <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Impostazioni</h3>
                        
                        {{-- Active --}}
                        <div class="mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="active" 
                                       value="1" 
                                       {{ old('active', true) ? 'checked' : '' }}
                                       class="rounded border-neutral-300 dark:border-neutral-600 text-primary-600 shadow-sm focus:ring-primary-500">
                                <span class="ml-2 text-sm text-neutral-700 dark:text-neutral-300">✅ Categoria attiva</span>
                            </label>
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                Solo le categorie attive appaiono nel menu pubblico
                            </p>
                        </div>

                        {{-- Sort Order --}}
                        <div class="mb-6">
                            <label for="sort_order" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Ordine di visualizzazione
                            </label>
                            <input type="number" 
                                   id="sort_order" 
                                   name="sort_order" 
                                   value="{{ old('sort_order', 1) }}"
                                   min="0"
                                   max="100"
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('sort_order') border-red-300 @enderror">
                            @error('sort_order')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                Numero più basso = posizione più alta nel menu
                            </p>
                        </div>
                    </div>

                    {{-- Preview --}}
                    <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">🔍 Anteprima</h3>
                        
                        <div class="space-y-3">
                            <div class="flex items-center p-3 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                                <span id="preview-icon" class="text-xl mr-3">📰</span>
                                <div>
                                    <div id="preview-name" class="font-medium text-neutral-900 dark:text-white">
                                        Nome Categoria
                                    </div>
                                    <div id="preview-slug" class="text-sm text-neutral-500 dark:text-neutral-400">
                                        /categoria-slug
                                    </div>
                                </div>
                            </div>
                            
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                Così apparirà nel menu di navigazione
                            </p>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                        <div class="space-y-3">
                            <button type="submit" 
                                    class="w-full px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg shadow-sm transition-colors">
                                💾 Crea Categoria
                            </button>
                            <a href="{{ route('admin.article-categories.index') }}" 
                               class="block w-full px-4 py-2 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-700 dark:hover:bg-neutral-600 text-neutral-700 dark:text-neutral-300 font-medium rounded-lg text-center transition-colors">
                                ❌ Annulla
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Preview functionality
    const iconInput = document.getElementById('icon');
    const nameItInput = document.getElementById('name_it');
    const slugInput = document.getElementById('slug');
    const previewIcon = document.getElementById('preview-icon');
    const previewName = document.getElementById('preview-name');
    const previewSlug = document.getElementById('preview-slug');

    function updatePreview() {
        previewIcon.textContent = iconInput.value || '📰';
        previewName.textContent = nameItInput.value || 'Nome Categoria';
        previewSlug.textContent = '/' + (slugInput.value || 'categoria-slug');
    }

    // Auto-generate slug from Italian name
    nameItInput.addEventListener('input', function() {
        if (!slugInput.value || slugInput.dataset.autoGenerated !== 'false') {
            const slug = this.value
                .toLowerCase()
                .replace(/[àáâãäå]/g, 'a')
                .replace(/[èéêë]/g, 'e')
                .replace(/[ìíîï]/g, 'i')
                .replace(/[òóôõö]/g, 'o')
                .replace(/[ùúûü]/g, 'u')
                .replace(/[ñ]/g, 'n')
                .replace(/[ç]/g, 'c')
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim('-');
                
            slugInput.value = slug;
            slugInput.dataset.autoGenerated = 'true';
        }
        updatePreview();
    });

    slugInput.addEventListener('input', function() {
        this.dataset.autoGenerated = 'false';
        updatePreview();
    });

    iconInput.addEventListener('input', updatePreview);

    // Initial preview
    updatePreview();
});
</script>
@endsection
