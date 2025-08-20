@extends('layouts.app')

@section('head')
<!-- Trix Editor CSS -->
<link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
<style>
/* Custom Trix styling for PlaySudoku */
trix-editor {
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    padding: 0.75rem;
    min-height: 20rem;
    background-color: white;
    color: #111827;
}

.dark trix-editor {
    border-color: #4b5563;
    background-color: #374151;
    color: white;
}

trix-editor:focus {
    outline: none;
    ring: 2px;
    ring-color: #3b82f6;
    border-color: transparent;
}

trix-toolbar .trix-button-group {
    border-color: #d1d5db;
}

.dark trix-toolbar .trix-button-group {
    border-color: #4b5563;
}
</style>
@endsection

@section('content')
<div class="min-h-screen bg-gradient-to-br from-neutral-50 to-neutral-100 dark:from-neutral-900 dark:to-neutral-800">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Header --}}
        <div class="mb-8">
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.articles.index') }}" 
                   class="inline-flex items-center text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Torna agli articoli
                </a>
            </div>
            <h1 class="text-3xl font-bold text-neutral-900 dark:text-white mt-4">
                ✏️ {{ __('app.editorial.create_article') }}
            </h1>
            <p class="mt-2 text-neutral-600 dark:text-neutral-300">
                Scrivi un nuovo articolo in italiano. Le traduzioni automatiche saranno generate automaticamente.
            </p>
            
            {{-- AI Generator Banner --}}
            <div class="mt-4 bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 border border-emerald-200 dark:border-emerald-700 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-emerald-500 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-emerald-900 dark:text-emerald-100">
                                🤖 Prova il Generatore AI
                            </h3>
                            <p class="text-xs text-emerald-700 dark:text-emerald-300">
                                Crea articoli SEO di 1000+ parole in pochi minuti con l'intelligenza artificiale
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('admin.articles.generator.index') }}" 
                       class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors">
                        ✨ Usa AI
                    </a>
                </div>
            </div>
        </div>

        {{-- Form --}}
        <form method="POST" action="{{ route('admin.articles.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Main Content --}}
                <div class="lg:col-span-2 space-y-6">
                    
                    {{-- Title & Excerpt --}}
                    <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                        <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Contenuto Principale</h2>
                        
                        {{-- Title --}}
                        <div class="mb-4">
                            <label for="title" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                {{ __('app.editorial.title') }} *
                            </label>
                            <input type="text" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title') }}"
                                   required
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white placeholder-neutral-500 dark:placeholder-neutral-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('title') border-red-300 @enderror"
                                   placeholder="Inserisci il titolo dell'articolo...">
                            @error('title')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Excerpt --}}
                        <div class="mb-4">
                            <label for="excerpt" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                {{ __('app.editorial.excerpt') }} *
                            </label>
                            <textarea id="excerpt" 
                                      name="excerpt" 
                                      rows="3"
                                      required
                                      class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white placeholder-neutral-500 dark:placeholder-neutral-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('excerpt') border-red-300 @enderror"
                                      placeholder="Breve descrizione dell'articolo (max 300 caratteri)...">{{ old('excerpt') }}</textarea>
                            @error('excerpt')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                Questo testo apparirà nelle anteprime e nei risultati di ricerca
                            </p>
                        </div>
                    </div>

                    {{-- Content Editor --}}
                    <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                        <label for="content" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-4">
                            {{ __('app.editorial.content') }} *
                        </label>
                        
                        {{-- Hidden input for form submission --}}
                        <input id="content" name="content" type="hidden" value="{{ old('content') }}">
                        
                        {{-- Trix Editor --}}
                        <trix-editor input="content" 
                                     placeholder="Scrivi il contenuto dell'articolo utilizzando l'editor visuale..."
                                     class="@error('content') border-red-300 @enderror"></trix-editor>
                        
                        @error('content')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        
                        <div class="flex items-center justify-between mt-3">
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                                ✨ <strong>Editor visuale</strong> con supporto per grassetto, corsivo, liste, link e immagini
                            </p>
                            <div class="flex items-center space-x-2 text-xs text-neutral-400">
                                <span>📷 Immagini</span>
                                <span>🔗 Link</span> 
                                <span>📝 Liste</span>
                                <span>✨ Formattazione</span>
                            </div>
                        </div>
                    </div>

                    {{-- SEO Section --}}
                    <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                        <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">SEO & Metadati</h2>
                        
                        {{-- Meta Title --}}
                        <div class="mb-4">
                            <label for="meta_title" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                {{ __('app.editorial.meta_title') }}
                            </label>
                            <input type="text" 
                                   id="meta_title" 
                                   name="meta_title" 
                                   value="{{ old('meta_title') }}"
                                   maxlength="60"
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white placeholder-neutral-500 dark:placeholder-neutral-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                   placeholder="Titolo SEO (max 60 caratteri)">
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                Se vuoto, verrà usato il titolo principale
                            </p>
                        </div>

                        {{-- Meta Description --}}
                        <div class="mb-4">
                            <label for="meta_description" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                {{ __('app.editorial.meta_description') }}
                            </label>
                            <textarea id="meta_description" 
                                      name="meta_description" 
                                      rows="2"
                                      maxlength="160"
                                      class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white placeholder-neutral-500 dark:placeholder-neutral-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                      placeholder="Descrizione per i motori di ricerca (max 160 caratteri)">{{ old('meta_description') }}</textarea>
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                Se vuoto, verrà usato l'estratto
                            </p>
                        </div>

                        {{-- Tags --}}
                        <div>
                            <label for="tags" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                {{ __('app.editorial.tags') }}
                            </label>
                            <input type="text" 
                                   id="tags" 
                                   name="tags" 
                                   value="{{ old('tags') }}"
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white placeholder-neutral-500 dark:placeholder-neutral-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                   placeholder="sudoku, tecnica, principianti">
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                Separati da virgole (es: sudoku, tecnica, principianti)
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    
                    {{-- Publish Settings --}}
                    <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Pubblicazione</h3>
                        
                        {{-- Status --}}
                        <div class="mb-4">
                            <label for="status" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                {{ __('app.editorial.status') }}
                            </label>
                            <select name="status" id="status" class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>📝 {{ __('app.editorial.draft') }}</option>
                                <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>✅ {{ __('app.editorial.published') }}</option>
                                <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>📦 {{ __('app.editorial.archived') }}</option>
                            </select>
                        </div>

                        {{-- Category --}}
                        <div class="mb-4">
                            <label for="category_id" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Categoria *
                            </label>
                            <select name="category_id" id="category_id" required class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('category_id') border-red-300 @enderror">
                                <option value="">Seleziona categoria</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->icon }} {{ $category->localized_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Featured --}}
                        <div class="mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="featured" 
                                       value="1" 
                                       {{ old('featured') ? 'checked' : '' }}
                                       class="rounded border-neutral-300 dark:border-neutral-600 text-primary-600 shadow-sm focus:ring-primary-500">
                                <span class="ml-2 text-sm text-neutral-700 dark:text-neutral-300">⭐ {{ __('app.editorial.featured') }}</span>
                            </label>
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                Gli articoli in evidenza vengono mostrati per primi
                            </p>
                        </div>

                        {{-- Auto Translate --}}
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="auto_translate" 
                                       value="1" 
                                       {{ old('auto_translate', true) ? 'checked' : '' }}
                                       class="rounded border-neutral-300 dark:border-neutral-600 text-primary-600 shadow-sm focus:ring-primary-500">
                                <span class="ml-2 text-sm text-neutral-700 dark:text-neutral-300">🤖 {{ __('app.editorial.auto_translate') }}</span>
                            </label>
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                Genera automaticamente le traduzioni con OpenAI
                            </p>
                        </div>
                    </div>

                    {{-- Featured Image --}}
                    <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">{{ __('app.editorial.featured_image') }}</h3>
                        
                        <div class="mb-4">
                            <input type="file" 
                                   id="featured_image" 
                                   name="featured_image" 
                                   accept="image/*"
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('featured_image') border-red-300 @enderror">
                            @error('featured_image')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                JPG, PNG, WebP max 2MB
                            </p>
                        </div>

                        <div id="image-preview" class="hidden">
                            <img id="preview-img" src="" alt="Preview" class="w-full h-32 object-cover rounded-lg">
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                        <div class="space-y-3">
                            <button type="submit" 
                                    class="w-full px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg shadow-sm transition-colors">
                                💾 Salva Articolo
                            </button>
                            <a href="{{ route('admin.articles.index') }}" 
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

