<div class="min-h-screen bg-gradient-to-br from-primary-50 via-neutral-50 to-secondary-50 dark:from-neutral-900 dark:via-neutral-950 dark:to-neutral-900">
    <!-- Header -->
    <div class="bg-white/80 dark:bg-neutral-900/80 backdrop-blur-sm border-b border-neutral-200 dark:border-neutral-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">{{ __('app.challenges.title') }}</h1>
                    <p class="text-neutral-600 dark:text-neutral-300 mt-2">{{ __('app.challenges.subtitle') }}</p>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('dashboard') }}" 
                       class="p-2 bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg hover:bg-gray-50 dark:hover:bg-neutral-700">
                        <svg class="w-5 h-5 text-neutral-600 dark:text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50 mb-6">
            <div class="grid md:grid-cols-2 gap-6">
                <!-- Type Filter -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3">{{ __('app.challenges.filter_by_type') }}</label>
                    <div class="flex flex-wrap gap-2">
                        <button wire:click="setFilter('all')"
                                class="px-4 py-2 rounded-lg border font-medium transition-colors {{ $filter === 'all' ? 'bg-primary-100 border-primary-300 text-primary-700 dark:bg-primary-900 dark:border-primary-700 dark:text-primary-300' : 'bg-white border-neutral-200 text-neutral-700 hover:bg-neutral-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700' }}">
                            {{ __('app.challenges.all') }}
                        </button>
                        <button wire:click="setFilter('daily')"
                                class="px-4 py-2 rounded-lg border font-medium transition-colors {{ $filter === 'daily' ? 'bg-primary-100 border-primary-300 text-primary-700 dark:bg-primary-900 dark:border-primary-700 dark:text-primary-300' : 'bg-white border-neutral-200 text-neutral-700 hover:bg-neutral-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700' }}">
                            🌅 {{ __('app.challenges.daily') }}
                        </button>
                        <button wire:click="setFilter('weekly')"
                                class="px-4 py-2 rounded-lg border font-medium transition-colors {{ $filter === 'weekly' ? 'bg-primary-100 border-primary-300 text-primary-700 dark:bg-primary-900 dark:border-primary-700 dark:text-primary-300' : 'bg-white border-neutral-200 text-neutral-700 hover:bg-neutral-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700' }}">
                            📅 {{ __('app.challenges.weekly') }}
                        </button>
                        <button wire:click="setFilter('custom')"
                                class="px-4 py-2 rounded-lg border font-medium transition-colors {{ $filter === 'custom' ? 'bg-primary-100 border-primary-300 text-primary-700 dark:bg-primary-900 dark:border-primary-700 dark:text-primary-300' : 'bg-white border-neutral-200 text-neutral-700 hover:bg-neutral-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700' }}">
                            🎯 {{ __('app.challenges.custom') }}
                        </button>
                    </div>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3">{{ __('app.challenges.filter_by_status') }}</label>
                    <div class="flex flex-wrap gap-2">
                        <button wire:click="setStatus('all')"
                                class="px-4 py-2 rounded-lg border font-medium transition-colors {{ $status === 'all' ? 'bg-secondary-100 border-secondary-300 text-secondary-700 dark:bg-secondary-900 dark:border-secondary-700 dark:text-secondary-300' : 'bg-white border-neutral-200 text-neutral-700 hover:bg-neutral-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700' }}">
                            {{ __('app.challenges.all') }}
                        </button>
                        <button wire:click="setStatus('not_started')"
                                class="px-4 py-2 rounded-lg border font-medium transition-colors {{ $status === 'not_started' ? 'bg-secondary-100 border-secondary-300 text-secondary-700 dark:bg-secondary-900 dark:border-secondary-700 dark:text-secondary-300' : 'bg-white border-neutral-200 text-neutral-700 hover:bg-neutral-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700' }}">
                            {{ __('app.challenges.not_started') }}
                        </button>
                        <button wire:click="setStatus('in_progress')"
                                class="px-4 py-2 rounded-lg border font-medium transition-colors {{ $status === 'in_progress' ? 'bg-secondary-100 border-secondary-300 text-secondary-700 dark:bg-secondary-900 dark:border-secondary-700 dark:text-secondary-300' : 'bg-white border-neutral-200 text-neutral-700 hover:bg-neutral-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700' }}">
                            {{ __('app.challenges.in_progress') }}
                        </button>
                        <button wire:click="setStatus('completed')"
                                class="px-4 py-2 rounded-lg border font-medium transition-colors {{ $status === 'completed' ? 'bg-secondary-100 border-secondary-300 text-secondary-700 dark:bg-secondary-900 dark:border-secondary-700 dark:text-secondary-300' : 'bg-white border-neutral-200 text-neutral-700 hover:bg-neutral-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700' }}">
                            {{ __('app.challenges.completed') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Challenges Grid -->
        <div class="grid lg:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
            @forelse($challenges as $challenge)
                @php
                    $challengeStatus = $this->getChallengeStatus($challenge->id);
                    $statusLabel = $this->getChallengeStatusLabel($challenge->id);
                    $statusColor = $this->getChallengeStatusColor($challenge->id);
                    $attempt = $userAttempts->get($challenge->id);
                    $isExpired = $challenge->ends_at <= now();
                    $isActive = $challenge->status === 'active' && !$isExpired;
                @endphp
                
                <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50 hover:bg-white/80 dark:hover:bg-neutral-800/80 transition-all">
                    <!-- Header -->
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-2">
                                @if($challenge->type === 'daily')
                                    <span class="text-2xl">🌅</span>
                                @elseif($challenge->type === 'weekly')
                                    <span class="text-2xl">📅</span>
                                @else
                                    <span class="text-2xl">🎯</span>
                                @endif
                                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">
                                    {{ $challenge->title ?? ($challenge->type === 'daily' ? __('app.challenges.daily_challenge') : ($challenge->type === 'weekly' ? __('app.challenges.weekly_challenge') : __('app.challenges.custom_challenge'))) }}
                                </h3>
                            </div>
                            @if($challenge->description)
                                <p class="text-sm text-neutral-600 dark:text-neutral-300 mb-3">{{ $challenge->description }}</p>
                            @endif
                        </div>
                        <span class="px-2 py-1 text-xs font-medium rounded-lg {{ $statusColor }}">
                            {{ $statusLabel }}
                        </span>
                    </div>

                    <!-- Details -->
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-neutral-600 dark:text-neutral-300">{{ __('app.challenges.difficulty') }}:</span>
                            <span class="font-medium text-neutral-900 dark:text-white">{{ ucfirst($challenge->puzzle->difficulty ?? 'normal') }}</span>
                        </div>
                        
                        @if($isActive)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-neutral-600 dark:text-neutral-300">{{ __('app.challenges.ends_in') }}:</span>
                                <span class="font-medium text-neutral-900 dark:text-white">{{ $challenge->ends_at->diffForHumans() }}</span>
                            </div>
                        @elseif($isExpired)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-neutral-600 dark:text-neutral-300">{{ __('app.challenges.status') }}:</span>
                                <span class="font-medium text-red-600 dark:text-red-400">{{ __('app.challenges.expired') }}</span>
                            </div>
                        @else
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-neutral-600 dark:text-neutral-300">{{ __('app.challenges.starts_at') }}:</span>
                                <span class="font-medium text-neutral-900 dark:text-white">{{ $challenge->starts_at->format('M j, H:i') }}</span>
                            </div>
                        @endif

                        @if($attempt && $attempt->duration_ms)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-neutral-600 dark:text-neutral-300">{{ __('app.challenges.your_time') }}:</span>
                                <span class="font-medium text-green-600 dark:text-green-400">{{ $this->getFormattedTime($attempt->duration_ms) }}</span>
                            </div>
                            @php
                                // Calcola posizione rapida utente per label (stessa ordering della leaderboard)
                                $rank = App\Models\ChallengeAttempt::where('challenge_id', $challenge->id)
                                    ->where('valid', true)->whereNotNull('completed_at')
                                    ->orderBy('duration_ms')->orderBy('errors_count')->orderBy('completed_at')
                                    ->pluck('user_id')->search(auth()->id());
                                $rank = is_int($rank) ? $rank + 1 : null;
                            @endphp
                            @if($rank)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-neutral-600 dark:text-neutral-300">Posizione:</span>
                                    <span class="font-semibold">#{{ $rank }}</span>
                                </div>
                            @endif
                        @endif

                        @if($challenge->settings && isset($challenge->settings['hints_allowed']))
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-neutral-600 dark:text-neutral-300">{{ __('app.challenges.hints') }}:</span>
                                <span class="font-medium {{ $challenge->settings['hints_allowed'] ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $challenge->settings['hints_allowed'] ? __('app.challenges.allowed') : __('app.challenges.not_allowed') }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="pt-4 border-t border-neutral-200 dark:border-neutral-700">
                        @if($challengeStatus === 'completed')
                            <div class="flex items-center justify-center p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-green-700 dark:text-green-300 font-medium">{{ __('app.challenges.completed') }}</span>
                            </div>
                        @elseif(!$isActive)
                            <div class="flex items-center justify-center p-3 bg-gray-50 dark:bg-gray-900/20 border border-gray-200 dark:border-gray-800 rounded-lg">
                                <span class="text-gray-600 dark:text-gray-400 font-medium">
                                    {{ $isExpired ? __('app.challenges.expired') : __('app.challenges.not_started_yet') }}
                                </span>
                            </div>
                        @else
                            <button wire:click="startChallenge({{ $challenge->id }})"
                                    class="w-full px-4 py-3 bg-gradient-to-r from-primary-600 to-secondary-600 text-white font-semibold rounded-lg hover:from-primary-700 hover:to-secondary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all transform hover:scale-105">
                                @if($challengeStatus === 'in_progress')
                                    {{ __('app.challenges.continue') }}
                                @else
                                    {{ __('app.challenges.start') }}
                                @endif
                            </button>
                        @endif
                        <a href="{{ route('localized.leaderboard.show', ['locale' => app()->getLocale(), 'challenge' => $challenge->id]) }}"
                           class="mt-3 w-full inline-flex items-center justify-center px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                            {{ __('app.dashboard.view_leaderboard') }}
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <div class="text-center py-12 bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl border border-neutral-200/50 dark:border-neutral-700/50">
                        <div class="w-20 h-20 bg-gradient-to-r from-primary-500 to-secondary-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2">{{ __('app.challenges.no_challenges') }}</h3>
                        <p class="text-neutral-600 dark:text-neutral-300 mb-6">{{ __('app.challenges.no_challenges_desc') }}</p>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($challenges->hasPages())
            <div class="flex justify-center">
                {{ $challenges->links() }}
            </div>
        @endif
    </div>
</div>