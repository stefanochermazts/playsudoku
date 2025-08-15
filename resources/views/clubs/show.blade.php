<x-site-layout class="overflow-x-hidden">
    {{-- Success Message --}}
    @if (session('success'))
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-6 mx-4 sm:mx-6 lg:mx-8 mt-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800 dark:text-green-200">
                        {{ session('success') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Header del club --}}
    <div class="bg-white dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <h1 class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white truncate">
                        {{ $club->name }}
                    </h1>
                    <div class="mt-2 flex flex-wrap items-center gap-4 text-sm text-neutral-600 dark:text-neutral-400">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.916-.75l.916.75zM9 12a4 4 0 008 0 4 4 0 00-8 0zM5 20v-2a7 7 0 0110-6.326"></path>
                            </svg>
                            {{ $club->activeMembers->count() }} {{ __('app.clubs.members') }}
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            {{ __('app.clubs.visibility_' . $club->visibility) }}
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ __('app.clubs.created_at') }} {{ $club->created_at->format('M Y') }}
                        </div>
                    </div>
                </div>
                
                {{-- Azioni --}}
                <div class="flex flex-col sm:flex-row gap-2">
                    @if($canManage)
                        <a href="{{ route('localized.clubs.edit', ['locale' => app()->getLocale(), 'club' => $club->slug]) }}" 
                           class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 transition-colors text-center">
                            {{ __('app.clubs.manage_club') }}
                        </a>
                    @endif
                    
                    @if($canInvite)
                        <a href="{{ route('localized.clubs.invite.form', ['locale' => app()->getLocale(), 'club' => $club->slug]) }}" 
                           class="px-4 py-2 bg-secondary-600 text-white rounded-md hover:bg-secondary-700 transition-colors text-center">
                            {{ __('app.clubs.invite_friends') }}
                        </a>
                    @endif
                    
                    @if($canJoin)
                        <form method="POST" action="{{ route('localized.clubs.join', ['locale' => app()->getLocale(), 'club' => $club->slug]) }}" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                                {{ __('app.clubs.join_club') }}
                            </button>
                        </form>
                    @endif
                    
                    @if($canLeave)
                        <form method="POST" action="{{ route('localized.clubs.leave', ['locale' => app()->getLocale(), 'club' => $club->slug]) }}" class="inline">
                            @csrf
                            <button type="submit" 
                                    onclick="return confirm('{{ __('app.clubs.leave_confirm') }}')"
                                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                                {{ __('app.clubs.leave_club') }}
                            </button>
                        </form>
                    @endif
                    
                    <a href="{{ route('localized.clubs.index', ['locale' => app()->getLocale()]) }}" 
                       class="px-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-colors text-center">
                        {{ __('app.clubs.back_to_clubs') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Informazioni principali --}}
            <div class="lg:col-span-2 space-y-8">
                {{-- Descrizione --}}
                @if($club->description)
                    <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                        <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">
                            {{ __('app.clubs.description') }}
                        </h2>
                        <p class="text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            {{ $club->description }}
                        </p>
                    </div>
                @endif

                {{-- Membri --}}
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-6">
                        {{ __('app.clubs.members') }} ({{ $club->activeMembers->count() }})
                    </h2>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Proprietario --}}
                        <div class="flex items-center p-4 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-800">
                            <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M9.293 2.293a1 1 0 011.414 0l7 7A1 1 0 0117 11h-1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-3a1 1 0 00-1-1H9a1 1 0 00-1 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-6H3a1 1 0 01-.707-1.707l7-7z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3 flex-1 min-w-0">
                                <p class="text-sm font-medium text-amber-900 dark:text-amber-100 truncate">
                                    {{ $club->owner->name }}
                                </p>
                                <p class="text-xs text-amber-700 dark:text-amber-300">
                                    {{ __('app.clubs.owner') }}
                                </p>
                            </div>
                        </div>

                        {{-- Altri membri --}}
                        @foreach($club->activeMembers->where('user_id', '!=', $club->owner_id) as $membership)
                            <div class="flex items-center p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                                <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3 flex-1 min-w-0">
                                    <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100 truncate">
                                        {{ $membership->user->name }}
                                    </p>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                        {{ __('app.clubs.member_since') }} {{ $membership->joined_at->format('M Y') }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Sidebar con statistiche --}}
            <div class="space-y-6">
                {{-- Statistiche club --}}
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">
                        {{ __('app.clubs.statistics') }}
                    </h3>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-neutral-600 dark:text-neutral-400">{{ __('app.clubs.total_members') }}</span>
                            <span class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $stats['total_members'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-neutral-600 dark:text-neutral-400">{{ __('app.clubs.recent_joins') }}</span>
                            <span class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $stats['recent_joins'] ?? 0 }}</span>
                        </div>
                        @if($club->max_members)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-neutral-600 dark:text-neutral-400">{{ __('app.clubs.max_members') }}</span>
                                <span class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $club->max_members }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Informazioni club --}}
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">
                        {{ __('app.clubs.club_info') }}
                    </h3>
                    
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">{{ __('app.clubs.visibility') }}</p>
                            <p class="text-sm text-neutral-900 dark:text-neutral-100">{{ __('app.clubs.visibility_' . $club->visibility) }}</p>
                        </div>
                        
                        <div>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">{{ __('app.clubs.created_at') }}</p>
                            <p class="text-sm text-neutral-900 dark:text-neutral-100">{{ $club->created_at->format('d M Y') }}</p>
                        </div>
                        
                        @if($club->slug)
                            <div>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">{{ __('app.clubs.club_id') }}</p>
                                <p class="text-sm text-neutral-900 dark:text-neutral-100 font-mono">{{ $club->slug }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-site-layout>
