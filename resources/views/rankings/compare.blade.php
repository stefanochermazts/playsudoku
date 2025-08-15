<x-site-layout class="overflow-x-hidden">
    {{-- Header della pagina --}}
    <div class="bg-white dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white">
                        {{ __('app.rankings.compare_title') }}
                    </h1>
                    <p class="mt-2 text-neutral-600 dark:text-neutral-400">
                        {{ $user->name }} {{ __('app.rankings.vs') }} {{ $friend->name }}
                    </p>
                </div>
                <a href="{{ route('localized.friends.ranking', ['locale' => app()->getLocale()]) }}" 
                   class="px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-colors">
                    {{ __('app.rankings.back_to_ranking') }}
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Head-to-Head Summary --}}
        @if($comparison['head_to_head']['total_common'] > 0)
        <div class="bg-gradient-to-r from-primary-50 to-secondary-50 dark:from-primary-900/20 dark:to-secondary-900/20 rounded-lg border border-primary-200 dark:border-primary-800 p-6 mb-8">
            <h2 class="text-lg font-semibold text-primary-900 dark:text-primary-100 mb-4">
                {{ __('app.rankings.head_to_head') }}
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ $comparison['head_to_head']['total_common'] }}</div>
                    <div class="text-sm text-primary-700 dark:text-primary-300">{{ __('app.rankings.common_challenges') }}</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $comparison['head_to_head']['user1_wins'] }}</div>
                    <div class="text-sm text-green-700 dark:text-green-300">{{ $user->name }} {{ __('app.rankings.wins') }}</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $comparison['head_to_head']['user2_wins'] }}</div>
                    <div class="text-sm text-blue-700 dark:text-blue-300">{{ $friend->name }} {{ __('app.rankings.wins') }}</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-neutral-600 dark:text-neutral-400">{{ $comparison['head_to_head']['ties'] }}</div>
                    <div class="text-sm text-neutral-700 dark:text-neutral-300">{{ __('app.rankings.ties') }}</div>
                </div>
            </div>
        </div>
        @endif

        {{-- Statistiche Generali --}}
        <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 mb-8">
            <div class="p-6 border-b border-neutral-200 dark:border-neutral-700">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">
                    {{ __('app.rankings.general_stats') }}
                </h2>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    {{-- Utente corrente --}}
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                <span class="text-green-600 dark:text-green-400 font-semibold">
                                    {{ substr($user->name, 0, 1) }}
                                </span>
                            </div>
                            <div>
                                <h3 class="font-semibold text-neutral-900 dark:text-white">{{ $user->name }}</h3>
                                <p class="text-sm text-neutral-500">{{ __('app.rankings.you') }}</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center p-3 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                                <div class="text-xl font-bold text-neutral-900 dark:text-white">{{ $comparison['general']['user1']['completed'] }}</div>
                                <div class="text-sm text-neutral-500">{{ __('app.rankings.completed') }}</div>
                            </div>
                            <div class="text-center p-3 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                                <div class="text-xl font-bold text-neutral-900 dark:text-white">{{ $comparison['general']['user1']['completion_rate'] }}%</div>
                                <div class="text-sm text-neutral-500">{{ __('app.rankings.completion_rate') }}</div>
                            </div>
                            <div class="text-center p-3 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                                <div class="text-xl font-bold text-neutral-900 dark:text-white">
                                    @if($comparison['general']['user1']['best_time'])
                                        {{ floor($comparison['general']['user1']['best_time'] / 60000) }}:{{ str_pad(floor(($comparison['general']['user1']['best_time'] % 60000) / 1000), 2, '0', STR_PAD_LEFT) }}
                                    @else
                                        --
                                    @endif
                                </div>
                                <div class="text-sm text-neutral-500">{{ __('app.rankings.best_time') }}</div>
                            </div>
                            <div class="text-center p-3 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                                <div class="text-xl font-bold text-neutral-900 dark:text-white">
                                    @if($comparison['general']['user1']['avg_time'])
                                        {{ floor($comparison['general']['user1']['avg_time'] / 60000) }}:{{ str_pad(floor(($comparison['general']['user1']['avg_time'] % 60000) / 1000), 2, '0', STR_PAD_LEFT) }}
                                    @else
                                        --
                                    @endif
                                </div>
                                <div class="text-sm text-neutral-500">{{ __('app.rankings.avg_time') }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Amico --}}
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                <span class="text-blue-600 dark:text-blue-400 font-semibold">
                                    {{ substr($friend->name, 0, 1) }}
                                </span>
                            </div>
                            <div>
                                <h3 class="font-semibold text-neutral-900 dark:text-white">{{ $friend->name }}</h3>
                                <p class="text-sm text-neutral-500">{{ __('app.rankings.friend') }}</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center p-3 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                                <div class="text-xl font-bold text-neutral-900 dark:text-white">{{ $comparison['general']['user2']['completed'] }}</div>
                                <div class="text-sm text-neutral-500">{{ __('app.rankings.completed') }}</div>
                            </div>
                            <div class="text-center p-3 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                                <div class="text-xl font-bold text-neutral-900 dark:text-white">{{ $comparison['general']['user2']['completion_rate'] }}%</div>
                                <div class="text-sm text-neutral-500">{{ __('app.rankings.completion_rate') }}</div>
                            </div>
                            <div class="text-center p-3 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                                <div class="text-xl font-bold text-neutral-900 dark:text-white">
                                    @if($comparison['general']['user2']['best_time'])
                                        {{ floor($comparison['general']['user2']['best_time'] / 60000) }}:{{ str_pad(floor(($comparison['general']['user2']['best_time'] % 60000) / 1000), 2, '0', STR_PAD_LEFT) }}
                                    @else
                                        --
                                    @endif
                                </div>
                                <div class="text-sm text-neutral-500">{{ __('app.rankings.best_time') }}</div>
                            </div>
                            <div class="text-center p-3 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                                <div class="text-xl font-bold text-neutral-900 dark:text-white">
                                    @if($comparison['general']['user2']['avg_time'])
                                        {{ floor($comparison['general']['user2']['avg_time'] / 60000) }}:{{ str_pad(floor(($comparison['general']['user2']['avg_time'] % 60000) / 1000), 2, '0', STR_PAD_LEFT) }}
                                    @else
                                        --
                                    @endif
                                </div>
                                <div class="text-sm text-neutral-500">{{ __('app.rankings.avg_time') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Confronto per Difficoltà --}}
        <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700">
            <div class="p-6 border-b border-neutral-200 dark:border-neutral-700">
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">
                    {{ __('app.rankings.by_difficulty') }}
                </h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                    <thead class="bg-neutral-50 dark:bg-neutral-750">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                {{ __('app.rankings.difficulty') }}
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                {{ $user->name }} - {{ __('app.rankings.completed') }}
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                {{ $friend->name }} - {{ __('app.rankings.completed') }}
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                {{ __('app.rankings.best_time_comparison') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-neutral-800 divide-y divide-neutral-200 dark:divide-neutral-700">
                        @foreach(['easy', 'normal', 'hard', 'expert', 'crazy'] as $difficulty)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full 
                                    {{ $difficulty === 'easy' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                    {{ $difficulty === 'normal' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : '' }}
                                    {{ $difficulty === 'hard' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                                    {{ $difficulty === 'expert' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}
                                    {{ $difficulty === 'crazy' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : '' }}">
                                    {{ __('app.difficulty.' . $difficulty) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="text-sm font-medium text-neutral-900 dark:text-white">
                                    {{ $comparison['by_difficulty'][$difficulty]['user1']['completed'] ?? 0 }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="text-sm font-medium text-neutral-900 dark:text-white">
                                    {{ $comparison['by_difficulty'][$difficulty]['user2']['completed'] ?? 0 }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @php
                                    $user1Time = $comparison['by_difficulty'][$difficulty]['user1']['best_time'] ?? null;
                                    $user2Time = $comparison['by_difficulty'][$difficulty]['user2']['best_time'] ?? null;
                                @endphp
                                
                                @if($user1Time && $user2Time)
                                    @if($user1Time < $user2Time)
                                        <span class="text-green-600 dark:text-green-400 font-medium">{{ $user->name }}</span>
                                    @elseif($user2Time < $user1Time)
                                        <span class="text-blue-600 dark:text-blue-400 font-medium">{{ $friend->name }}</span>
                                    @else
                                        <span class="text-neutral-500">{{ __('app.rankings.tie') }}</span>
                                    @endif
                                @elseif($user1Time)
                                    <span class="text-green-600 dark:text-green-400 font-medium">{{ $user->name }}</span>
                                @elseif($user2Time)
                                    <span class="text-blue-600 dark:text-blue-400 font-medium">{{ $friend->name }}</span>
                                @else
                                    <span class="text-neutral-500">--</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-site-layout>
