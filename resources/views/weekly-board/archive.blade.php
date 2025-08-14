<x-site-layout>
    <div class="max-w-6xl mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-neutral-900 dark:text-white mb-2">
                {{ __('app.weekly_board_archive') }}
            </h1>
            <p class="text-neutral-600 dark:text-neutral-300">
                {{ __('app.weekly_board_archive_description') }}
            </p>
        </div>

        <!-- Navigation -->
        <div class="mb-6 flex justify-between items-center">
            <a href="{{ route('localized.weekly-board.index', ['locale' => app()->getLocale()]) }}" 
               class="inline-flex items-center text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300">
                ← {{ __('app.back_to_this_week') }}
            </a>
            
            <div class="flex space-x-2">
                <a href="{{ route('localized.weekly-board.archive', ['locale' => app()->getLocale(), 'week' => $currentWeek->copy()->subWeeks(12)->format('Y-m-d')]) }}" 
                   class="px-3 py-1 text-sm bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded hover:bg-neutral-50 dark:hover:bg-neutral-700">
                    {{ __('app.previous_period') }}
                </a>
                @if($currentWeek->format('Y-m-d') < now()->startOfWeek()->format('Y-m-d'))
                    <a href="{{ route('localized.weekly-board.archive', ['locale' => app()->getLocale(), 'week' => $currentWeek->copy()->addWeeks(12)->format('Y-m-d')]) }}" 
                       class="px-3 py-1 text-sm bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded hover:bg-neutral-50 dark:hover:bg-neutral-700">
                        {{ __('app.next_period') }}
                    </a>
                @endif
            </div>
        </div>

        @if($challenges->count() > 0)
            <div class="grid gap-6">
                @foreach($challenges as $challenge)
                    <div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between">
                            <div class="mb-4 lg:mb-0">
                                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2">
                                    {{ __('app.week_of') }} {{ $challenge->starts_at->format('F j') }} - {{ $challenge->ends_at->format('F j, Y') }}
                                </h3>
                                
                                <div class="flex flex-wrap items-center gap-4 text-sm text-neutral-600 dark:text-neutral-300">
                                    <span class="flex items-center">
                                        <span class="mr-2">{{ __('app.difficulty') }}:</span>
                                        <span class="px-2 py-1 rounded text-xs font-medium
                                            @switch($challenge->puzzle->difficulty)
                                                @case('easy') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300 @break
                                                @case('normal') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 @break
                                                @case('hard') bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300 @break
                                                @case('expert') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 @break
                                                @case('crazy') bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300 @break
                                            @endswitch
                                        ">
                                            {{ ucfirst($challenge->puzzle->difficulty) }}
                                        </span>
                                    </span>
                                    
                                    <span>{{ __('app.seed') }}: <code class="font-mono">{{ $challenge->puzzle->seed }}</code></span>
                                    
                                    <span>{{ __('app.participants') }}: {{ $challenge->attempts->where('valid', true)->unique('user_id')->count() }}</span>
                                </div>
                            </div>

                            <div class="flex flex-col lg:flex-row items-start lg:items-center space-y-3 lg:space-y-0 lg:space-x-6">
                                <!-- Top 5 players -->
                                @if($challenge->attempts->count() > 0)
                                    <div class="flex space-x-3">
                                        @foreach($challenge->attempts->take(5) as $index => $attempt)
                                            <div class="text-center">
                                                <div class="w-8 h-8 flex items-center justify-center rounded-full text-xs font-medium mb-1
                                                    @if($index === 0) bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300
                                                    @elseif($index === 1) bg-neutral-200 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-200
                                                    @elseif($index === 2) bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300
                                                    @else bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400 @endif
                                                ">
                                                    {{ $index + 1 }}
                                                </div>
                                                <div class="text-xs text-neutral-600 dark:text-neutral-400 truncate max-w-16">
                                                    {{ $attempt->user?->name ?? '—' }}
                                                </div>
                                                <div class="text-xs font-mono text-neutral-500 dark:text-neutral-500">
                                                    @php($ms = (int) ($attempt->duration_ms ?? 0))
                                                    @php($s = intdiv($ms, 1000))
                                                    @php($cs = intdiv($ms % 1000, 10))
                                                    {{ sprintf('%02d:%02d', intdiv($s,60), $s%60) }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <!-- Actions -->
                                <div class="flex space-x-2">
                                    <a href="{{ route('localized.weekly-board.show', ['locale' => app()->getLocale(), 'week' => $challenge->starts_at->format('Y-m-d')]) }}" 
                                       class="px-3 py-1 text-sm bg-primary-600 hover:bg-primary-700 text-white rounded transition-colors">
                                        {{ __('app.view_details') }}
                                    </a>
                                    <a href="{{ route('localized.leaderboard.show', ['locale' => app()->getLocale(), 'challenge' => $challenge->id]) }}" 
                                       class="px-3 py-1 text-sm bg-white dark:bg-neutral-700 border border-neutral-200 dark:border-neutral-600 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-600 rounded transition-colors">
                                        {{ __('app.leaderboard') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $challenges->links() }}
            </div>
        @else
            <div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-8 text-center">
                <div class="text-neutral-400 dark:text-neutral-500 mb-4">
                    <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-neutral-900 dark:text-white mb-2">
                    {{ __('app.no_archived_challenges') }}
                </h3>
                <p class="text-neutral-600 dark:text-neutral-300">
                    {{ __('app.no_archived_challenges_description') }}
                </p>
            </div>
        @endif
    </div>
</x-site-layout>
