<x-site-layout>
    {{-- Header della pagina --}}
    <div class="bg-white dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">
                        {{ __('app.privacy.title') }}
                    </h1>
                    <p class="mt-2 text-neutral-600 dark:text-neutral-400">
                        {{ __('app.privacy.subtitle') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if (session('success'))
            <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-green-800 dark:text-green-200">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('localized.privacy.update', ['locale' => app()->getLocale()]) }}" class="space-y-8">
            @csrf

            {{-- Visibilità Profilo --}}
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">
                    {{ __('app.privacy.profile_visibility') }}
                </h2>
                <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-4">
                    {{ __('app.privacy.profile_visibility_description') }}
                </p>
                
                <div class="space-y-3">
                    @foreach($visibilityOptions as $value => $label)
                    <label class="flex items-start space-x-3 cursor-pointer">
                        <input type="radio" 
                               name="profile_visibility" 
                               value="{{ $value }}"
                               {{ $user->profile_visibility === $value ? 'checked' : '' }}
                               class="mt-1 h-4 w-4 text-primary-600 focus:ring-primary-500 border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700">
                        <div>
                            <div class="font-medium text-neutral-900 dark:text-white">{{ $label }}</div>
                            <div class="text-sm text-neutral-500 dark:text-neutral-400">
                                @if($value === 'public')
                                    {{ __('app.privacy.profile_public_description') }}
                                @elseif($value === 'friends')
                                    {{ __('app.privacy.profile_friends_description') }}
                                @else
                                    {{ __('app.privacy.profile_private_description') }}
                                @endif
                            </div>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Visibilità Statistiche --}}
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">
                    {{ __('app.privacy.stats_visibility') }}
                </h2>
                <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-4">
                    {{ __('app.privacy.stats_visibility_description') }}
                </p>
                
                <div class="space-y-3">
                    @foreach($visibilityOptions as $value => $label)
                    <label class="flex items-start space-x-3 cursor-pointer">
                        <input type="radio" 
                               name="stats_visibility" 
                               value="{{ $value }}"
                               {{ $user->stats_visibility === $value ? 'checked' : '' }}
                               class="mt-1 h-4 w-4 text-primary-600 focus:ring-primary-500 border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700">
                        <div>
                            <div class="font-medium text-neutral-900 dark:text-white">{{ $label }}</div>
                            <div class="text-sm text-neutral-500 dark:text-neutral-400">
                                @if($value === 'public')
                                    {{ __('app.privacy.stats_public_description') }}
                                @elseif($value === 'friends')
                                    {{ __('app.privacy.stats_friends_description') }}
                                @else
                                    {{ __('app.privacy.stats_private_description') }}
                                @endif
                            </div>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Impostazioni Generali --}}
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">
                    {{ __('app.privacy.general_settings') }}
                </h2>
                
                <div class="space-y-4">
                    {{-- Richieste di amicizia --}}
                    <label class="flex items-start space-x-3 cursor-pointer">
                        <input type="checkbox" 
                               name="friend_requests_enabled" 
                               value="1"
                               {{ $user->friend_requests_enabled ? 'checked' : '' }}
                               class="mt-1 h-4 w-4 text-primary-600 focus:ring-primary-500 border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 rounded">
                        <div>
                            <div class="font-medium text-neutral-900 dark:text-white">
                                {{ __('app.privacy.friend_requests_enabled') }}
                            </div>
                            <div class="text-sm text-neutral-500 dark:text-neutral-400">
                                {{ __('app.privacy.friend_requests_description') }}
                            </div>
                        </div>
                    </label>

                    {{-- Status online --}}
                    <label class="flex items-start space-x-3 cursor-pointer">
                        <input type="checkbox" 
                               name="show_online_status" 
                               value="1"
                               {{ $user->show_online_status ? 'checked' : '' }}
                               class="mt-1 h-4 w-4 text-primary-600 focus:ring-primary-500 border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 rounded">
                        <div>
                            <div class="font-medium text-neutral-900 dark:text-white">
                                {{ __('app.privacy.show_online_status') }}
                            </div>
                            <div class="text-sm text-neutral-500 dark:text-neutral-400">
                                {{ __('app.privacy.online_status_description') }}
                            </div>
                        </div>
                    </label>

                    {{-- Activity Feed --}}
                    <label class="flex items-start space-x-3 cursor-pointer">
                        <input type="checkbox" 
                               name="activity_feed_visible" 
                               value="1"
                               {{ $user->activity_feed_visible ? 'checked' : '' }}
                               class="mt-1 h-4 w-4 text-primary-600 focus:ring-primary-500 border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 rounded">
                        <div>
                            <div class="font-medium text-neutral-900 dark:text-white">
                                {{ __('app.privacy.activity_feed_visible') }}
                            </div>
                            <div class="text-sm text-neutral-500 dark:text-neutral-400">
                                {{ __('app.privacy.activity_feed_description') }}
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Bottoni --}}
            <div class="flex justify-end space-x-4 pt-6">
                <a href="{{ route('localized.profile', ['locale' => app()->getLocale()]) }}" 
                   class="px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-colors">
                    {{ __('app.privacy.cancel') }}
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900 transition-colors">
                    {{ __('app.privacy.save_settings') }}
                </button>
            </div>
        </form>
    </div>
</x-site-layout>
