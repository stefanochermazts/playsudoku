<x-site-layout>
    {{-- Header della pagina --}}
    <div class="bg-white dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">
                        {{ __('app.clubs.title') }}
                    </h1>
                    <p class="mt-2 text-neutral-600 dark:text-neutral-400">
                        {{ __('app.clubs.subtitle') }}
                    </p>
                </div>
                
                <div class="flex gap-3">
                    <a href="{{ route('localized.clubs.explore', ['locale' => app()->getLocale()]) }}"
                       class="inline-flex items-center px-4 py-2 bg-secondary-600 text-white rounded-lg hover:bg-secondary-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        {{ __('app.clubs.explore_clubs') }}
                    </a>
                    <a href="{{ route('localized.clubs.create', ['locale' => app()->getLocale()]) }}"
                       class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        {{ __('app.clubs.create_club') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid lg:grid-cols-3 gap-8">
            {{-- Sidebar --}}
            <div class="lg:col-span-1 space-y-6">
                {{-- Inviti in attesa --}}
                @if($invites->count() > 0)
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">
                        {{ __('app.clubs.club_invites') }} ({{ $invites->count() }})
                    </h2>
                    
                    <div class="space-y-3">
                        @foreach($invites as $club)
                        <div class="flex items-center justify-between p-3 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                            <div>
                                <div class="font-medium text-neutral-900 dark:text-white">{{ $club->name }}</div>
                                <div class="text-sm text-neutral-500">{{ $club->members_count }} {{ __('app.clubs.members') }}</div>
                            </div>
                            <div class="flex space-x-2">
                                <button onclick="acceptClubInvite({{ $club->pivot->id }})"
                                        class="px-3 py-1 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm">
                                    {{ __('app.clubs.accept_invite') }}
                                </button>
                                <button onclick="declineClubInvite({{ $club->pivot->id }})"
                                        class="px-3 py-1 bg-neutral-600 text-white rounded-md hover:bg-neutral-700 text-sm">
                                    {{ __('app.clubs.decline_invite') }}
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Club suggeriti --}}
                @if($suggestedClubs->count() > 0)
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">
                        {{ __('app.clubs.suggested_clubs') }}
                    </h2>
                    
                    <div class="space-y-3">
                        @foreach($suggestedClubs as $club)
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-medium text-neutral-900 dark:text-white">{{ $club->name }}</div>
                                <div class="text-sm text-neutral-500">{{ $club->members_count }} {{ __('app.clubs.members') }}</div>
                            </div>
                            <a href="{{ route('localized.clubs.show', ['locale' => app()->getLocale(), 'club' => $club->slug]) }}"
                               class="px-3 py-1 bg-primary-600 text-white rounded-md hover:bg-primary-700 text-sm">
                                {{ __('app.clubs.view_club') }}
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- Contenuto principale --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- I miei club --}}
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700">
                    <div class="p-6 border-b border-neutral-200 dark:border-neutral-700">
                        <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">
                            {{ __('app.clubs.my_clubs') }} ({{ $myClubs->count() }})
                        </h2>
                    </div>
                    
                    @if($myClubs->count() > 0)
                    <div class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @foreach($myClubs as $club)
                        <div class="p-6 flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center">
                                    <span class="text-primary-600 dark:text-primary-400 font-semibold">
                                        {{ substr($club->name, 0, 1) }}
                                    </span>
                                </div>
                                <div>
                                    <h3 class="font-medium text-neutral-900 dark:text-white">{{ $club->name }}</h3>
                                    <p class="text-sm text-neutral-500">
                                        {{ $club->members_count }} {{ __('app.clubs.members') }} • 
                                        @if($club->pivot->role === 'owner')
                                            {{ __('app.clubs.owner') }}
                                        @elseif($club->pivot->role === 'admin')
                                            {{ __('app.clubs.admins') }}
                                        @else
                                            {{ __('app.clubs.members') }}
                                        @endif
                                    </p>
                                    @if($club->description)
                                    <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">{{ Str::limit($club->description, 100) }}</p>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="flex space-x-2">
                                <a href="{{ route('localized.clubs.show', ['locale' => app()->getLocale(), 'club' => $club->slug]) }}"
                                   class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700">
                                    {{ __('app.clubs.view_club') }}
                                </a>
                                @if($club->pivot->role === 'owner')
                                <a href="{{ route('localized.clubs.edit', ['locale' => app()->getLocale(), 'club' => $club->slug]) }}"
                                   class="px-4 py-2 bg-neutral-600 text-white rounded-md hover:bg-neutral-700">
                                    {{ __('app.clubs.manage_club') }}
                                </a>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="p-12 text-center">
                        <div class="w-16 h-16 bg-neutral-100 dark:bg-neutral-700 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-neutral-900 dark:text-white mb-2">{{ __('app.clubs.no_clubs_yet') }}</h3>
                        <p class="text-neutral-500 mb-4">{{ __('app.clubs.no_clubs_description') }}</p>
                        <a href="{{ route('localized.clubs.create', ['locale' => app()->getLocale()]) }}"
                           class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                            {{ __('app.clubs.create_club') }}
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        {{-- Sezione Club Pubblici --}}
        @if($publicClubs->count() > 0)
        <div class="mt-12">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-neutral-900 dark:text-white">
                        {{ __('app.clubs.public_clubs') }}
                    </h2>
                    <p class="text-neutral-600 dark:text-neutral-400">
                        {{ __('app.clubs.public_clubs_subtitle') }}
                    </p>
                </div>
                <a href="{{ route('localized.clubs.explore', ['locale' => app()->getLocale()]) }}"
                   class="text-primary-600 hover:text-primary-700 font-medium">
                    {{ __('app.clubs.view_all') }} →
                </a>
            </div>
            
            {{-- Barra di ricerca rapida --}}
            <div class="mb-6">
                <form method="GET" action="{{ route('localized.clubs.index', ['locale' => app()->getLocale()]) }}" class="max-w-md">
                    <div class="relative">
                        <input type="text" 
                               name="search" 
                               value="{{ $search }}"
                               placeholder="{{ __('app.clubs.search_clubs') }}"
                               class="w-full pl-10 pr-4 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-neutral-700 dark:text-white">
                        <svg class="absolute left-3 top-2.5 h-5 w-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </form>
            </div>
            
            {{-- Grid dei club pubblici --}}
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($publicClubs as $club)
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white truncate">
                                {{ $club->name }}
                            </h3>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                                {{ __('app.clubs.by') }} {{ $club->owner->name }}
                            </p>
                        </div>
                        <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded-full">
                            {{ __('app.clubs.visibility_public') }}
                        </span>
                    </div>
                    
                    @if($club->description)
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-4 line-clamp-2">
                            {{ Str::limit($club->description, 100) }}
                        </p>
                    @endif
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4 text-sm text-neutral-500">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.916-.75l.916.75zM9 12a4 4 0 008 0 4 4 0 00-8 0zM5 20v-2a7 7 0 0110-6.326"></path>
                                </svg>
                                {{ $club->activeMembers->count() }}
                                @if($club->max_members)
                                    /{{ $club->max_members }}
                                @endif
                            </div>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ $club->created_at->format('M Y') }}
                            </div>
                        </div>
                        
                        <div class="flex space-x-2">
                            <a href="{{ route('localized.clubs.show', ['locale' => app()->getLocale(), 'club' => $club->slug]) }}"
                               class="px-3 py-1 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-colors">
                                {{ __('app.clubs.view') }}
                            </a>
                            @if($club->canJoin)
                                <form method="POST" action="{{ route('localized.clubs.join', ['locale' => app()->getLocale(), 'club' => $club->slug]) }}" class="inline">
                                    @csrf
                                    <button type="submit"
                                            class="px-3 py-1 text-sm bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                                        {{ __('app.clubs.join') }}
                                    </button>
                                </form>
                            @else
                                <span class="px-3 py-1 text-sm bg-neutral-100 text-neutral-500 dark:bg-neutral-700 dark:text-neutral-400 rounded-md">
                                    {{ __('app.clubs.full') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    @push('scripts')
    <script>
        // Funzioni per gestire gli inviti ai club
        async function acceptClubInvite(membershipId) {
            try {
                const response = await fetch(`/api/clubs/accept-invite/${membershipId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    location.reload(); // Ricarica la pagina per aggiornare la lista
                } else {
                    showNotification(data.message, 'error');
                }
            } catch (error) {
                showNotification('{{ __("app.clubs.accept_error") }}', 'error');
            }
        }
        
        async function declineClubInvite(membershipId) {
            try {
                const response = await fetch(`/api/clubs/decline-invite/${membershipId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    location.reload();
                } else {
                    showNotification(data.message, 'error');
                }
            } catch (error) {
                showNotification('{{ __("app.clubs.decline_error") }}', 'error');
            }
        }
        
        // Sistema di notifiche semplice
        function showNotification(message, type = 'info') {
            if (type === 'success') {
                alert('✓ ' + message);
            } else if (type === 'error') {
                alert('✗ ' + message);
            } else {
                alert(message);
            }
        }
    </script>
    @endpush
</x-site-layout>
