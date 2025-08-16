<!-- Hamburger Menu for Authenticated Users -->
<div class="relative" x-data="{ menuOpen: false, isDark: document.documentElement.classList.contains('dark') }">
	<button @click="menuOpen = !menuOpen" 
	        class="flex items-center space-x-2 px-4 py-2 rounded-lg text-neutral-600 dark:text-neutral-300 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
	        :class="{ 'bg-neutral-100 dark:bg-neutral-800 text-primary-600 dark:text-primary-400': menuOpen }">
		<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
			<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
		</svg>
		<span class="font-medium">{{ __('app.nav.menu') }}</span>
		<svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': menuOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
			<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
		</svg>
	</button>

	<!-- Dropdown Menu -->
	<div x-show="menuOpen" 
	     x-transition:enter="transition ease-out duration-200"
	     x-transition:enter-start="opacity-0 scale-95"
	     x-transition:enter-end="opacity-100 scale-100"
	     x-transition:leave="transition ease-in duration-150"
	     x-transition:leave-start="opacity-100 scale-100"
	     x-transition:leave-end="opacity-0 scale-95"
	     @click.away="menuOpen = false"
	     class="absolute right-0 top-full mt-2 w-[800px] max-w-[90vw] bg-white dark:bg-neutral-800 rounded-lg shadow-lg border border-neutral-200 dark:border-neutral-700 z-50 max-h-[80vh] overflow-y-auto">
	     
		 <!-- Grid Layout: 5 groups in horizontal layout -->
		 <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 p-4">
		     <!-- Group 1: Bacheche -->
		     <div class="space-y-3">
		         <h3 class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide border-b border-neutral-200 dark:border-neutral-600 pb-2">{{ __('app.nav.dashboard_challenges') }}</h3>
		         <div class="space-y-1">
		             <a href="{{ route('localized.dashboard', ['locale' => app()->getLocale()]) }}" 
		                class="flex items-center space-x-2 px-2 py-1.5 rounded-md text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors"
		                @click="menuOpen = false">
		                 <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
		                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
		                 </svg>
		                 <span>{{ __('app.nav.dashboard') }}</span>
		             </a>
		             <a href="{{ route('localized.challenges.index', ['locale' => app()->getLocale()]) }}" 
		                class="flex items-center space-x-2 px-2 py-1.5 rounded-md text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors"
		                @click="menuOpen = false">
		                 <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
		                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
		                 </svg>
		                 <span>{{ __('app.nav.challenges') }}</span>
		             </a>
		             <a href="{{ route('localized.daily-board.index', ['locale' => app()->getLocale()]) }}" 
		                class="flex items-center space-x-2 px-2 py-1.5 rounded-md text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors"
		                @click="menuOpen = false">
		                 <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
		                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
		                 </svg>
		                 <span>{{ __('app.daily_board') }}</span>
		             </a>
		             <a href="{{ route('localized.weekly-board.index', ['locale' => app()->getLocale()]) }}" 
		                class="flex items-center space-x-2 px-2 py-1.5 rounded-md text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors"
		                @click="menuOpen = false">
		                 <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
		                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
		                 </svg>
		                 <span>{{ __('app.weekly_board') }}</span>
		             </a>
		         </div>
		     </div>

		     <!-- Group 2: Social -->
		     <div class="space-y-3">
		         <h3 class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide border-b border-neutral-200 dark:border-neutral-600 pb-2">{{ __('app.nav.social') }}</h3>
		         <div class="space-y-1">
		             <a href="{{ route('localized.friends.index', ['locale' => app()->getLocale()]) }}" 
		                class="flex items-center space-x-2 px-2 py-1.5 rounded-md text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors"
		                @click="menuOpen = false">
		                 <svg class="w-4 h-4 text-secondary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
		                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
		                 </svg>
		                 <span>{{ __('app.nav.friends') }}</span>
		             </a>
		             <a href="{{ route('localized.clubs.index', ['locale' => app()->getLocale()]) }}" 
		                class="flex items-center space-x-2 px-2 py-1.5 rounded-md text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors"
		                @click="menuOpen = false">
		                 <svg class="w-4 h-4 text-secondary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
		                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 515.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 009.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
		                 </svg>
		                 <span>{{ __('app.nav.clubs') }}</span>
		             </a>
		             <a href="{{ route('localized.friends.ranking', ['locale' => app()->getLocale()]) }}" 
		                class="flex items-center space-x-2 px-2 py-1.5 rounded-md text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors"
		                @click="menuOpen = false">
		                 <svg class="w-4 h-4 text-secondary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
		                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
		                 </svg>
		                 <span>{{ __('app.rankings.friends_title') }}</span>
		             </a>
		             <a href="{{ route('localized.activity.index', ['locale' => app()->getLocale()]) }}" 
		                class="flex items-center space-x-2 px-2 py-1.5 rounded-md text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors"
		                @click="menuOpen = false">
		                 <svg class="w-4 h-4 text-secondary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
		                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
		                 </svg>
		                 <span>{{ __('app.activity.title') }}</span>
		             </a>
		         </div>
		     </div>

		     <!-- Group 3: Tools -->
		     <div class="space-y-3">
		         <h3 class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide border-b border-neutral-200 dark:border-neutral-600 pb-2">{{ __('app.nav.tools') }}</h3>
		         <div class="space-y-1">
		             <a href="{{ route('localized.sudoku.training', ['locale' => app()->getLocale()]) }}" 
		                class="flex items-center space-x-2 px-2 py-1.5 rounded-md text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors"
		                @click="menuOpen = false">
		                 <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
		                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
		                 </svg>
		                 <span>{{ __('app.nav.training') }}</span>
		             </a>
		             <a href="{{ route('localized.sudoku.analyzer', ['locale' => app()->getLocale()]) }}" 
		                class="flex items-center space-x-2 px-2 py-1.5 rounded-md text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors"
		                @click="menuOpen = false">
		                 <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
		                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
		                 </svg>
		                 <span>{{ __('app.nav.analyzer') }}</span>
		             </a>
		             <a href="{{ route('localized.badges.index', ['locale' => app()->getLocale()]) }}" 
		                class="flex items-center space-x-2 px-2 py-1.5 rounded-md text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors"
		                @click="menuOpen = false">
		                 <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 17l-4.5 2.4 1-5.1-3.7-3.6 5.1-.7L12 5l2.1 4.9 5.1.7-3.7 3.6 1 5.1z"/></svg>
		                 <span>{{ __('app.badges.title') }}</span>
		             </a>
		             <a href="{{ route('localized.season.leaderboard', ['locale' => app()->getLocale()]) }}" 
		                class="flex items-center space-x-2 px-2 py-1.5 rounded-md text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors"
		                @click="menuOpen = false">
		                 <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 24 24"><path d="M3 3h18v2H3V3zm0 6h12v2H3V9zm0 6h18v2H3v-2z"/></svg>
		                 <span>{{ __('app.season.leaderboard_title') ?? 'Classifica Stagionale' }}</span>
		             </a>
		         </div>
		     </div>

		     <!-- Group 4: Settings -->
		     <div class="space-y-3">
		         <h3 class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide border-b border-neutral-200 dark:border-neutral-600 pb-2">{{ __('app.nav.settings') }}</h3>
		         <div class="space-y-1">
		             <!-- Language Toggle -->
		             <div class="flex items-center space-x-2 px-2 py-1.5">
		                 <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
		                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
		                 </svg>
		                 @php
		                     $currentPath = request()->path();
		                     $startsWithLocale = preg_match('/^(en|it)(\/?|$)/', $currentPath) === 1;
		                     $pathEn = $startsWithLocale ? preg_replace('/^(en|it)(?=\/|$)/', 'en', $currentPath) : 'en';
		                     $pathIt = $startsWithLocale ? preg_replace('/^(en|it)(?=\/|$)/', 'it', $currentPath) : 'it';
		                 @endphp
		                 <div class="flex space-x-1">
		                     <a href="{{ url($pathIt) }}" 
		                        @click="menuOpen = false"
		                        class="px-1.5 py-0.5 text-xs rounded transition-colors {{ app()->getLocale() === 'it' ? 'bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300' : 'text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-700' }}">IT</a>
		                     <a href="{{ url($pathEn) }}" 
		                        @click="menuOpen = false"
		                        class="px-1.5 py-0.5 text-xs rounded transition-colors {{ app()->getLocale() === 'en' ? 'bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300' : 'text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-700' }}">EN</a>
		                 </div>
		             </div>
		             
		             <!-- Privacy moved to Profile page -->
		             
		             <!-- Theme Toggle: moon/sun button -->
		             <div class="flex items-center justify-between px-2 py-1.5">
		                 <div class="flex items-center space-x-2">
		                     <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
		                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
		                     </svg>
		                     <span class="text-neutral-800 dark:text-white">{{ __('app.nav.theme') ?? 'Tema' }}</span>
		                 </div>
		                 <button @click="toggleTheme(); isDark = !isDark; menuOpen = false"
		                         aria-label="{{ __('app.aria.toggle_theme') }}"
		                         class="p-2 rounded-lg bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors">
		                     <svg x-show="!isDark" x-cloak class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
		                         <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path>
		                     </svg>
		                     <svg x-show="isDark" x-cloak class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
		                         <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
		                     </svg>
		                 </button>
		             </div>
		         </div>
		     </div>

		     <!-- Group 5: User -->
		     <div class="space-y-3">
		         <h3 class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide border-b border-neutral-200 dark:border-neutral-600 pb-2">{{ __('app.nav.account') }}</h3>
		         <div class="space-y-1">
		             <a href="{{ route('localized.profile', ['locale' => app()->getLocale()]) }}" 
		                class="flex items-center space-x-2 px-2 py-1.5 rounded-md text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors"
		                @click="menuOpen = false">
		                 <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
		                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
		                 </svg>
		                 <span>{{ __('app.nav.profile') }}</span>
		             </a>
		             @if(auth()->user() && auth()->user()->isAdmin())
		             <a href="{{ route('admin.dashboard') }}" 
		                class="flex items-center space-x-2 px-2 py-1.5 rounded-md text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors"
		                @click="menuOpen = false">
		                 <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
		                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
		                 </svg>
		                 <span>Admin</span>
		             </a>
		             @endif
		             <form method="POST" action="{{ route('logout') }}" class="w-full">
		                 @csrf
		                 <button type="submit" 
		                         class="flex items-center space-x-2 w-full px-2 py-1.5 rounded-md text-sm text-neutral-700 dark:text-neutral-300 hover:bg-red-100 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400 transition-colors">
		                     <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
		                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
		                     </svg>
		                     <span>{{ __('app.nav.logout') }}</span>
		                 </button>
		             </form>
		         </div>
		     </div>
		 </div>
	</div>
</div>
