<x-site-layout>
    <div class="max-w-6xl mx-auto px-4 py-8">
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-neutral-900 dark:text-white mb-2">
                        {{ __('app.daily_board') }} - {{ $targetDate->format('l, F j, Y') }}
                    </h1>
                    <p class="text-neutral-600 dark:text-neutral-300">
                        {{ __('app.detailed_stats_for_date') }}
                    </p>
                </div>
                
                <a href="{{ route('localized.daily-board.archive', ['locale' => app()->getLocale()]) }}" 
                   class="inline-flex items-center text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300">
                    ← {{ __('app.back_to_archive') }}
                </a>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Challenge Info -->
            <div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
                <h2 class="text-xl font-semibold text-neutral-900 dark:text-white mb-4">
                    {{ __('app.challenge_details') }}
                </h2>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-neutral-600 dark:text-neutral-300">{{ __('app.board.difficulty') }}:</span>
                        <span class="px-2 py-1 rounded text-sm font-medium
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
                    </div>

                    <div class="flex justify-between">
                        <span class="text-neutral-600 dark:text-neutral-300">{{ __('app.puzzle_seed') }}:</span>
                        <span class="font-mono text-neutral-900 dark:text-white">{{ $challenge->puzzle->seed }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-neutral-600 dark:text-neutral-300">{{ __('app.participants') }}:</span>
                        <span class="font-semibold text-neutral-900 dark:text-white">{{ $stats['total_participants'] }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-neutral-600 dark:text-neutral-300">{{ __('app.completions') }}:</span>
                        <span class="font-semibold text-neutral-900 dark:text-white">{{ $stats['completion_rate'] }}</span>
                    </div>

                    @if($stats['fastest_time'])
                        <div class="flex justify-between">
                            <span class="text-neutral-600 dark:text-neutral-300">{{ __('app.fastest_time') }}:</span>
                            <span class="font-mono text-green-600 dark:text-green-400">
                                @php($ms = (int) $stats['fastest_time'])
                                @php($s = intdiv($ms, 1000))
                                @php($cs = intdiv($ms % 1000, 10))
                                {{ sprintf('%02d:%02d.%02d', intdiv($s,60), $s%60, $cs) }}
                            </span>
                        </div>
                    @endif

                    @if($stats['average_time'])
                        <div class="flex justify-between">
                            <span class="text-neutral-600 dark:text-neutral-300">{{ __('app.average_time') }}:</span>
                            <span class="font-mono text-neutral-600 dark:text-neutral-300">
                                @php($ms = (int) $stats['average_time'])
                                @php($s = intdiv($ms, 1000))
                                @php($cs = intdiv($ms % 1000, 10))
                                {{ sprintf('%02d:%02d.%02d', intdiv($s,60), $s%60, $cs) }}
                            </span>
                        </div>
                    @endif
                </div>

                <div class="pt-4 mt-4 border-t border-neutral-200 dark:border-neutral-700">
                    <a href="{{ route('localized.challenges.play', ['locale' => app()->getLocale(), 'challenge' => $challenge->id]) }}" 
                       class="w-full inline-flex justify-center items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                        {{ __('app.play_this_challenge') }}
                    </a>
                </div>
            </div>

            <!-- Leaderboard -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-neutral-900 dark:text-white">
                            {{ __('app.complete_leaderboard') }}
                        </h2>
                        <a href="{{ route('localized.leaderboard.export', ['locale' => app()->getLocale(), 'challenge' => $challenge->id]) }}" 
                           class="inline-flex items-center px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-sm rounded transition-colors">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            {{ __('app.export_csv') }}
                        </a>
                    </div>

                    @if($leaderboard->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead class="bg-neutral-50 dark:bg-neutral-900/50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-neutral-600 dark:text-neutral-300 uppercase">{{ __('app.rank') }}</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-neutral-600 dark:text-neutral-300 uppercase">{{ __('app.player') }}</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-neutral-600 dark:text-neutral-300 uppercase">{{ __('app.time') }}</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-neutral-600 dark:text-neutral-300 uppercase">{{ __('app.errors') }}</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-neutral-600 dark:text-neutral-300 uppercase">{{ __('app.hints') }}</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-neutral-600 dark:text-neutral-300 uppercase">{{ __('app.completed_at') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                                    @foreach($leaderboard as $index => $attempt)
                                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-900/30">
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <span class="font-semibold">#{{ $index + 1 }}</span>
                                                    @if($index === 0)
                                                        <span class="ml-2">🥇</span>
                                                    @elseif($index === 1)
                                                        <span class="ml-2">🥈</span>
                                                    @elseif($index === 2)
                                                        <span class="ml-2">🥉</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-neutral-900 dark:text-white">
                                                {{ $attempt->user?->name ?? '—' }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap font-mono">
                                                {{ $attempt->getFormattedDuration() }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">{{ $attempt->errors_count }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap">{{ $attempt->hints_used }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-neutral-500">
                                                {{ $attempt->completed_at?->format('H:i') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6">
                            {{ $leaderboard->links() }}
                        </div>
                    @else
                        <div class="text-center py-8 text-neutral-500 dark:text-neutral-400">
                            {{ __('app.no_completions_yet') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-site-layout>
