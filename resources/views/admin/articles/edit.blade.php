<x-site-layout>

    @push('styles')
<!-- Trix Editor CSS -->
<link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
<style>
/* Custom Trix styling for PlaySudoku */
trix-editor {
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
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
@endpush

    <div class="min-h-screen bg-gradient-to-br from-primary-50 via-neutral-50 to-secondary-50 dark:from-neutral-900 dark:via-neutral-950 dark:to-neutral-900">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
        
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
                    <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">
                        Modifica Articolo
                    </h1>
                </div>
                
                <div class="flex items-center space-x-3">
                    @if($article->status === 'published')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                            📤 Pubblicato
                        </span>
                    @elseif($article->status === 'draft')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100">
                            📝 Bozza
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Main Form --}}
        <form action="{{ route('admin.articles.update', $article) }}" method="POST" enctype="multipart/form-data" 
              x-data="{ 
                  autoTranslate: {{ old('auto_translate', '1') }}, 
                  status: '{{ old('status', $article->status) }}' 
              }">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                {{-- Main Content --}}
                <div class="lg:col-span-2 space-y-6">
                    
                    {{-- Basic Info --}}
                    <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                        
                        {{-- Title --}}
                        <div class="mb-6">
                            <label for="title" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Titolo (Italiano) *
                            </label>
                            <input type="text" 
                                   id="title"
                                   name="title" 
                                   value="{{ old('title', $italianTranslation?->title) }}"
                                   required
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-neutral-700 dark:text-white @error('title') border-red-300 @enderror"
                                   placeholder="Inserisci il titolo dell'articolo">
                            @error('title')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Excerpt --}}
                        <div class="mb-6">
                            <label for="excerpt" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Estratto (Italiano)
                            </label>
                            <textarea id="excerpt" 
                                      name="excerpt" 
                                      rows="3"
                                      class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-neutral-700 dark:text-white @error('excerpt') border-red-300 @enderror"
                                      placeholder="Breve descrizione dell'articolo...">{{ old('excerpt', $italianTranslation?->excerpt) }}</textarea>
                            @error('excerpt')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    
                    {{-- Content Editor --}}
                    <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                        <label for="content" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-4">
                            Contenuto (Italiano) *
                        </label>
                        
                        {{-- Hidden input for form submission --}}
                        <input id="content" name="content" type="hidden" value="{{ old('content', $italianTranslation?->content) }}">
                        
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
                </div>
                
                {{-- Sidebar --}}
                <div class="space-y-6">
                    
                    {{-- Publish Settings --}}
                    <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Pubblicazione</h3>
                        
                        {{-- Status --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Stato
                            </label>
                            <select name="status" x-model="status" 
                                    class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-neutral-700 dark:text-white">
                                <option value="draft">📝 Bozza</option>
                                <option value="published">📤 Pubblicato</option>
                                <option value="archived">📦 Archiviato</option>
                            </select>
                        </div>

                        {{-- Category --}}
                        <div class="mb-4">
                            <label for="category_id" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Categoria *
                            </label>
                            <select id="category_id" 
                                    name="category_id" 
                                    required
                                    class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-neutral-700 dark:text-white @error('category_id') border-red-300 @enderror">
                                <option value="">Seleziona categoria</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" 
                                            {{ old('category_id', $article->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->icon }} {{ $category->name_it }}
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
                                       {{ old('featured', $article->featured) ? 'checked' : '' }}
                                       class="rounded border-neutral-300 text-primary-600 shadow-sm focus:ring-primary-500">
                                <span class="ml-2 text-sm text-neutral-700 dark:text-neutral-300">⭐ In evidenza</span>
                            </label>
                        </div>

                        {{-- Auto Translate --}}
                        <div class="mb-6">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="auto_translate" 
                                       value="1"
                                       x-model="autoTranslate"
                                       {{ old('auto_translate', '1') ? 'checked' : '' }}
                                       class="rounded border-neutral-300 text-primary-600 shadow-sm focus:ring-primary-500">
                                <span class="ml-2 text-sm text-neutral-700 dark:text-neutral-300">🌍 Traduci automaticamente</span>
                            </label>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                Se abilitato, l'articolo sarà tradotto automaticamente in inglese, tedesco e spagnolo usando OpenAI.
                            </p>
                        </div>
                    </div>

                    {{-- Image Upload --}}
                    <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Immagine in evidenza</h3>
                        
                        @if($article->featured_image)
                            <div class="mb-4">
                                <img src="{{ Storage::disk('public')->url($article->featured_image) }}" 
                                     alt="Immagine attuale" 
                                     class="w-full h-32 object-cover rounded-lg">
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Immagine attuale</p>
                            </div>
                        @endif
                        
                        <input type="file" 
                               name="featured_image" 
                               accept="image/*"
                               class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-neutral-700 dark:text-white @error('featured_image') border-red-300 @enderror">
                        @error('featured_image')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                            JPG, PNG o WebP. Max 2MB.
                        </p>
                    </div>

                    {{-- Actions --}}
                    <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                        <div class="space-y-3">
                            <button type="submit" 
                                    class="w-full bg-primary-600 hover:bg-primary-700 text-white font-medium py-2 px-4 rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-800">
                                💾 Aggiorna Articolo
                            </button>
                            
                            <a href="{{ route('admin.articles.index') }}" 
                               class="w-full bg-neutral-500 hover:bg-neutral-600 text-white font-medium py-2 px-4 rounded-md transition-colors text-center block">
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
            console.log('🎨 Trix Editor inizializzato');
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
        
        // Auto-save content on change (optional)
        let saveTimeout;
        editor.addEventListener('trix-change', function() {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(() => {
                console.log('💾 Contenuto auto-salvato localmente');
                // Potresti implementare auto-save qui se necessario
            }, 2000);
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
});

// Prevent default file handling and implement custom upload
document.addEventListener('trix-attachment-add', function(event) {
    if (event.attachment.file) {
        // You can implement custom file upload logic here
        // For now, we'll use base64 embedding (not recommended for production)
        const reader = new FileReader();
        reader.onload = function(e) {
            event.attachment.setAttributes({
                url: e.target.result,
                href: e.target.result
            });
        };
        reader.readAsDataURL(event.attachment.file);
    }
});
</script>
        </div>
    </div>
</x-site-layout>