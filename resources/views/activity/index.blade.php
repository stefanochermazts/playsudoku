<x-site-layout class="overflow-x-hidden">
    {{-- Header della pagina --}}
    <div class="bg-white dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="min-w-0 flex-1">
                    <h1 class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white">
                        {{ __('app.activity.title') }}
                    </h1>
                    <p class="mt-2 text-neutral-600 dark:text-neutral-400">
                        {{ __('app.activity.subtitle') }}
                    </p>
                </div>
                
                {{-- Statistiche --}}
                <div class="flex items-center space-x-6">
                    <div class="text-center">
                        <div class="text-xl font-bold text-primary-600 dark:text-primary-400">{{ $stats['recent_activities'] }}</div>
                        <div class="text-xs text-neutral-500">{{ __('app.activity.recent_activities') }}</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xl font-bold text-green-600 dark:text-green-400">{{ $stats['total_friends'] }}</div>
                        <div class="text-xs text-neutral-500">{{ __('app.activity.total_friends') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 lg:gap-8">
            {{-- Sidebar con statistiche --}}
            <div class="lg:col-span-1 space-y-6">
                {{-- Amici più attivi --}}
                @if($stats['active_friends']->count() > 0)
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">
                        {{ __('app.activity.most_active_friends') }}
                    </h2>
                    
                    <div class="space-y-3">
                        @foreach($stats['active_friends'] as $activeFriend)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center">
                                    <span class="text-primary-600 dark:text-primary-400 font-semibold text-sm">
                                        {{ substr($activeFriend->user->name, 0, 1) }}
                                    </span>
                                </div>
                                <div>
                                    <div class="font-medium text-neutral-900 dark:text-white text-sm">{{ $activeFriend->user->name }}</div>
                                </div>
                            </div>
                            <div class="text-sm font-medium text-primary-600 dark:text-primary-400">
                                {{ $activeFriend->activity_count }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Tipi di attività --}}
                @if(count($stats['activity_types']) > 0)
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">
                        {{ __('app.activity.activity_types') }}
                    </h2>
                    
                    <div class="space-y-3">
                        @foreach($stats['activity_types'] as $type => $count)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <span class="text-lg">
                                    @if($type === 'challenge_completed') 🎯
                                    @elseif($type === 'new_personal_record') 🏆
                                    @elseif($type === 'streak_milestone') 🔥
                                    @elseif($type === 'friend_added') 👥
                                    @else 📝
                                    @endif
                                </span>
                                <span class="text-sm text-neutral-700 dark:text-neutral-300">
                                    {{ __('app.activity.type_' . $type) }}
                                </span>
                            </div>
                            <span class="text-sm font-medium text-neutral-600 dark:text-neutral-400">{{ $count }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- Timeline delle attività --}}
            <div class="lg:col-span-3">
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700">
                    <div class="p-6 border-b border-neutral-200 dark:border-neutral-700">
                        <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">
                            {{ __('app.activity.timeline') }}
                        </h2>
                    </div>
                    
                    @if($activities->count() > 0)
                    <div class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @foreach($activities as $activity)
                        <div class="p-6 hover:bg-neutral-50 dark:hover:bg-neutral-750 transition-colors">
                            <div class="flex items-start space-x-4">
                                {{-- Avatar e icona --}}
                                <div class="relative">
                                    <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center">
                                        <span class="text-primary-600 dark:text-primary-400 font-semibold">
                                            {{ substr($activity->user->name, 0, 1) }}
                                        </span>
                                    </div>
                                    <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-white dark:bg-neutral-800 rounded-full flex items-center justify-center border-2 border-neutral-200 dark:border-neutral-700">
                                        <span class="text-sm">{{ $activity->icon }}</span>
                                    </div>
                                </div>
                                
                                {{-- Contenuto --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <p class="text-sm text-neutral-900 dark:text-white">
                                                {{ $activity->localized_description }}
                                            </p>
                                            <p class="text-xs text-neutral-500 mt-1">
                                                {{ $activity->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                        
                                        {{-- Dati aggiuntivi --}}
                                        @if($activity->type === 'challenge_completed' && isset($activity->data['difficulty']))
                                        <div class="ml-4">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                {{ $activity->data['difficulty'] === 'easy' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                                {{ $activity->data['difficulty'] === 'normal' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : '' }}
                                                {{ $activity->data['difficulty'] === 'hard' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                                                {{ $activity->data['difficulty'] === 'expert' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}
                                                {{ $activity->data['difficulty'] === 'crazy' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : '' }}">
                                                {{ __('app.difficulty.' . $activity->data['difficulty']) }}
                                            </span>
                                        </div>
                                        @endif
                                    </div>
                                    
                                    {{-- Dettagli aggiuntivi per record personali --}}
                                    @if($activity->type === 'new_personal_record' && isset($activity->data['previous_best_time']))
                                    <div class="mt-2 p-2 bg-yellow-50 dark:bg-yellow-900/20 rounded-md">
                                        <p class="text-xs text-yellow-800 dark:text-yellow-200">
                                            {{ __('app.activity.previous_best') }}: {{ floor($activity->data['previous_best_time'] / 60000) }}:{{ str_pad(floor(($activity->data['previous_best_time'] % 60000) / 1000), 2, '0', STR_PAD_LEFT) }}
                                        </p>
                                    </div>
                                    @endif
                                    
                                    {{-- Dettagli per streak milestone --}}
                                    @if($activity->type === 'streak_milestone')
                                    <div class="mt-2 p-2 bg-red-50 dark:bg-red-900/20 rounded-md">
                                        <p class="text-xs text-red-800 dark:text-red-200">
                                            {{ __('app.activity.streak_achievement') }}
                                        </p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="p-12 text-center">
                        <div class="w-16 h-16 bg-neutral-100 dark:bg-neutral-700 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-neutral-900 dark:text-white mb-2">{{ __('app.activity.no_activities') }}</h3>
                        <p class="text-neutral-500 mb-4">{{ __('app.activity.no_activities_description') }}</p>
                        <a href="{{ route('localized.friends.index', ['locale' => app()->getLocale()]) }}" 
                           class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 transition-colors">
                            {{ __('app.activity.add_friends') }}
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-site-layout>
