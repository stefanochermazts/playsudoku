<x-site-layout class="overflow-x-hidden">
    {{-- Header della pagina --}}
    <div class="bg-white dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="min-w-0 flex-1">
                    <h1 class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white">
                        {{ __('app.rankings.friends_title') }}
                    </h1>
                    <p class="mt-2 text-neutral-600 dark:text-neutral-400">
                        {{ __('app.rankings.friends_subtitle') }}
                    </p>
                </div>
                
                {{-- Statistiche --}}
                <div class="flex items-center space-x-6">
                    <div class="text-center">
                        <div class="text-xl font-bold text-primary-600 dark:text-primary-400">{{ $stats['total_friends'] }}</div>
                        <div class="text-xs text-neutral-500">{{ __('app.rankings.total_friends') }}</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xl font-bold text-green-600 dark:text-green-400">{{ $stats['active_friends'] }}</div>
                        <div class="text-xs text-neutral-500">{{ __('app.rankings.active_friends') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Filtri --}}
        <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-6 mb-8">
            <form method="GET" class="flex flex-col sm:flex-row gap-4">
                {{-- Filtro periodo --}}
                <div class="flex-1">
                    <label for="type" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                        {{ __('app.rankings.period') }}
                    </label>
                    <select name="type" id="type" onchange="this.form.submit()" 
                            class="w-full rounded-md border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white focus:border-primary-500 focus:ring-primary-500">
                        <option value="overall" {{ $type === 'overall' ? 'selected' : '' }}>{{ __('app.rankings.overall') }}</option>
                        <option value="monthly" {{ $type === 'monthly' ? 'selected' : '' }}>{{ __('app.rankings.monthly') }}</option>
                        <option value="weekly" {{ $type === 'weekly' ? 'selected' : '' }}>{{ __('app.rankings.weekly') }}</option>
                    </select>
                </div>
                
                {{-- Filtro difficoltà --}}
                <div class="flex-1">
                    <label for="difficulty" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                        {{ __('app.rankings.difficulty') }}
                    </label>
                    <select name="difficulty" id="difficulty" onchange="this.form.submit()"
                            class="w-full rounded-md border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white focus:border-primary-500 focus:ring-primary-500">
                        <option value="all" {{ $difficulty === 'all' ? 'selected' : '' }}>{{ __('app.rankings.all_difficulties') }}</option>
                        <option value="easy" {{ $difficulty === 'easy' ? 'selected' : '' }}>{{ __('app.difficulty.easy') }}</option>
                        <option value="normal" {{ $difficulty === 'normal' ? 'selected' : '' }}>{{ __('app.difficulty.normal') }}</option>
                        <option value="hard" {{ $difficulty === 'hard' ? 'selected' : '' }}>{{ __('app.difficulty.hard') }}</option>
                        <option value="expert" {{ $difficulty === 'expert' ? 'selected' : '' }}>{{ __('app.difficulty.expert') }}</option>
                        <option value="crazy" {{ $difficulty === 'crazy' ? 'selected' : '' }}>{{ __('app.difficulty.crazy') }}</option>
                    </select>
                </div>
            </form>
        </div>

        {{-- La tua posizione --}}
        @if($userPosition)
        <div class="bg-gradient-to-r from-primary-50 to-secondary-50 dark:from-primary-900/20 dark:to-secondary-900/20 rounded-lg border border-primary-200 dark:border-primary-800 p-6 mb-8">
            <h2 class="text-lg font-semibold text-primary-900 dark:text-primary-100 mb-4">
                {{ __('app.rankings.your_position') }}
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-primary-600 dark:text-primary-400">#{{ $userPosition['position'] }}</div>
                    <div class="text-sm text-primary-700 dark:text-primary-300">{{ __('app.rankings.position') }}</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ $userPosition['completed_challenges'] }}</div>
                    <div class="text-sm text-primary-700 dark:text-primary-300">{{ __('app.rankings.completed') }}</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ $userPosition['completion_rate'] }}%</div>
                    <div class="text-sm text-primary-700 dark:text-primary-300">{{ __('app.rankings.completion_rate') }}</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                        @if($userPosition['best_time'])
                            {{ floor($userPosition['best_time'] / 60000) }}:{{ str_pad(floor(($userPosition['best_time'] % 60000) / 1000), 2, '0', STR_PAD_LEFT) }}
                        @else
                            --
                        @endif
                    </div>
                    <div class="text-sm text-primary-700 dark:text-primary-300">{{ __('app.rankings.best_time') }}</div>
                </div>
            </div>
        </div>
        @endif

        {{-- Classifica --}}
        <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700">
            <div class="p-6 border-b border-neutral-200 dark:border-neutral-700">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">
                    {{ __('app.rankings.leaderboard') }}
                </h2>
            </div>
            
            @if(count($rankings) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                    <thead class="bg-neutral-50 dark:bg-neutral-750">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                {{ __('app.rankings.position') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                {{ __('app.rankings.player') }}
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                {{ __('app.rankings.completed') }}
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                {{ __('app.rankings.completion_rate') }}
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                {{ __('app.rankings.best_time') }}
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                {{ __('app.rankings.avg_time') }}
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                {{ __('app.rankings.actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-neutral-800 divide-y divide-neutral-200 dark:divide-neutral-700">
                        @foreach($rankings as $ranking)
                        <tr class="{{ $ranking['user']->id === auth()->id() ? 'bg-primary-50 dark:bg-primary-900/20' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if($ranking['position'] <= 3)
                                        <div class="flex items-center justify-center w-8 h-8 rounded-full {{ $ranking['position'] === 1 ? 'bg-yellow-100 text-yellow-800' : ($ranking['position'] === 2 ? 'bg-gray-100 text-gray-800' : 'bg-orange-100 text-orange-800') }}">
                                            @if($ranking['position'] === 1) 🥇
                                            @elseif($ranking['position'] === 2) 🥈
                                            @else 🥉
                                            @endif
                                        </div>
                                    @else
                                        <div class="w-8 h-8 flex items-center justify-center text-lg font-bold text-neutral-600 dark:text-neutral-400">
                                            {{ $ranking['position'] }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center">
                                        <span class="text-primary-600 dark:text-primary-400 font-semibold">
                                            {{ substr($ranking['user']->name, 0, 1) }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="font-medium text-neutral-900 dark:text-white">
                                            {{ $ranking['user']->name }}
                                            @if($ranking['user']->id === auth()->id())
                                                <span class="text-sm text-primary-600 dark:text-primary-400">({{ __('app.rankings.you') }})</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="text-sm font-medium text-neutral-900 dark:text-white">{{ $ranking['completed_challenges'] }}</div>
                                <div class="text-xs text-neutral-500">{{ __('app.rankings.of') }} {{ $ranking['total_challenges'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="text-sm font-medium text-neutral-900 dark:text-white">{{ $ranking['completion_rate'] }}%</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="text-sm font-medium text-neutral-900 dark:text-white">
                                    @if($ranking['best_time'])
                                        {{ floor($ranking['best_time'] / 60000) }}:{{ str_pad(floor(($ranking['best_time'] % 60000) / 1000), 2, '0', STR_PAD_LEFT) }}
                                    @else
                                        --
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="text-sm font-medium text-neutral-900 dark:text-white">
                                    @if($ranking['avg_time'])
                                        {{ floor($ranking['avg_time'] / 60000) }}:{{ str_pad(floor(($ranking['avg_time'] % 60000) / 1000), 2, '0', STR_PAD_LEFT) }}
                                    @else
                                        --
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($ranking['user']->id !== auth()->id())
                                <a href="{{ route('localized.friends.compare', ['locale' => app()->getLocale(), 'friend' => $ranking['user']]) }}" 
                                   class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-primary-700 bg-primary-100 hover:bg-primary-200 dark:text-primary-400 dark:bg-primary-900/30 dark:hover:bg-primary-900/50 transition-colors">
                                    {{ __('app.rankings.compare') }}
                                </a>
                                @else
                                <span class="text-sm text-neutral-500">{{ __('app.rankings.you') }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-neutral-100 dark:bg-neutral-700 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-neutral-900 dark:text-white mb-2">{{ __('app.rankings.no_data') }}</h3>
                <p class="text-neutral-500 mb-4">{{ __('app.rankings.no_data_description') }}</p>
            </div>
            @endif
        </div>
    </div>
</x-site-layout>
