<x-site-layout class="overflow-x-hidden">
    {{-- Header della pagina --}}
    <div class="bg-white dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white">
                        {{ __('app.clubs.edit_title') }}
                    </h1>
                    <p class="mt-2 text-neutral-600 dark:text-neutral-400">
                        {{ __('app.clubs.edit_subtitle', ['name' => $club->name]) }}
                    </p>
                </div>
                <a href="{{ route('localized.clubs.show', ['locale' => app()->getLocale(), 'club' => $club->slug]) }}" 
                   class="px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-colors">
                    {{ __('app.clubs.back_to_club') }}
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if ($errors->any())
            <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                            {{ __('app.clubs.validation_errors') }}
                        </h3>
                        <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('localized.clubs.update', ['locale' => app()->getLocale(), 'club' => $club->slug]) }}" class="space-y-8">
            @csrf
            @method('PUT')

            {{-- Informazioni di base --}}
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-6">
                    {{ __('app.clubs.basic_info') }}
                </h2>
                
                <div class="grid grid-cols-1 gap-6">
                    {{-- Nome club --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            {{ __('app.clubs.name') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $club->name) }}"
                               required
                               maxlength="100"
                               class="w-full rounded-md border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 {{ $errors->has('name') ? 'border-red-500' : '' }}"
                               placeholder="{{ __('app.clubs.name_placeholder') }}">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Descrizione --}}
                    <div>
                        <label for="description" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            {{ __('app.clubs.description') }}
                        </label>
                        <textarea id="description" 
                                  name="description" 
                                  rows="4"
                                  maxlength="1000"
                                  class="w-full rounded-md border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 {{ $errors->has('description') ? 'border-red-500' : '' }}"
                                  placeholder="{{ __('app.clubs.description_placeholder') }}">{{ old('description', $club->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-neutral-500">{{ __('app.clubs.description_help') }}</p>
                    </div>
                </div>
            </div>

            {{-- Impostazioni --}}
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-6">
                    {{ __('app.clubs.settings') }}
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Visibilità --}}
                    <div>
                        <label for="visibility" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            {{ __('app.clubs.visibility') }} <span class="text-red-500">*</span>
                        </label>
                        <select id="visibility" 
                                name="visibility" 
                                required
                                class="w-full rounded-md border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 {{ $errors->has('visibility') ? 'border-red-500' : '' }}">
                            <option value="">{{ __('app.clubs.select_visibility') }}</option>
                            <option value="public" {{ old('visibility', $club->visibility) === 'public' ? 'selected' : '' }}>
                                {{ __('app.clubs.visibility_public') }}
                            </option>
                            <option value="private" {{ old('visibility', $club->visibility) === 'private' ? 'selected' : '' }}>
                                {{ __('app.clubs.visibility_private') }}
                            </option>
                            <option value="invite_only" {{ old('visibility', $club->visibility) === 'invite_only' ? 'selected' : '' }}>
                                {{ __('app.clubs.visibility_invite_only') }}
                            </option>
                        </select>
                        @error('visibility')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        
                        {{-- Descrizioni visibilità --}}
                        <div class="mt-2 text-sm text-neutral-500 space-y-1">
                            <div id="visibility-public-desc" class="hidden">
                                <strong>{{ __('app.clubs.visibility_public') }}:</strong> {{ __('app.clubs.visibility_public_desc') }}
                            </div>
                            <div id="visibility-private-desc" class="hidden">
                                <strong>{{ __('app.clubs.visibility_private') }}:</strong> {{ __('app.clubs.visibility_private_desc') }}
                            </div>
                            <div id="visibility-invite-desc" class="hidden">
                                <strong>{{ __('app.clubs.visibility_invite_only') }}:</strong> {{ __('app.clubs.visibility_invite_desc') }}
                            </div>
                        </div>
                    </div>

                    {{-- Numero massimo membri --}}
                    <div>
                        <label for="max_members" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            {{ __('app.clubs.max_members') }}
                        </label>
                        <input type="number" 
                               id="max_members" 
                               name="max_members" 
                               value="{{ old('max_members', $club->max_members) }}"
                               min="2"
                               max="200"
                               class="w-full rounded-md border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 {{ $errors->has('max_members') ? 'border-red-500' : '' }}">
                        @error('max_members')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-neutral-500">{{ __('app.clubs.max_members_help') }}</p>
                    </div>
                </div>
            </div>

            {{-- Zona Pericolo --}}
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6">
                <h2 class="text-lg font-semibold text-red-900 dark:text-red-100 mb-4">
                    {{ __('app.clubs.danger_zone') }}
                </h2>
                <p class="text-sm text-red-700 dark:text-red-300 mb-4">
                    {{ __('app.clubs.delete_warning') }}
                </p>
                <button type="button" 
                        onclick="confirmDelete()"
                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                    {{ __('app.clubs.delete_club') }}
                </button>
            </div>

            {{-- Bottoni --}}
            <div class="flex flex-col sm:flex-row justify-end gap-4 pt-6">
                <a href="{{ route('localized.clubs.show', ['locale' => app()->getLocale(), 'club' => $club->slug]) }}" 
                   class="px-6 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-colors text-center">
                    {{ __('app.clubs.cancel') }}
                </a>
                <button type="submit" 
                        class="px-8 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900 transition-colors">
                    {{ __('app.clubs.save_changes') }}
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        // Mostra/nascondi descrizioni visibilità
        document.addEventListener('DOMContentLoaded', function() {
            const visibilitySelect = document.getElementById('visibility');
            const descriptions = {
                'public': document.getElementById('visibility-public-desc'),
                'private': document.getElementById('visibility-private-desc'),
                'invite_only': document.getElementById('visibility-invite-desc')
            };

            function updateVisibilityDescription() {
                // Nascondi tutte le descrizioni
                Object.values(descriptions).forEach(desc => {
                    if (desc) desc.classList.add('hidden');
                });

                // Mostra la descrizione appropriata
                const selected = visibilitySelect.value;
                if (selected && descriptions[selected]) {
                    descriptions[selected].classList.remove('hidden');
                }
            }

            // Aggiorna al cambio
            visibilitySelect.addEventListener('change', updateVisibilityDescription);
            
            // Inizializza
            updateVisibilityDescription();
        });

        // Conferma eliminazione club
        function confirmDelete() {
            if (confirm('{{ __('app.clubs.delete_confirm') }}')) {
                // TODO: Implementare eliminazione club
                alert('{{ __('app.clubs.delete_not_implemented') }}');
            }
        }
    </script>
    @endpush
</x-site-layout>
