@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-neutral-50 to-neutral-100 dark:from-neutral-900 dark:to-neutral-800">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
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
                        🤖 Generatore Articoli AI
                    </h1>
                </div>
            </div>
            <p class="mt-2 text-neutral-600 dark:text-neutral-300">
                Crea articoli SEO-ottimizzati di 1000+ parole usando l'intelligenza artificiale
            </p>
        </div>

        {{-- Progress Steps --}}
        <div class="mb-8">
            <div class="flex items-center justify-center">
                <ol class="flex items-center w-full max-w-2xl">
                    <li class="flex w-full items-center text-primary-600 dark:text-primary-400 after:content-[''] after:w-full after:h-1 after:border-b after:border-neutral-200 dark:after:border-neutral-600 after:border-4 after:inline-block">
                        <span class="flex items-center justify-center w-10 h-10 bg-primary-100 dark:bg-primary-800 rounded-full lg:h-12 lg:w-12 shrink-0">
                            <span class="text-primary-600 dark:text-primary-300 font-bold">1</span>
                        </span>
                        <span class="ml-2 text-sm font-medium">Setup</span>
                    </li>
                    <li class="flex w-full items-center after:content-[''] after:w-full after:h-1 after:border-b after:border-neutral-200 dark:after:border-neutral-600 after:border-4 after:inline-block">
                        <span class="flex items-center justify-center w-10 h-10 bg-neutral-100 dark:bg-neutral-700 rounded-full lg:h-12 lg:w-12 shrink-0">
                            <span class="text-neutral-500 dark:text-neutral-400 font-bold">2</span>
                        </span>
                        <span class="ml-2 text-sm font-medium text-neutral-500 dark:text-neutral-400">Outline</span>
                    </li>
                    <li class="flex w-full items-center">
                        <span class="flex items-center justify-center w-10 h-10 bg-neutral-100 dark:bg-neutral-700 rounded-full lg:h-12 lg:w-12 shrink-0">
                            <span class="text-neutral-500 dark:text-neutral-400 font-bold">3</span>
                        </span>
                        <span class="ml-2 text-sm font-medium text-neutral-500 dark:text-neutral-400">Articolo</span>
                    </li>
                </ol>
            </div>
        </div>

        {{-- Main Content --}}
        <div x-data="articleGenerator()" x-init="init()" class="space-y-8">
            
            {{-- Step 1: Setup Form --}}
            <div x-show="currentStep === 1" class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-8">
                <h2 class="text-2xl font-bold text-neutral-900 dark:text-white mb-6">
                    📝 Configurazione Articolo
                </h2>
                
                <form @submit.prevent="generateOutline()" class="space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Topic --}}
                        <div class="lg:col-span-2">
                            <label for="topic" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Argomento principale *
                            </label>
                            <input type="text" 
                                   id="topic"
                                   x-model="formData.topic"
                                   required
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white placeholder-neutral-500 dark:placeholder-neutral-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                   placeholder="es: Tecniche di risoluzione Sudoku per principianti">
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                Descrivi l'argomento che vuoi trattare nell'articolo
                            </p>
                        </div>

                        {{-- Keywords --}}
                        <div class="lg:col-span-2">
                            <label for="keywords" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Keywords SEO (massimo 10) *
                            </label>
                            <div class="space-y-2">
                                <template x-for="(keyword, index) in formData.keywords" :key="index">
                                    <div class="flex items-center space-x-2">
                                        <input type="text" 
                                               x-model="formData.keywords[index]"
                                               class="flex-1 px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white placeholder-neutral-500 dark:placeholder-neutral-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                               :placeholder="`Keyword ${index + 1}`">
                                        <button type="button" 
                                                @click="removeKeyword(index)"
                                                x-show="formData.keywords.length > 1"
                                                class="px-3 py-2 text-red-600 hover:text-red-700 transition-colors">
                                            ❌
                                        </button>
                                    </div>
                                </template>
                                <button type="button" 
                                        @click="addKeyword()"
                                        x-show="formData.keywords.length < 10"
                                        class="w-full px-3 py-2 border-2 border-dashed border-neutral-300 dark:border-neutral-600 rounded-lg text-neutral-500 dark:text-neutral-400 hover:border-primary-500 hover:text-primary-600 transition-colors">
                                    ➕ Aggiungi keyword
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                Parole chiave per l'ottimizzazione SEO (es: sudoku, tecniche, principianti)
                            </p>
                        </div>

                        {{-- Target Audience --}}
                        <div>
                            <label for="target_audience" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Pubblico target *
                            </label>
                            <select x-model="formData.target_audience" 
                                    required
                                    class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option value="">Seleziona pubblico</option>
                                <option value="principianti">🟢 Principianti</option>
                                <option value="intermedi">🟡 Intermedi</option>
                                <option value="avanzati">🟠 Avanzati</option>
                                <option value="esperti">🔴 Esperti</option>
                            </select>
                        </div>

                        {{-- Category --}}
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Categoria *
                            </label>
                            <select x-model="formData.category_id" 
                                    required
                                    class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option value="">Seleziona categoria</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->icon }} {{ $category->name_it }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex justify-between items-center pt-6 border-t border-neutral-200 dark:border-neutral-600">
                        <div class="text-sm text-neutral-500 dark:text-neutral-400">
                            ⚡ Genera un outline dettagliato con l'AI
                        </div>
                        <button type="submit" 
                                :disabled="isLoading"
                                class="inline-flex items-center px-6 py-3 bg-primary-600 hover:bg-primary-700 disabled:bg-neutral-400 text-white font-medium rounded-lg transition-colors">
                            <span x-show="!isLoading">🚀 Genera Outline</span>
                            <span x-show="isLoading" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle>
                                    <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" class="opacity-75"></path>
                                </svg>
                                Generando...
                            </span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Step 2: Outline Preview/Edit --}}
            <div x-show="currentStep === 2" class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-8">
                <h2 class="text-2xl font-bold text-neutral-900 dark:text-white mb-6">
                    📋 Outline Generato
                </h2>
                
                <div x-show="outline" class="space-y-6">
                    {{-- Title and Meta --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Titolo SEO (max 60 caratteri)
                            </label>
                            <input type="text" 
                                   x-model="outline.title"
                                   maxlength="60"
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <div class="mt-1 text-xs" :class="outline.title?.length > 60 ? 'text-red-500' : 'text-neutral-500'">
                                <span x-text="outline.title?.length || 0"></span>/60 caratteri
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Meta Description (max 160 caratteri)
                            </label>
                            <textarea x-model="outline.meta_description"
                                      maxlength="160"
                                      rows="3"
                                      class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"></textarea>
                            <div class="mt-1 text-xs" :class="outline.meta_description?.length > 160 ? 'text-red-500' : 'text-neutral-500'">
                                <span x-text="outline.meta_description?.length || 0"></span>/160 caratteri
                            </div>
                        </div>
                    </div>

                    {{-- Sections --}}
                    <div>
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Sezioni Articolo</h3>
                        <div class="space-y-4">
                            <template x-for="(section, index) in outline.sections" :key="index">
                                <div class="border border-neutral-200 dark:border-neutral-600 rounded-lg p-4">
                                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                                        <div class="lg:col-span-2">
                                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                                                Titolo Sezione
                                            </label>
                                            <input type="text" 
                                                   x-model="section.heading"
                                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                                                Parole target
                                            </label>
                                            <input type="number" 
                                                   x-model="section.word_count"
                                                   min="50"
                                                   max="500"
                                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                        </div>
                                        <div class="lg:col-span-3">
                                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                                                Descrizione contenuto
                                            </label>
                                            <textarea x-model="section.description"
                                                      rows="2"
                                                      class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex justify-between items-center pt-6 border-t border-neutral-200 dark:border-neutral-600">
                        <button @click="goToStep(1)" 
                                class="px-4 py-2 text-neutral-600 hover:text-neutral-800 dark:text-neutral-400 dark:hover:text-neutral-200 transition-colors">
                            ← Modifica Setup
                        </button>
                        <div class="flex space-x-3">
                            <button @click="modifyOutline()" 
                                    class="px-6 py-3 bg-neutral-500 hover:bg-neutral-600 text-white font-medium rounded-lg transition-colors">
                                💾 Salva Modifiche
                            </button>
                            <button @click="generateArticle()" 
                                    :disabled="isLoading"
                                    class="px-6 py-3 bg-primary-600 hover:bg-primary-700 disabled:bg-neutral-400 text-white font-medium rounded-lg transition-colors">
                                <span x-show="!isLoading">✨ Genera Articolo</span>
                                <span x-show="isLoading">⏳ Generando...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step 3: Article Preview --}}
            <div x-show="currentStep === 3" class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-8">
                <h2 class="text-2xl font-bold text-neutral-900 dark:text-white mb-6">
                    📄 Articolo Completo
                </h2>
                
                <div x-show="article" class="space-y-6">
                    {{-- Stats --}}
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400" x-text="article?.word_count || 0"></div>
                            <div class="text-sm text-blue-700 dark:text-blue-300">Parole</div>
                        </div>
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-green-600 dark:text-green-400" x-text="article?.seo_data?.seo_score || 'N/A'"></div>
                            <div class="text-sm text-green-700 dark:text-green-300">SEO Score</div>
                        </div>
                        <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400" x-text="article?.sections?.length || 0"></div>
                            <div class="text-sm text-purple-700 dark:text-purple-300">Sezioni</div>
                        </div>
                        <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-orange-600 dark:text-orange-400" x-text="Math.ceil((article?.word_count || 0) / 200)"></div>
                            <div class="text-sm text-orange-700 dark:text-orange-300">Min. lettura</div>
                        </div>
                    </div>

                    {{-- Article Preview --}}
                    <div class="border border-neutral-200 dark:border-neutral-600 rounded-lg p-6 bg-neutral-50 dark:bg-neutral-900 max-h-96 overflow-y-auto">
                        <h3 class="text-xl font-bold text-neutral-900 dark:text-white mb-2" x-text="article?.title"></h3>
                        <p class="text-neutral-600 dark:text-neutral-400 mb-4" x-text="article?.excerpt"></p>
                        <div class="prose dark:prose-invert max-w-none" x-html="article?.content?.substring(0, 500) + '...'"></div>
                    </div>

                    {{-- SEO Data --}}
                    <div x-show="article?.seo_data" class="bg-neutral-50 dark:bg-neutral-900 rounded-lg p-6">
                        <h4 class="font-semibold text-neutral-900 dark:text-white mb-3">📊 Analisi SEO</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-neutral-600 dark:text-neutral-400">Title SEO:</span>
                                <span class="font-medium text-neutral-900 dark:text-white" x-text="article?.meta_title"></span>
                            </div>
                            <div>
                                <span class="text-neutral-600 dark:text-neutral-400">Meta Description:</span>
                                <span class="font-medium text-neutral-900 dark:text-white" x-text="article?.meta_description"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex justify-between items-center pt-6 border-t border-neutral-200 dark:border-neutral-600">
                        <button @click="goToStep(2)" 
                                class="px-4 py-2 text-neutral-600 hover:text-neutral-800 dark:text-neutral-400 dark:hover:text-neutral-200 transition-colors">
                            ← Modifica Outline
                        </button>
                        <div class="flex space-x-3">
                            <button @click="exportArticle()" 
                                    class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                                📝 Usa per Nuovo Articolo
                            </button>
                            <button @click="clearSession()" 
                                    class="px-6 py-3 bg-neutral-500 hover:bg-neutral-600 text-white font-medium rounded-lg transition-colors">
                                🗑️ Ricomincia
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Loading/Error States --}}
            <div x-show="isLoading" class="text-center py-12">
                <svg class="animate-spin h-12 w-12 text-primary-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle>
                    <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" class="opacity-75"></path>
                </svg>
                <p class="text-neutral-600 dark:text-neutral-400" x-text="loadingMessage"></p>
            </div>

            <div x-show="error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 text-red-700 dark:text-red-300">
                <p class="font-medium">❌ Errore</p>
                <p x-text="error"></p>
            </div>
        </div>
    </div>
