<x-site-layout>
    <div class="max-w-6xl mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-neutral-900 dark:text-white mb-2">
                {{ __('app.daily_board_archive') }}
            </h1>
            <p class="text-neutral-600 dark:text-neutral-300">
                {{ __('app.daily_board_archive_description') }}
            </p>
        </div>

        <!-- Navigation -->
        <div class="mb-6 flex justify-between items-center">
            <a href="{{ route('localized.daily-board.index', ['locale' => app()->getLocale()]) }}" 
               class="inline-flex items-center text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300">
                ← {{ __('app.back_to_today') }}
            </a>
            
            <div class="flex space-x-2">
                <a href="{{ route('localized.daily-board.archive', ['locale' => app()->getLocale(), 'date' => $currentDate->copy()->subDays(30)->format('Y-m-d')]) }}" 
                   class="px-3 py-1 text-sm bg-neutral-200 dark:bg-neutral-700 rounded-md hover:bg-neutral-300 dark:hover:bg-neutral-600">
                    {{ __('app.previous_month') }}
                </a>
                @if($currentDate->copy()->addDays(30) <= now())
                    <a href="{{ route('localized.daily-board.archive', ['locale' => app()->getLocale(), 'date' => $currentDate->copy()->addDays(30)->format('Y-m-d')]) }}" 
                       class="px-3 py-1 text-sm bg-neutral-200 dark:bg-neutral-700 rounded-md hover:bg-neutral-300 dark:hover:bg-neutral-600">
                        {{ __('app.next_month') }}
                    </a>
                @endif
            </div>
        </div>

        <!-- Challenges List -->
        @if($challenges->count() > 0)
            <div class="space-y-6">
                @foreach($challenges as $challenge)
                    <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                            <div class="mb-4 lg:mb-0">
                                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2">
                                    {{ $challenge->starts_at->format('l, F j, Y') }}
                                </h3>
                                
                                <div class="flex flex-wrap items-center gap-4 text-sm text-neutral-600 dark:text-neutral-300">
                                    @if($challenge->puzzle)
                                        <span class="flex items-center">
                                            <span class="mr-2">{{ __('app.board.difficulty') }}:</span>
                                            <span class="px-2 py-1 rounded text-xs font-medium
                                                @switch($challenge->puzzle->difficulty ?? 'normal')
                                                    @case('easy') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300 @break
                                                    @case('normal') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 @break
                                                    @case('hard') bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300 @break
                                                    @case('expert') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 @break
                                                    @case('crazy') bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300 @break
                                                @endswitch
                                            ">
                                                {{ ucfirst($challenge->puzzle->difficulty ?? 'normal') }}
                                            </span>
                                        </span>
                                        
                                        <span>{{ __('app.seed') }}: <code class="font-mono">{{ $challenge->puzzle->seed ?? 'N/A' }}</code></span>
                                    @else
                                        <span>{{ __('app.board.difficulty') }}: N/A</span>
                                    @endif
                                    
                                    <span>{{ __('app.participants') }}: {{ (int) $challenge->attempts->where('valid', true)->unique('user_id')->count() }}</span>
                                </div>
                            </div>

                            <div class="flex flex-col lg:flex-row items-start lg:items-center space-y-3 lg:space-y-0 lg:space-x-6">
                                <!-- Top 3 players -->
                                @if($challenge->attempts->count() > 0)
                                    <div class="flex items-center space-x-2">
                                        @foreach($challenge->attempts->take(3) as $index => $attempt)
                                            <div class="flex flex-col items-center">
                                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-medium
                                                    @if($index === 0) bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300
                                                    @elseif($index === 1) bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                                    @else bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300
                                                    @endif">
                                                    {{ $index + 1 }}
                                                </div>
                                                <div class="text-xs text-neutral-600 dark:text-neutral-400 truncate max-w-16">
                                                    {{ $attempt->user?->name ?? '—' }}
                                                </div>
                                                <div class="text-xs font-mono text-neutral-500 dark:text-neutral-500">
                                                    @php($ms = (int) ($attempt->duration_ms ?? 0))
                                                    @php($s = intdiv($ms, 1000))
                                                    {{ sprintf('%02d:%02d', intdiv($s,60), $s%60) }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <!-- Action buttons -->
                                @php($userCompleted = auth()->check() && $challenge->attempts()->where('valid', true)->whereNotNull('completed_at')->where('user_id', auth()->id())->exists())
                                <div class="flex space-x-2">
                                    @if($userCompleted)
                                        <a href="{{ route('localized.leaderboard.show', ['locale' => app()->getLocale(), 'challenge' => $challenge->id]) }}" 
                                           class="px-3 py-1.5 text-xs font-medium bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300 rounded-md hover:bg-primary-200 dark:hover:bg-primary-900/50">
                                            {{ __('app.view_full_leaderboard') }}
                                        </a>
                                        <a href="{{ route('localized.daily-board.show', ['locale' => app()->getLocale(), 'date' => $challenge->starts_at->format('Y-m-d')]) }}" 
                                           class="px-3 py-1.5 text-xs font-medium bg-neutral-100 text-neutral-700 dark:bg-neutral-700 dark:text-neutral-300 rounded-md hover:bg-neutral-200 dark:hover:bg-neutral-600">
                                            {{ __('app.view_details') }}
                                        </a>
                                    @else
                                        <a href="{{ route('localized.daily-board.show', ['locale' => app()->getLocale(), 'date' => $challenge->starts_at->format('Y-m-d')]) }}" 
                                           class="px-3 py-1.5 text-xs font-medium bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300 rounded-md hover:bg-primary-200 dark:hover:bg-primary-900/50">
                                            {{ __('app.play_this_challenge') }}
                                        </a>
                                        <a href="{{ route('localized.leaderboard.show', ['locale' => app()->getLocale(), 'challenge' => $challenge->id]) }}" 
                                           class="px-3 py-1.5 text-xs font-medium bg-neutral-100 text-neutral-700 dark:bg-neutral-700 dark:text-neutral-300 rounded-md hover:bg-neutral-200 dark:hover:bg-neutral-600">
                                            {{ __('app.view_full_leaderboard') }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $challenges->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <div class="max-w-md mx-auto">
                    <div class="mb-4 text-neutral-400 dark:text-neutral-500">
                        <svg class="w-16 h-16 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-neutral-900 dark:text-white mb-2">
                        {{ __('app.no_archived_challenges') }}
                    </h3>
                    <p class="text-neutral-600 dark:text-neutral-300">
                        {{ __('app.no_archived_challenges_description') }}
                    </p>
                </div>
            </div>
        @endif
    </div>
</x-site-layout>