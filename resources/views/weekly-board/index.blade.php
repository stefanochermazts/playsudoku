<x-site-layout>
    <div class="max-w-6xl mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-neutral-900 dark:text-white mb-2">
                {{ __('app.weekly_board') }}
            </h1>
            <p class="text-neutral-600 dark:text-neutral-300">
                {{ __('app.week_of') }} {{ $thisWeek->format('F j') }} - {{ $thisWeek->copy()->endOfWeek()->format('F j, Y') }}
            </p>
        </div>

        @if($weeklyChallenge)
            <div class="grid lg:grid-cols-2 gap-8">
                <!-- Challenge Info -->
                <div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
                    <h2 class="text-xl font-semibold text-neutral-900 dark:text-white mb-4">
                        {{ __('app.this_weeks_challenge') }}
                    </h2>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-neutral-600 dark:text-neutral-300">{{ __('app.difficulty') }}:</span>
                            <span class="px-3 py-1 rounded-full text-sm font-medium
                                @switch($weeklyChallenge->puzzle->difficulty)
                                    @case('easy') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300 @break
                                    @case('normal') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 @break
                                    @case('hard') bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300 @break
                                    @case('expert') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 @break
                                    @case('crazy') bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300 @break
                                @endswitch
                            ">
                                {{ ucfirst($weeklyChallenge->puzzle->difficulty) }}
                            </span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-neutral-600 dark:text-neutral-300">{{ __('app.puzzle_seed') }}:</span>
                            <span class="font-mono text-neutral-900 dark:text-white">{{ $weeklyChallenge->puzzle->seed }}</span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-neutral-600 dark:text-neutral-300">{{ __('app.status') }}:</span>
                            <span class="px-3 py-1 rounded-full text-sm font-medium
                                @if($weeklyChallenge->status === 'active') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300
                                @else bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-200 @endif
                            ">
                                {{ ucfirst($weeklyChallenge->status) }}
                            </span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-neutral-600 dark:text-neutral-300">{{ __('app.time_remaining') }}:</span>
                            <span class="text-neutral-900 dark:text-white font-medium">
                                {{ $weeklyChallenge->ends_at->diffForHumans() }}
                            </span>
                        </div>

                        @if($weeklyChallenge->status === 'active')
                            <div class="pt-4">
                                <a href="{{ route('localized.challenges.play', ['locale' => app()->getLocale(), 'challenge' => $weeklyChallenge->id]) }}" 
                                   class="w-full inline-flex justify-center items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                                    {{ __('app.play_now') }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Top Players -->
                @if($leaderboard && $leaderboard->count() > 0)
                    <div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
                        <h2 class="text-xl font-semibold text-neutral-900 dark:text-white mb-4">
                            {{ __('app.top_players_this_week') }}
                        </h2>
                        
                        <div class="space-y-3">
                            @foreach($leaderboard->take(10) as $index => $attempt)
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <span class="w-8 h-8 flex items-center justify-center rounded-full text-sm font-medium
                                            @if($index === 0) bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300
                                            @elseif($index === 1) bg-neutral-200 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-200
                                            @elseif($index === 2) bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300
                                            @else bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400 @endif
                                        ">
                                            {{ $index + 1 }}
                                        </span>
                                        <span class="text-neutral-900 dark:text-white">{{ $attempt->user?->name ?? '—' }}</span>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-mono text-sm text-neutral-900 dark:text-white">
                                            @php($ms = (int) ($attempt->duration_ms ?? 0))
                                            @php($s = intdiv($ms, 1000))
                                            @php($cs = intdiv($ms % 1000, 10))
                                            {{ sprintf('%02d:%02d.%02d', intdiv($s,60), $s%60, $cs) }}
                                        </div>
                                        <div class="text-xs text-neutral-500 dark:text-neutral-400">
                                            {{ $attempt->completed_at?->format('M j') }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="pt-4 mt-4 border-t border-neutral-200 dark:border-neutral-700">
                            <a href="{{ route('localized.leaderboard.show', ['locale' => app()->getLocale(), 'challenge' => $weeklyChallenge->id]) }}" 
                               class="text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 text-sm font-medium">
                                {{ __('app.view_full_leaderboard') }} →
                            </a>
                        </div>
                    </div>
                @endif
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
                    {{ __('app.no_weekly_challenge') }}
                </h3>
                <p class="text-neutral-600 dark:text-neutral-300 mb-4">
                    {{ __('app.no_weekly_challenge_description') }}
                </p>
            </div>
        @endif

        <!-- Navigation -->
        <div class="mt-8 flex justify-center space-x-4">
            <a href="{{ route('localized.weekly-board.archive', ['locale' => app()->getLocale()]) }}" 
               class="inline-flex items-center px-4 py-2 bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-colors">
                {{ __('app.view_archive') }}
            </a>
            <a href="{{ route('localized.daily-board.index', ['locale' => app()->getLocale()]) }}" 
               class="inline-flex items-center px-4 py-2 bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-colors">
                {{ __('app.daily_board') }}
            </a>
        </div>
    </div>
</x-site-layout>
