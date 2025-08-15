<x-site-layout>
    {{-- Header del profilo --}}
    <div class="bg-gradient-to-r from-primary-600 to-primary-800 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center space-x-6">
                {{-- Avatar dell'utente --}}
                <div class="w-20 h-20 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                    <span class="text-2xl font-bold text-white">
                        {{ substr($user->name, 0, 1) }}
                    </span>
                </div>
                
                {{-- Informazioni utente --}}
                <div class="flex-1">
                    <h1 class="text-3xl font-bold">{{ $user->name }}</h1>
                    <p class="text-primary-100 mt-1">
                        {{ __('app.profiles.member_since') }} {{ $stats['member_since'] }}
                    </p>
                    @if($areFriends)
                        <div class="mt-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-green-500 bg-opacity-20 text-green-100 text-sm">
                                👥 {{ __('app.profiles.friends') }}
                            </span>
                        </div>
                    @endif
                </div>
                
                {{-- Statistiche veloci --}}
                <div class="hidden md:flex space-x-8 text-center">
                    <div>
                        <div class="text-2xl font-bold">{{ $stats['completed_challenges'] }}</div>
                        <div class="text-sm text-primary-100">{{ __('app.profiles.challenges_completed') }}</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $stats['friends_count'] }}</div>
                        <div class="text-sm text-primary-100">{{ __('app.profiles.friends_count') }}</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $stats['current_streak'] }}</div>
                        <div class="text-sm text-primary-100">{{ __('app.profiles.day_streak') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Contenuto del profilo --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid lg:grid-cols-3 gap-8">
            {{-- Statistiche principali --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Statistiche generali --}}
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">
                        {{ __('app.profiles.general_stats') }}
                    </h2>
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div class="flex justify-between">
                                <span class="text-neutral-600 dark:text-neutral-400">{{ __('app.profiles.total_challenges') }}</span>
                                <span class="font-medium text-neutral-900 dark:text-white">{{ $stats['completed_challenges'] }}</span>
                            </div>
                            
                            @if($stats['best_time'])
                            <div class="flex justify-between">
                                <span class="text-neutral-600 dark:text-neutral-400">{{ __('app.profiles.best_time') }}</span>
                                <span class="font-medium text-green-600 dark:text-green-400">{{ $stats['best_time'] }}</span>
                            </div>
                            @endif
                            
                            <div class="flex justify-between">
                                <span class="text-neutral-600 dark:text-neutral-400">{{ __('app.profiles.current_streak') }}</span>
                                <span class="font-medium text-orange-600 dark:text-orange-400">
                                    {{ $stats['current_streak'] }} {{ trans_choice('app.profiles.days', $stats['current_streak']) }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="flex justify-between">
                                <span class="text-neutral-600 dark:text-neutral-400">{{ __('app.profiles.friends_count') }}</span>
                                <span class="font-medium text-blue-600 dark:text-blue-400">{{ $stats['friends_count'] }}</span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-neutral-600 dark:text-neutral-400">{{ __('app.profiles.member_since') }}</span>
                                <span class="font-medium text-neutral-900 dark:text-white">{{ $stats['member_since'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Statistiche per difficoltà --}}
                @if($stats['by_difficulty']->count() > 0)
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">
                        {{ __('app.profiles.stats_by_difficulty') }}
                    </h2>
                    
                    <div class="space-y-4">
                        @foreach($stats['by_difficulty'] as $difficulty => $diffStats)
                        <div class="border border-neutral-200 dark:border-neutral-600 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="font-medium text-neutral-900 dark:text-white capitalize">
                                    {{ ucfirst($difficulty) }}
                                </h3>
                                <span class="text-sm text-neutral-500">
                                    {{ $diffStats['completed'] }}/{{ $diffStats['total_attempts'] }} 
                                    ({{ $diffStats['completion_rate'] }}%)
                                </span>
                            </div>
                            
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                                @if($diffStats['best_time'])
                                <div>
                                    <div class="text-neutral-600 dark:text-neutral-400">{{ __('app.profiles.best_time') }}</div>
                                    <div class="font-medium text-green-600 dark:text-green-400">{{ $diffStats['best_time'] }}</div>
                                </div>
                                @endif
                                
                                @if($diffStats['avg_time'])
                                <div>
                                    <div class="text-neutral-600 dark:text-neutral-400">{{ __('app.profiles.avg_time') }}</div>
                                    <div class="font-medium text-blue-600 dark:text-blue-400">{{ $diffStats['avg_time'] }}</div>
                                </div>
                                @endif
                                
                                <div>
                                    <div class="text-neutral-600 dark:text-neutral-400">{{ __('app.profiles.completion_rate') }}</div>
                                    <div class="font-medium text-purple-600 dark:text-purple-400">{{ $diffStats['completion_rate'] }}%</div>
                                </div>
                            </div>
                            
                            {{-- Barra di progresso --}}
                            <div class="mt-3">
                                <div class="bg-neutral-200 dark:bg-neutral-700 rounded-full h-2">
                                    <div class="bg-primary-600 h-2 rounded-full transition-all duration-300" 
                                         style="width: {{ $diffStats['completion_rate'] }}%"></div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Amici in comune --}}
                @if($mutualFriends->count() > 0)
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">
                        {{ __('app.profiles.mutual_friends') }} ({{ $mutualFriends->count() }})
                    </h2>
                    
                    <div class="space-y-3">
                        @foreach($mutualFriends->take(5) as $friend)
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center">
                                <span class="text-sm font-medium text-primary-600 dark:text-primary-400">
                                    {{ substr($friend->name, 0, 1) }}
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-neutral-900 dark:text-white truncate">
                                    {{ $friend->name }}
                                </div>
                            </div>
                        </div>
                        @endforeach
                        
                        @if($mutualFriends->count() > 5)
                        <div class="text-center pt-2">
                            <span class="text-sm text-neutral-500">
                                {{ __('app.profiles.and_more', ['count' => $mutualFriends->count() - 5]) }}
                            </span>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Azioni profilo --}}
                @auth
                @if(!$areFriends && Auth::id() !== $user->id)
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">
                        {{ __('app.profiles.connect') }}
                    </h2>
                    
                    <button onclick="sendFriendRequestFromProfile({{ $user->id }})" 
                            class="w-full px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                        👥 {{ __('app.profiles.send_friend_request') }}
                    </button>
                </div>
                @endif
                @endauth

                @guest
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">
                        {{ __('app.profiles.join_community') }}
                    </h2>
                    <p class="text-neutral-600 dark:text-neutral-400 text-sm mb-4">
                        {{ __('app.profiles.join_description') }}
                    </p>
                    <a href="{{ route('register') }}" 
                       class="w-full inline-block text-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                        {{ __('app.profiles.join_now') }}
                    </a>
                </div>
                @endguest
            </div>
        </div>
    </div>

    @auth
    @push('scripts')
    <script>
        async function sendFriendRequestFromProfile(userId) {
            try {
                const response = await fetch('/api/friends/request', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        friend_id: userId,
                        message: 'Hi! Let\'s be friends and challenge each other at Sudoku!'
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('✓ ' + data.message);
                    location.reload(); // Ricarica per aggiornare lo stato
                } else {
                    alert('✗ ' + data.message);
                }
            } catch (error) {
                alert('✗ {{ __("app.friends.request_error") }}');
            }
        }
    </script>
    @endpush
    @endauth
</x-site-layout>