</div>

<script>
function articleGenerator() {
    return {
        currentStep: 1,
        isLoading: false,
        loadingMessage: '',
        error: null,
        
        formData: {
            topic: '',
            keywords: [''],
            target_audience: '',
            category_id: ''
        },
        
        outline: null,
        article: null,
        
        init() {
            // Check if there's an existing session
            this.checkStatus();
        },
        
        async checkStatus() {
            try {
                const response = await fetch('/admin/articles/generator/status');
                const data = await response.json();
                
                if (data.success && data.status === 'in_progress') {
                    if (data.step === 'outline_generated' || data.step === 'outline_modified') {
                        this.currentStep = 2;
                        await this.loadOutline();
                    } else if (data.step === 'article_generated') {
                        this.currentStep = 3;
                        await this.loadArticle();
                    }
                }
            } catch (error) {
                console.log('No existing session found');
            }
        },
        
        async loadOutline() {
            try {
                const response = await fetch('/admin/articles/generator/outline/preview');
                const data = await response.json();
                
                if (data.success) {
                    this.outline = data.outline;
                    this.formData = data.input_data;
                }
            } catch (error) {
                this.error = 'Errore nel caricamento dell\'outline';
            }
        },
        
        async loadArticle() {
            try {
                const response = await fetch('/admin/articles/generator/article/preview');
                const data = await response.json();
                
                if (data.success) {
                    this.article = data.article;
                    await this.loadOutline(); // Also load outline for navigation
                }
            } catch (error) {
                this.error = 'Errore nel caricamento dell\'articolo';
            }
        },
        
        addKeyword() {
            if (this.formData.keywords.length < 10) {
                this.formData.keywords.push('');
            }
        },
        
        removeKeyword(index) {
            if (this.formData.keywords.length > 1) {
                this.formData.keywords.splice(index, 1);
            }
        },
        
        async generateOutline() {
            if (!this.validateForm()) return;
            
            this.isLoading = true;
            this.loadingMessage = 'Generando outline con AI...';
            this.error = null;
            
            try {
                const response = await fetch('/admin/articles/generator/outline', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.formData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.outline = data.outline;
                    this.currentStep = 2;
                } else {
                    this.error = data.error || 'Errore nella generazione dell\'outline';
                }
            } catch (error) {
                this.error = 'Errore di connessione durante la generazione';
            } finally {
                this.isLoading = false;
            }
        },
        
        async modifyOutline() {
            this.isLoading = true;
            this.loadingMessage = 'Salvando modifiche...';
            
            try {
                const response = await fetch('/admin/articles/generator/outline/modify', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ outline: this.outline })
                });
                
                const data = await response.json();
                
                if (!data.success) {
                    this.error = data.error || 'Errore nel salvataggio delle modifiche';
                }
            } catch (error) {
                this.error = 'Errore di connessione durante il salvataggio';
            } finally {
                this.isLoading = false;
            }
        },
        
        async generateArticle() {
            this.isLoading = true;
            this.loadingMessage = 'Generando articolo completo... (può richiedere alcuni minuti)';
            this.error = null;
            
            try {
                const response = await fetch('/admin/articles/generator/article', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.article = data.article;
                    this.currentStep = 3;
                } else {
                    this.error = data.error || 'Errore nella generazione dell\'articolo';
                }
            } catch (error) {
                this.error = 'Errore di connessione durante la generazione';
            } finally {
                this.isLoading = false;
            }
        },
        
        async exportArticle() {
            try {
                const response = await fetch('/admin/articles/generator/article/export');
                const data = await response.json();
                
                if (data.success) {
                    // Redirect to create article with pre-filled data
                    const params = new URLSearchParams();
                    Object.keys(data.export_data).forEach(key => {
                        if (Array.isArray(data.export_data[key])) {
                            data.export_data[key].forEach(item => params.append(key + '[]', item));
                        } else {
                            params.append(key, data.export_data[key]);
                        }
                    });
                    
                    window.location.href = data.create_url + '?' + params.toString();
                } else {
                    this.error = data.error || 'Errore nell\'esportazione';
                }
            } catch (error) {
                this.error = 'Errore di connessione durante l\'esportazione';
            }
        },
        
        async clearSession() {
            if (!confirm('Sei sicuro di voler ricominciare? Tutti i dati verranno persi.')) return;
            
            try {
                await fetch('/admin/articles/generator/session', { method: 'DELETE' });
                
                // Reset state
                this.currentStep = 1;
                this.formData = {
                    topic: '',
                    keywords: [''],
                    target_audience: '',
                    category_id: ''
                };
                this.outline = null;
                this.article = null;
                this.error = null;
            } catch (error) {
                this.error = 'Errore durante la pulizia della sessione';
            }
        },
        
        goToStep(step) {
            this.currentStep = step;
            this.error = null;
        },
        
        validateForm() {
            if (!this.formData.topic.trim()) {
                this.error = 'L\'argomento è obbligatorio';
                return false;
            }
            
            const keywords = this.formData.keywords.filter(k => k.trim());
            if (keywords.length === 0) {
                this.error = 'Almeno una keyword è obbligatoria';
                return false;
            }
            
            if (!this.formData.target_audience) {
                this.error = 'Il pubblico target è obbligatorio';
                return false;
            }
            
            if (!this.formData.category_id) {
                this.error = 'La categoria è obbligatoria';
                return false;
            }
            
            // Filter empty keywords
            this.formData.keywords = keywords;
            
            return true;
        }
    }
}
</script>
@endsection
