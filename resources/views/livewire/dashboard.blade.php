<div class="min-h-screen bg-gradient-to-br from-primary-50 via-neutral-50 to-secondary-50 dark:from-neutral-900 dark:via-neutral-950 dark:to-neutral-900">
    <!-- Header -->
    <div class="bg-white/80 dark:bg-neutral-900/80 backdrop-blur-sm border-b border-neutral-200 dark:border-neutral-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">{{ __('app.dashboard.title') }}</h1>
                    <p class="text-neutral-600 dark:text-neutral-300 mt-2">{{ __('app.dashboard.welcome', ['name' => auth()->user()->name]) }}</p>
                </div>
                <div class="flex items-center space-x-4">
                    <button wire:click="refreshData" 
                            class="p-2 bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg hover:bg-gray-50 dark:hover:bg-neutral-700">
                        <svg class="w-5 h-5 text-neutral-600 dark:text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </button>
                    <div class="bg-gradient-to-r from-primary-500 to-secondary-500 text-white px-4 py-2 rounded-lg">
                        <span class="text-sm font-medium">Livello 1</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Quick Stats -->
        <div class="grid md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-r from-primary-500 to-primary-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-neutral-600 dark:text-neutral-300">{{ __('app.dashboard.puzzles_solved') }}</p>
                        <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $userStats['puzzles_solved'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-r from-secondary-500 to-secondary-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-neutral-600 dark:text-neutral-300">{{ __('app.dashboard.best_time') }}</p>
                        <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $this->getFormattedTime($userStats['best_time'] ?? null) }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-r from-accent-500 to-accent-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-neutral-600 dark:text-neutral-300">{{ __('app.dashboard.current_streak') }}</p>
                        <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $userStats['current_streak'] ?? 0 }} {{ __('app.dashboard.days') }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-r from-success-500 to-success-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-neutral-600 dark:text-neutral-300">{{ __('app.dashboard.total_errors') }}</p>
                        <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $userStats['total_errors'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-neutral-900 dark:text-white mb-6">{{ __('app.dashboard.quick_actions') }}</h2>
            <div class="grid md:grid-cols-4 gap-4">
                <a href="{{ route('localized.challenges.index', ['locale' => app()->getLocale()]) }}" 
                   class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-xl p-6 border border-neutral-200/50 dark:border-neutral-700/50 hover:bg-white dark:hover:bg-neutral-800 transition-all group">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                            <span class="text-xl">🎯</span>
                        </div>
                        <div>
                            <h3 class="font-medium text-neutral-900 dark:text-white">{{ __('app.nav.challenges') }}</h3>
                            <p class="text-sm text-neutral-600 dark:text-neutral-300">{{ __('app.dashboard.browse_challenges') }}</p>
                        </div>
                    </div>
                </a>
                
                <a href="{{ route('localized.daily-board.index', ['locale' => app()->getLocale()]) }}" 
                   class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-xl p-6 border border-neutral-200/50 dark:border-neutral-700/50 hover:bg-white dark:hover:bg-neutral-800 transition-all group">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                            <span class="text-xl">📅</span>
                        </div>
                        <div>
                            <h3 class="font-medium text-neutral-900 dark:text-white">{{ __('app.daily_board') }}</h3>
                            <p class="text-sm text-neutral-600 dark:text-neutral-300">{{ __('app.dashboard.daily_leaderboards') }}</p>
                        </div>
                    </div>
                </a>
                
                <a href="{{ route('localized.weekly-board.index', ['locale' => app()->getLocale()]) }}" 
                   class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-xl p-6 border border-neutral-200/50 dark:border-neutral-700/50 hover:bg-white dark:hover:bg-neutral-800 transition-all group">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                            <span class="text-xl">📊</span>
                        </div>
                        <div>
                            <h3 class="font-medium text-neutral-900 dark:text-white">{{ __('app.weekly_board') }}</h3>
                            <p class="text-sm text-neutral-600 dark:text-neutral-300">{{ __('app.dashboard.weekly_leaderboards') }}</p>
                        </div>
                    </div>
                </a>
                
                <a href="{{ route('profile') }}" 
                   class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-xl p-6 border border-neutral-200/50 dark:border-neutral-700/50 hover:bg-white dark:hover:bg-neutral-800 transition-all group">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                            <span class="text-xl">👤</span>
                        </div>
                        <div>
                            <h3 class="font-medium text-neutral-900 dark:text-white">{{ __('app.nav.profile') }}</h3>
                            <p class="text-sm text-neutral-600 dark:text-neutral-300">{{ __('app.dashboard.view_stats') }}</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Active Challenges -->
            <div class="lg:col-span-2">
                <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-8 border border-neutral-200/50 dark:border-neutral-700/50">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-neutral-900 dark:text-white">{{ __('app.dashboard.active_challenges') }}</h2>
                        <a href="{{ app()->has('locale') && in_array(app()->getLocale(), ['en', 'it']) ? route('localized.challenges.index') : route('challenges.index') }}" class="text-primary-600 dark:text-primary-400 hover:underline text-sm font-medium">
                            {{ __('app.dashboard.view_all_challenges') }} →
                        </a>
                    </div>
                    
                    @if($activeChallenges->count() > 0)
                        <div class="space-y-4">
                            @foreach($activeChallenges->take(3) as $challenge)
                                @php
                                    $status = $this->getChallengeStatus($challenge->id);
                                    $statusLabel = $this->getChallengeStatusLabel($challenge->id);
                                    $statusColor = $this->getChallengeStatusColor($challenge->id);
                                    $attempt = $userAttempts->get($challenge->id);
                                @endphp
                                
                                <div class="border border-neutral-200 dark:border-neutral-700 rounded-xl p-6 hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-3 mb-2">
                                                <h3 class="font-semibold text-neutral-900 dark:text-white">
                                                    @if($challenge->type === 'daily')
                                                        🌅 {{ __('app.dashboard.challenge_daily') }}
                                                    @elseif($challenge->type === 'weekly')
                                                        📅 {{ __('app.dashboard.challenge_weekly') }}
                                                    @else
                                                        🎯 {{ $challenge->title ?? __('app.dashboard.challenge_custom') }}
                                                    @endif
                                                </h3>
                                                <span class="px-2 py-1 text-xs font-medium rounded-lg {{ $statusColor }}">
                                                    {{ $statusLabel }}
                                                </span>
                                            </div>
                                            
                                            <div class="flex items-center space-x-4 text-sm text-neutral-600 dark:text-neutral-300">
                                                <span>{{ __('app.dashboard.difficulty_label') }} <strong>{{ ucfirst($challenge->puzzle->difficulty ?? 'Normal') }}</strong></span>
                                                <span>{{ __('app.dashboard.expires_label') }} <strong>{{ $challenge->ends_at->diffForHumans() }}</strong></span>
                                                @if($attempt && $attempt->duration_ms)
                                                    <span>{{ __('app.dashboard.time_label') }} <strong>{{ $this->getFormattedTime($attempt->duration_ms) }}</strong></span>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <div class="ml-4">
                                            @if($status === 'completed')
                                                <div class="text-green-600 dark:text-green-400 text-center">
                                                    <svg class="w-8 h-8 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <span class="text-xs font-medium">{{ __('app.dashboard.status_completed') }}</span>
                                                </div>
                                            @else
                                                <button wire:click="startChallenge({{ $challenge->id }})"
                                                        class="px-4 py-2 bg-gradient-to-r from-primary-600 to-secondary-600 text-white font-medium rounded-lg hover:from-primary-700 hover:to-secondary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all">
                                                    @if($status === 'in_progress')
                                                        {{ __('app.dashboard.action_continue') }}
                                                    @else
                                                        {{ __('app.dashboard.action_start') }}
                                                    @endif
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="w-20 h-20 bg-gradient-to-r from-primary-500 to-secondary-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2">{{ __('app.dashboard.no_challenges') }}</h3>
                            <p class="text-neutral-600 dark:text-neutral-300 mb-6">{{ __('app.dashboard.new_challenges_created_daily') }}</p>
                            <a href="{{ route('localized.sudoku.training', ['locale' => app()->getLocale()]) }}" 
                               class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-primary-600 to-secondary-600 text-white font-semibold rounded-xl hover:from-primary-700 hover:to-secondary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900 transition-all transform hover:scale-105">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                {{ __('app.dashboard.free_training') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">{{ __('app.dashboard.quick_actions') }}</h3>
                    <div class="space-y-3">
                        <a href="{{ route('localized.sudoku.training', ['locale' => app()->getLocale()]) }}" 
                           class="flex items-center p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            <span class="text-blue-700 dark:text-blue-300 font-medium">{{ __('app.dashboard.free_training') }}</span>
                        </a>
                        
                        <a href="{{ route('localized.challenges.index', ['locale' => app()->getLocale()]) }}" 
                           class="flex items-center p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-green-700 dark:text-green-300 font-medium">{{ __('app.dashboard.view_all_challenges') }}</span>
                        </a>
                        
                        <a href="{{ route('localized.challenges.index', ['locale' => app()->getLocale()]) }}" 
                           class="flex items-center p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg hover:bg-yellow-100 dark:hover:bg-yellow-900/30 transition-colors">
                            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span class="text-yellow-700 dark:text-yellow-300 font-medium">{{ __('app.dashboard.view_challenges_leaderboards') }}</span>
                        </a>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">{{ __('app.dashboard.your_stats') }}</h3>
                    <div class="space-y-3">
                        @if(($userStats['average_time'] ?? 0) > 0)
                            <div class="flex justify-between">
                                <span class="text-sm text-neutral-600 dark:text-neutral-300">{{ __('app.dashboard.average_time') }}:</span>
                                <span class="text-sm font-medium text-neutral-900 dark:text-white">{{ $this->getFormattedTime($userStats['average_time']) }}</span>
                            </div>
                        @endif
                        
                        @if(($userStats['hints_used'] ?? 0) > 0)
                            <div class="flex justify-between">
                                <span class="text-sm text-neutral-600 dark:text-neutral-300">{{ __('app.dashboard.hints_used') }}:</span>
                                <span class="text-sm font-medium text-neutral-900 dark:text-white">{{ $userStats['hints_used'] }}</span>
                            </div>
                        @endif
                        
                        @if(($userStats['puzzles_solved'] ?? 0) > 0)
                            <div class="flex justify-between">
                                <span class="text-sm text-neutral-600 dark:text-neutral-300">{{ __('app.dashboard.accuracy') }}:</span>
                                <span class="text-sm font-medium text-neutral-900 dark:text-white">
                                    {{ $userStats['total_errors'] > 0 ? round((1 - $userStats['total_errors'] / max($userStats['puzzles_solved'], 1)) * 100, 1) : 100 }}%
                                </span>
                            </div>
                        @endif
                        
                        @if(($userStats['puzzles_solved'] ?? 0) === 0)
                            <p class="text-sm text-neutral-600 dark:text-neutral-300">{{ __('app.dashboard.start_playing_to_see_stats') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
