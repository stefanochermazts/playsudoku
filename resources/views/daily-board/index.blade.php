<x-site-layout>
    <div class="max-w-6xl mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-neutral-900 dark:text-white mb-2">
                {{ __('app.daily_board') }}
            </h1>
            <p class="text-neutral-600 dark:text-neutral-300">
                {{ $today->format('l, F j, Y') }}
            </p>
        </div>

        @if($dailyChallenge)
            <div class="grid lg:grid-cols-2 gap-8">
                <!-- Challenge Info -->
                <div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
                    <h2 class="text-xl font-semibold text-neutral-900 dark:text-white mb-4">
                        {{ __('app.todays_challenge') }}
                    </h2>
                    
                    <div class="space-y-4">
                        <!-- Challenge identifier -->
                        <div class="flex justify-between items-center">
                            <span class="text-neutral-600 dark:text-neutral-300">Challenge</span>
                            <span class="text-neutral-900 dark:text-white font-medium">#{{ $dailyChallenge->id }} — {{ $dailyChallenge->title ?? 'Daily' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-neutral-600 dark:text-neutral-300">{{ __('app.board.difficulty') }}:</span>
                            <span class="px-3 py-1 rounded-full text-sm font-medium
                                @switch($dailyChallenge->puzzle->difficulty)
                                    @case('easy') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300 @break
                                    @case('normal') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 @break
                                    @case('hard') bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300 @break
                                    @case('expert') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 @break
                                    @case('crazy') bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300 @break
                                @endswitch
                            ">
                                {{ ucfirst($dailyChallenge->puzzle->difficulty) }}
                            </span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-neutral-600 dark:text-neutral-300">{{ __('app.puzzle_seed') }}:</span>
                            <span class="font-mono text-neutral-900 dark:text-white">{{ $dailyChallenge->puzzle->seed }}</span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-neutral-600 dark:text-neutral-300">{{ __('app.status') }}:</span>
                            <span class="px-3 py-1 rounded-full text-sm font-medium
                                @if($dailyChallenge->status === 'active') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300
                                @else bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-200 @endif
                            ">
                                {{ ucfirst($dailyChallenge->status) }}
                            </span>
                        </div>

                        @php($userCompleted = auth()->check() && $dailyChallenge->attempts()->where('valid', true)->whereNotNull('completed_at')->where('user_id', auth()->id())->exists())
                        @if($dailyChallenge->status === 'active' && !$userCompleted)
                            <div class="pt-4">
                                <a href="{{ route('localized.challenges.play', ['locale' => app()->getLocale(), 'challenge' => $dailyChallenge->id]) }}" 
                                   class="w-full inline-flex justify-center items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                                    {{ __('app.play_now') }}
                                </a>
                            </div>
                        @else
                            <div class="pt-4">
                                <a href="{{ route('localized.leaderboard.show', ['locale' => app()->getLocale(), 'challenge' => $dailyChallenge->id]) }}" 
                                   class="w-full inline-flex justify-center items-center px-4 py-2 bg-primary-100 hover:bg-primary-200 text-primary-700 dark:bg-primary-900/30 dark:hover:bg-primary-900/50 dark:text-primary-300 font-medium rounded-lg transition-colors">
                                    {{ __('app.view_full_leaderboard') }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Top Players (show even if empty) -->
                <div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
                    <h2 class="text-xl font-semibold text-neutral-900 dark:text-white mb-2">
                        {{ __('app.top_players_today') }}
                    </h2>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-4">Tempo penalizzato: +3 secondi per errore</p>

                    @if($leaderboard && $leaderboard->count() > 0)
                        <div class="space-y-3">
                            @foreach($leaderboard->take(5) as $index => $attempt)
                                @php($isCurrentUser = auth()->check() && ((int)($attempt['user_id'] ?? 0) === auth()->id()))
                                <div class="flex items-center justify-between {{ $isCurrentUser ? 'bg-primary-50 dark:bg-primary-900/20 rounded-md px-2 py-1' : '' }}">
                                    <div class="flex items-center space-x-3">
                                        <span class="w-6 h-6 flex items-center justify-center rounded-full text-sm font-medium
                                            @if($index === 0) bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300
                                            @elseif($index === 1) bg-neutral-200 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-200
                                            @elseif($index === 2) bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300
                                            @else bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400 @endif
                                        ">
                                            {{ $index + 1 }}
                                        </span>
                                        <span class="text-neutral-900 dark:text-white font-medium {{ $isCurrentUser ? 'text-primary-700 dark:text-primary-300' : '' }}">{{ $attempt['user_name'] ?? '—' }}</span>
                                    </div>
                                    <div class="text-right">
                                        <span class="font-mono text-sm text-neutral-900 dark:text-white font-semibold">
                                            {{ $attempt['formatted_duration'] ?? '-' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-sm text-neutral-600 dark:text-neutral-400">
                            {{ __('app.no_completions_yet') }}
                        </div>
                    @endif

                    <div class="pt-4 mt-4 border-t border-neutral-200 dark:border-neutral-700">
                        <a href="{{ route('localized.leaderboard.show', ['locale' => app()->getLocale(), 'challenge' => $dailyChallenge->id]) }}" 
                           class="text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 text-sm font-medium">
                            {{ __('app.view_full_leaderboard') }} →
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-8 text-center">
                <div class="text-neutral-400 dark:text-neutral-500 mb-4">
                    <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-neutral-900 dark:text-white mb-2">
                    {{ __('app.no_daily_challenge') }}
                </h3>
                <p class="text-neutral-600 dark:text-neutral-300 mb-4">
                    {{ __('app.no_daily_challenge_description') }}
                </p>
            </div>
        @endif

        <!-- Navigation -->
        <div class="mt-8 flex justify-center space-x-4">
            <a href="{{ route('localized.daily-board.archive', ['locale' => app()->getLocale()]) }}" 
               class="inline-flex items-center px-4 py-2 bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-colors">
                {{ __('app.view_archive') }}
            </a>
            <a href="{{ route('localized.weekly-board.index', ['locale' => app()->getLocale()]) }}" 
               class="inline-flex items-center px-4 py-2 bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-colors">
                {{ __('app.weekly_board') }}
            </a>
        </div>
    </div>
</x-site-layout>
