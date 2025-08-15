<x-site-layout class="overflow-x-hidden">
    {{-- Hero Section con statistiche --}}
    <div class="bg-gradient-to-br from-primary-50 to-primary-100 dark:from-neutral-900 dark:to-neutral-800 border-b border-primary-200 dark:border-neutral-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl sm:text-4xl font-bold text-primary-900 dark:text-white mb-3">
                    {{ __('app.clubs.explore_clubs') }}
                </h1>
                <p class="text-lg text-primary-700 dark:text-neutral-300 max-w-2xl mx-auto">
                    {{ __('app.clubs.explore_subtitle') }}
                </p>
            </div>
            
            {{-- Statistiche rapide --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-neutral-800 rounded-lg p-4 text-center shadow-sm">
                    <div class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ $clubs->total() }}</div>
                    <div class="text-sm text-neutral-600 dark:text-neutral-400">{{ __('app.clubs.total_clubs') }}</div>
                </div>
                <div class="bg-white dark:bg-neutral-800 rounded-lg p-4 text-center shadow-sm">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $clubs->where('activeMembers_count', '>', 0)->count() }}</div>
                    <div class="text-sm text-neutral-600 dark:text-neutral-400">{{ __('app.clubs.active_clubs') }}</div>
                </div>
                <div class="bg-white dark:bg-neutral-800 rounded-lg p-4 text-center shadow-sm">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $clubs->sum('activeMembers_count') ?? 0 }}</div>
                    <div class="text-sm text-neutral-600 dark:text-neutral-400">{{ __('app.clubs.total_members') }}</div>
                </div>
                <div class="bg-white dark:bg-neutral-800 rounded-lg p-4 text-center shadow-sm">
                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $clubs->where('created_at', '>=', now()->subWeek())->count() }}</div>
                    <div class="text-sm text-neutral-600 dark:text-neutral-400">{{ __('app.clubs.new_this_week') }}</div>
                </div>
            </div>
            
            <div class="text-center">
                <a href="{{ route('localized.clubs.index', ['locale' => app()->getLocale()]) }}" 
                   class="inline-flex items-center px-4 py-2 border border-primary-300 dark:border-neutral-600 rounded-lg text-primary-700 dark:text-neutral-300 hover:bg-white dark:hover:bg-neutral-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    {{ __('app.clubs.back_to_clubs') }}
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Filtri e ricerca avanzati --}}
        <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-6 mb-8">
            <form method="GET" action="{{ route('localized.clubs.explore', ['locale' => app()->getLocale()]) }}" class="space-y-4">
                {{-- Riga principale: ricerca e ordinamento --}}
                <div class="flex flex-col lg:flex-row gap-4">
                    {{-- Barra di ricerca --}}
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            {{ __('app.clubs.search_label') }}
                        </label>
                        <div class="relative">
                            <input type="text" 
                                   name="search" 
                                   value="{{ $search }}"
                                   placeholder="{{ __('app.clubs.search_clubs_placeholder') }}"
                                   class="w-full pl-10 pr-4 py-3 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-neutral-700 dark:text-white">
                            <svg class="absolute left-3 top-3.5 h-5 w-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                    
                    {{-- Ordinamento --}}
                    <div class="lg:w-48">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            {{ __('app.clubs.sort_by') }}
                        </label>
                        <select name="sort" 
                                class="w-full py-3 px-4 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-neutral-700 dark:text-white">
                            <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>{{ __('app.clubs.sort_newest') }}</option>
                            <option value="oldest" {{ $sort === 'oldest' ? 'selected' : '' }}>{{ __('app.clubs.sort_oldest') }}</option>
                            <option value="members" {{ $sort === 'members' ? 'selected' : '' }}>{{ __('app.clubs.sort_members') }}</option>
                            <option value="name" {{ $sort === 'name' ? 'selected' : '' }}>{{ __('app.clubs.sort_name') }}</option>
                        </select>
                    </div>
                    
                    {{-- Pulsante ricerca --}}
                    <div class="lg:w-auto flex items-end">
                        <button type="submit" 
                                class="w-full lg:w-auto px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors flex items-center justify-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <span>{{ __('app.clubs.search') }}</span>
                        </button>
                    </div>
                </div>
                
                @if($search)
                    <div class="flex items-center justify-between bg-primary-50 dark:bg-primary-900/20 rounded-lg p-3">
                        <span class="text-sm text-primary-700 dark:text-primary-300">
                            {{ __('app.clubs.searching_for', ['term' => $search]) }}
                        </span>
                        <a href="{{ route('localized.clubs.explore', ['locale' => app()->getLocale()]) }}"
                           class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 font-medium">
                            {{ __('app.clubs.clear_search') }}
                        </a>
                    </div>
                @endif
            </form>
        </div>

        {{-- Risultati --}}
        @if($clubs->count() > 0)
            {{-- Header risultati --}}
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-semibold text-neutral-900 dark:text-white">
                        {{ __('app.clubs.found_clubs', ['count' => $clubs->total()]) }}
                    </h2>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">
                        {{ __('app.clubs.showing_results', ['from' => $clubs->firstItem(), 'to' => $clubs->lastItem(), 'total' => $clubs->total()]) }}
                    </p>
                </div>
            </div>
            
            {{-- Grid club migliorata --}}
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
                @foreach($clubs as $club)
                <div class="group bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700 hover:shadow-lg hover:border-primary-300 dark:hover:border-primary-600 transition-all duration-200 overflow-hidden">
                    {{-- Header card con badge attività --}}
                    <div class="relative p-5 pb-4">
                        {{-- Badge attività --}}
                        <div class="absolute top-3 right-3 flex space-x-1">
                            @if($club->created_at >= now()->subWeek())
                                <span class="px-2 py-1 text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200 rounded-full">
                                    {{ __('app.clubs.new') }}
                                </span>
                            @endif
                            @if($club->activeMembers_count >= 10)
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded-full">
                                    {{ __('app.clubs.popular') }}
                                </span>
                            @endif
                        </div>
                        
                        {{-- Nome e proprietario --}}
                        <div class="pr-16">
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-1 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                                {{ $club->name }}
                            </h3>
                            <div class="flex items-center space-x-2 text-sm text-neutral-500 dark:text-neutral-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span>{{ $club->owner->name }}</span>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Descrizione --}}
                    @if($club->description)
                        <div class="px-5 pb-4">
                            <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                {{ $club->description }}
                            </p>
                        </div>
                    @endif
                    
                    {{-- Statistiche visuali --}}
                    <div class="px-5 pb-4">
                        <div class="flex items-center justify-between text-sm">
                            {{-- Membri con barra progresso --}}
                            <div class="flex-1 mr-4">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-neutral-600 dark:text-neutral-400">{{ __('app.clubs.members') }}</span>
                                    <span class="font-medium text-neutral-900 dark:text-white">
                                        {{ $club->activeMembers_count }}@if($club->max_members)/{{ $club->max_members }}@endif
                                    </span>
                                </div>
                                @if($club->max_members)
                                    <div class="w-full bg-neutral-200 dark:bg-neutral-700 rounded-full h-2">
                                        <div class="bg-primary-600 h-2 rounded-full transition-all" 
                                             style="width: {{ min(($club->activeMembers_count / $club->max_members) * 100, 100) }}%"></div>
                                    </div>
                                @else
                                    <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ __('app.clubs.unlimited') }}</div>
                                @endif
                            </div>
                            
                            {{-- Data creazione --}}
                            <div class="text-right">
                                <div class="text-neutral-600 dark:text-neutral-400">{{ __('app.clubs.created') }}</div>
                                <div class="font-medium text-neutral-900 dark:text-white">{{ $club->created_at->format('M Y') }}</div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Azioni --}}
                    <div class="border-t border-neutral-200 dark:border-neutral-700 p-4 bg-neutral-50 dark:bg-neutral-700/50">
                        <div class="flex space-x-2">
                            <a href="{{ route('localized.clubs.show', ['locale' => app()->getLocale(), 'club' => $club->slug]) }}"
                               class="flex-1 px-3 py-2.5 text-sm text-center border border-neutral-300 dark:border-neutral-600 rounded-lg hover:bg-white dark:hover:bg-neutral-600 transition-colors flex items-center justify-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <span>{{ __('app.clubs.view') }}</span>
                            </a>
                            @if($club->canJoin)
                                <form method="POST" action="{{ route('localized.clubs.join', ['locale' => app()->getLocale(), 'club' => $club->slug]) }}" class="flex-1">
                                    @csrf
                                    <button type="submit"
                                            class="w-full px-3 py-2.5 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center justify-center space-x-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        <span>{{ __('app.clubs.join') }}</span>
                                    </button>
                                </form>
                            @else
                                <div class="flex-1">
                                    <span class="flex w-full px-3 py-2.5 text-sm text-center bg-neutral-200 text-neutral-500 dark:bg-neutral-700 dark:text-neutral-400 rounded-lg items-center justify-center space-x-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path>
                                        </svg>
                                        <span>{{ __('app.clubs.full') }}</span>
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Paginazione --}}
            <div class="mt-8">
                {{ $clubs->links() }}
            </div>
        @else
            {{-- Stato vuoto --}}
            <div class="text-center py-12">
                <svg class="mx-auto h-16 w-16 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M34 40h10v-4a6 6 0 00-10.712-3.714M34 40H14m20 0v-4a9.971 9.971 0 00-.712-3.714M14 40H4v-4a6 6 0 0110.712-3.714M14 40v-4a9.971 9.971 0 01.712-3.714M18 20a4 4 0 11-8 0 4 4 0 018 0zm16 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-neutral-900 dark:text-neutral-100">
                    @if($search)
                        {{ __('app.clubs.no_results') }}
                    @else
                        {{ __('app.clubs.no_public_clubs') }}
                    @endif
                </h3>
                <p class="mt-2 text-neutral-500 dark:text-neutral-400">
                    @if($search)
                        {{ __('app.clubs.no_results_description', ['search' => $search]) }}
                    @else
                        {{ __('app.clubs.no_public_clubs_description') }}
                    @endif
                </p>
                @if($search)
                    <div class="mt-4">
                        <a href="{{ route('localized.clubs.explore', ['locale' => app()->getLocale()]) }}"
                           class="text-primary-600 hover:text-primary-700 font-medium">
                            {{ __('app.clubs.clear_search') }}
                        </a>
                    </div>
                @else
                    <div class="mt-4">
                        <a href="{{ route('localized.clubs.create', ['locale' => app()->getLocale()]) }}"
                           class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                            {{ __('app.clubs.create_first_club') }}
                        </a>
                    </div>
                @endif
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        // Auto-submit del form quando cambia l'ordinamento
        document.querySelector('select[name="sort"]').addEventListener('change', function() {
            this.form.submit();
        });
    </script>
    @endpush
</x-site-layout>