{{-- Trix Editor JavaScript --}}
<script type="text/javascript" src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configure Trix editor
    const editor = document.querySelector('trix-editor');
    
    if (editor) {
        // Custom configuration for Trix
        editor.addEventListener('trix-initialize', function() {
            console.log('🎨 Trix Editor inizializzato per creazione articolo');
        });
        
        // Handle file uploads (images)
        editor.addEventListener('trix-file-accept', function(event) {
            const acceptedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!acceptedTypes.includes(event.file.type)) {
                event.preventDefault();
                alert('⚠️ Sono supportati solo file immagine (JPEG, PNG, GIF, WebP)');
            }
            
            // Limit file size to 2MB
            if (event.file.size > 2 * 1024 * 1024) {
                event.preventDefault(); 
                alert('⚠️ Il file è troppo grande. Dimensione massima: 2MB');
            }
        });
        
        // Dark mode adaptation
        function updateEditorTheme() {
            const isDark = document.documentElement.classList.contains('dark');
            if (isDark) {
                editor.classList.add('dark-theme');
            } else {
                editor.classList.remove('dark-theme');
            }
        }
        
        // Initialize theme
        updateEditorTheme();
        
        // Watch for theme changes
        const observer = new MutationObserver(updateEditorTheme);
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class']
        });
    }

    // Populate form from URL parameters (AI Generator export)
    function populateFromUrlParams() {
        const urlParams = new URLSearchParams(window.location.search);
        
        // Title
        if (urlParams.has('title')) {
            document.getElementById('title').value = urlParams.get('title');
        }
        
        // Excerpt
        if (urlParams.has('excerpt')) {
            document.getElementById('excerpt').value = urlParams.get('excerpt');
        }
        
        // Content (for Trix editor)
        if (urlParams.has('content')) {
            const contentInput = document.getElementById('content');
            const editor = document.querySelector('trix-editor');
            
            if (contentInput && editor) {
                contentInput.value = urlParams.get('content');
                editor.editor.loadHTML(urlParams.get('content'));
            }
        }
        
        // Meta title
        if (urlParams.has('meta_title')) {
            document.getElementById('meta_title').value = urlParams.get('meta_title');
        }
        
        // Meta description
        if (urlParams.has('meta_description')) {
            document.getElementById('meta_description').value = urlParams.get('meta_description');
        }
        
        // Tags (convert array back to comma-separated string)
        const tags = urlParams.getAll('tags');
        if (tags.length > 0) {
            document.getElementById('tags').value = tags.join(', ');
        }
        
        // Category ID
        if (urlParams.has('category_id')) {
            document.getElementById('category_id').value = urlParams.get('category_id');
        }
        
        // Status
        if (urlParams.has('status')) {
            document.getElementById('status').value = urlParams.get('status');
        }
        
        // Auto translate (default to true for AI-generated content)
        if (urlParams.has('auto_translate')) {
            document.querySelector('input[name="auto_translate"]').checked = urlParams.get('auto_translate') === '1';
        } else if (urlParams.has('title')) {
            // If title exists (indicating AI import), enable auto-translate by default
            document.querySelector('input[name="auto_translate"]').checked = true;
        }
        
        // Show success message if coming from AI generator
        if (urlParams.has('title') && urlParams.has('content')) {
            const banner = document.createElement('div');
            banner.className = 'mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-xl p-4';
            banner.innerHTML = `
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <p class="text-green-800 dark:text-green-200 text-sm">
                        🤖 <strong>Articolo importato dal Generatore AI!</strong> I campi sono stati popolati automaticamente. Modifica e salva quando sei soddisfatto.
                    </p>
                </div>
            `;
            
            const form = document.querySelector('form');
            if (form) {
                form.parentNode.insertBefore(banner, form);
            }
        }
        
        // Clean URL after population
        if (urlParams.has('title')) {
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    }

    // Image preview
    const imageInput = document.getElementById('featured_image');
    const preview = document.getElementById('image-preview');
    const previewImg = document.getElementById('preview-img');

    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        } else {
            preview.classList.add('hidden');
        }
    });
    
    // Call population function when page loads
    populateFromUrlParams();

    // Convert tags to array format
    const tagsInput = document.getElementById('tags');
    const form = tagsInput.closest('form');
    
    form.addEventListener('submit', function(e) {
        if (tagsInput.value) {
            const tags = tagsInput.value.split(',').map(tag => tag.trim()).filter(tag => tag);
            
            // Remove original input
            tagsInput.remove();
            
            // Add hidden inputs for each tag
            tags.forEach(tag => {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'tags[]';
                hiddenInput.value = tag;
                form.appendChild(hiddenInput);
            });
        }
    });
});
</script>
@endsection
