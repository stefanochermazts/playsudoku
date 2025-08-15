<x-site-layout class="overflow-x-hidden">
    {{-- Header della pagina --}}
    <div class="bg-white dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white">
                        {{ __('app.clubs.invite_title') }}
                    </h1>
                    <p class="mt-2 text-neutral-600 dark:text-neutral-400">
                        {{ __('app.clubs.invite_subtitle', ['name' => $club->name]) }}
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

        @if (session('success'))
            <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800 dark:text-green-200">
                            {{ session('success') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{-- Form di invito --}}
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-6">
                    {{ __('app.clubs.select_friends') }}
                </h2>
                
                @if($friends->count() > 0)
                    <form method="POST" action="{{ route('localized.clubs.invite.send', ['locale' => app()->getLocale(), 'club' => $club->slug]) }}">
                        @csrf
                        
                        <div class="space-y-3 mb-6 max-h-96 overflow-y-auto">
                            @foreach($friends as $friend)
                                <label class="flex items-center p-3 rounded-lg border border-neutral-200 dark:border-neutral-600 hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-colors cursor-pointer">
                                    <input type="checkbox" 
                                           name="user_ids[]" 
                                           value="{{ $friend->id }}"
                                           class="rounded border-neutral-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                                    <div class="ml-3 flex-1 min-w-0">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                                </svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100 truncate">
                                                    {{ $friend->name }}
                                                </p>
                                                <p class="text-xs text-neutral-500 dark:text-neutral-400 truncate">
                                                    {{ $friend->email }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" 
                                    class="px-6 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900 transition-colors">
                                {{ __('app.clubs.send_invites') }}
                            </button>
                        </div>
                    </form>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M34 40h10v-4a6 6 0 00-10.712-3.714M34 40H14m20 0v-4a9.971 9.971 0 00-.712-3.714M14 40H4v-4a6 6 0 0110.712-3.714M14 40v-4a9.971 9.971 0 01.712-3.714M18 20a4 4 0 11-8 0 4 4 0 018 0zm16 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ __('app.clubs.no_friends_to_invite') }}</h3>
                        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                            Tutti i tuoi amici sono già membri del club o sono già stati invitati.
                        </p>
                    </div>
                @endif
            </div>

            {{-- Inviti pendenti --}}
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-6">
                    {{ __('app.clubs.pending_invites') }}
                </h2>
                
                @if($pendingInvites->count() > 0)
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @foreach($pendingInvites as $invite)
                            <div class="flex items-center justify-between p-3 rounded-lg border border-neutral-200 dark:border-neutral-600">
                                <div class="flex items-center space-x-3 flex-1 min-w-0">
                                    <div class="w-8 h-8 bg-amber-100 dark:bg-amber-900 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100 truncate">
                                            {{ $invite->user->name }}
                                        </p>
                                        <div class="flex items-center space-x-2 text-xs text-neutral-500 dark:text-neutral-400">
                                            <span>{{ __('app.clubs.invited_on') }} {{ $invite->invited_at->format('d/m/Y') }}</span>
                                            @if($invite->invitedBy)
                                                <span>•</span>
                                                <span>{{ __('app.clubs.invited_by') }} {{ $invite->invitedBy->name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <span class="px-2 py-1 text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200 rounded-full">
                                        {{ __('app.clubs.pending') ?? 'Pending' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v20c0 4.418 7.163 8 16 8 1.381 0 2.721-.087 4-.252M8 14c0 4.418 7.163 8 16 8s16-3.582 16-8M8 14c0-4.418 7.163-8 16-8s16 3.582 16 8m0 0v14m-16-4L24 22.59"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ __('app.clubs.no_pending_invites') }}</h3>
                        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                            Non ci sono inviti pendenti al momento.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Seleziona/deseleziona tutti
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('input[name="user_ids[]"]');
            const submitButton = document.querySelector('button[type="submit"]');
            
            function updateSubmitButton() {
                const checkedBoxes = document.querySelectorAll('input[name="user_ids[]"]:checked');
                if (submitButton) {
                    submitButton.disabled = checkedBoxes.length === 0;
                    submitButton.classList.toggle('opacity-50', checkedBoxes.length === 0);
                }
            }
            
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateSubmitButton);
            });
            
            // Inizializza stato
            updateSubmitButton();
        });
    </script>
    @endpush
</x-site-layout>
