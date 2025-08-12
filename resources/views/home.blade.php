@php($t = __('app'))
<x-site-layout 
    seo-title="{{ __('app.welcome_title') }}" 
    seo-description="{{ __('app.meta.description') }}"
>
    <!-- Hero Section -->
    <header class="relative overflow-hidden pt-20 pb-32" role="banner">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-neutral-900 dark:text-white mb-6 animate-fade-in" itemprop="name">
                    {{ __('app.welcome_title') }}
                </h1>
                <p class="text-xl sm:text-2xl text-neutral-600 dark:text-neutral-300 mb-8 max-w-3xl mx-auto animate-slide-up" itemprop="description">
                    {{ __('app.welcome_subtitle') }}
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center mb-16 animate-slide-up" style="animation-delay: 0.2s;">
                    <a href="{{ route('localized.register', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center justify-center px-8 py-4 bg-gradient-to-r from-primary-600 to-secondary-600 text-white font-semibold rounded-xl hover:from-primary-700 hover:to-secondary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900 transition-all transform hover:scale-105 hover:shadow-xl">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        {{ __('app.cta.start_playing') }}
                    </a>
                    <a href="{{ route('localized.login', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center justify-center px-8 py-4 bg-white/10 dark:bg-neutral-800/50 border border-neutral-300 dark:border-neutral-600 text-neutral-900 dark:text-neutral-100 font-semibold rounded-xl hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900 transition-all backdrop-blur-sm">
                        {{ __('auth.Log in') }}
                    </a>
                </div>

                <!-- Main Features Preview -->
                <div class="grid md:grid-cols-3 gap-6 mb-20 animate-slide-up" style="animation-delay: 0.4s;" itemscope itemtype="https://schema.org/ItemList">
                    <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50 hover:shadow-lg transition-all" itemscope itemtype="https://schema.org/SoftwareApplication" itemprop="itemListElement">
                        <div class="w-12 h-12 bg-gradient-to-r from-primary-500 to-primary-600 rounded-xl flex items-center justify-center mb-4 mx-auto">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2" itemprop="name">{{ __('app.features.sudoku_board.title') }}</h3>
                        <p class="text-neutral-600 dark:text-neutral-300 text-sm" itemprop="description">{{ __('app.features.sudoku_board.description') }}</p>
                    </div>
                    <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50 hover:shadow-lg transition-all" itemscope itemtype="https://schema.org/SoftwareApplication" itemprop="itemListElement">
                        <div class="w-12 h-12 bg-gradient-to-r from-secondary-500 to-secondary-600 rounded-xl flex items-center justify-center mb-4 mx-auto">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2" itemprop="name">{{ __('app.features.competitions.title') }}</h3>
                        <p class="text-neutral-600 dark:text-neutral-300 text-sm" itemprop="description">{{ __('app.features.competitions.description') }}</p>
                    </div>
                    <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50 hover:shadow-lg transition-all" itemscope itemtype="https://schema.org/SoftwareApplication" itemprop="itemListElement">
                        <div class="w-12 h-12 bg-gradient-to-r from-accent-500 to-accent-600 rounded-xl flex items-center justify-center mb-4 mx-auto">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2" itemprop="name">{{ __('app.features.solver.title') }}</h3>
                        <p class="text-neutral-600 dark:text-neutral-300 text-sm" itemprop="description">{{ __('app.features.solver.description') }}</p>
                    </div>
                </div>
                
                <!-- Second row of feature cards -->
                <div class="grid md:grid-cols-3 gap-6 mb-16 animate-slide-up" style="animation-delay: 0.6s;" itemscope itemtype="https://schema.org/ItemList">
                            <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50 hover:shadow-lg transition-all" itemscope itemtype="https://schema.org/SoftwareApplication" itemprop="itemListElement">
                                <div class="w-12 h-12 bg-gradient-to-r from-success-500 to-success-600 rounded-xl flex items-center justify-center mb-4 mx-auto">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2" itemprop="name">{{ __('app.features.anticheat.title') }}</h3>
                                <p class="text-neutral-600 dark:text-neutral-300 text-sm" itemprop="description">{{ __('app.features.anticheat.description') }}</p>
                            </div>
                            <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50 hover:shadow-lg transition-all" itemscope itemtype="https://schema.org/SoftwareApplication" itemprop="itemListElement">
                                <div class="w-12 h-12 bg-gradient-to-r from-warning-500 to-warning-600 rounded-xl flex items-center justify-center mb-4 mx-auto">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2" itemprop="name">{{ __('app.features.replay.title') }}</h3>
                                <p class="text-neutral-600 dark:text-neutral-300 text-sm" itemprop="description">{{ __('app.features.replay.description') }}</p>
                            </div>
                            <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-2xl p-6 border border-neutral-200/50 dark:border-neutral-700/50 hover:shadow-lg transition-all" itemscope itemtype="https://schema.org/SoftwareApplication" itemprop="itemListElement">
                                <div class="w-12 h-12 bg-gradient-to-r from-danger-500 to-danger-600 rounded-xl flex items-center justify-center mb-4 mx-auto">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2" itemprop="name">{{ __('app.features.profile.title') }}</h3>
                                <p class="text-neutral-600 dark:text-neutral-300 text-sm" itemprop="description">{{ __('app.features.profile.description') }}</p>
                            </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Sudoku Grid Showcase -->
    <section class="py-12 bg-white/50 dark:bg-neutral-900/50" itemscope itemtype="https://schema.org/Article">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <article>
                    <h2 class="text-3xl sm:text-4xl font-bold text-neutral-900 dark:text-white mb-6" itemprop="headline">
                        {{ __('app.sections.board.title') }}
                    </h2>
                    <p class="text-lg text-neutral-600 dark:text-neutral-300 mb-8" itemprop="description">
                        {{ __('app.sections.board.description') }}
                    </p>
                    <ul class="space-y-4">
                        <li class="flex items-start">
                            <div class="flex-shrink-0 w-6 h-6 bg-primary-500 rounded-full flex items-center justify-center mr-3 mt-0.5">
                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <span class="text-neutral-700 dark:text-neutral-300">{{ __('app.features.board.candidates') }}</span>
                        </li>
                        <li class="flex items-start">
                            <div class="flex-shrink-0 w-6 h-6 bg-primary-500 rounded-full flex items-center justify-center mr-3 mt-0.5">
                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <span class="text-neutral-700 dark:text-neutral-300">{{ __('app.features.board.undo_redo') }}</span>
                        </li>
                        <li class="flex items-start">
                            <div class="flex-shrink-0 w-6 h-6 bg-primary-500 rounded-full flex items-center justify-center mr-3 mt-0.5">
                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <span class="text-neutral-700 dark:text-neutral-300">{{ __('app.features.board.timer') }}</span>
                        </li>
                        <li class="flex items-start">
                            <div class="flex-shrink-0 w-6 h-6 bg-primary-500 rounded-full flex items-center justify-center mr-3 mt-0.5">
                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <span class="text-neutral-700 dark:text-neutral-300">{{ __('app.features.board.accessibility') }}</span>
                        </li>
                    </ul>
                </article>
                <div class="relative">
                    <div class="aspect-square w-full max-w-md mx-auto bg-gradient-to-br from-white to-neutral-50 dark:from-neutral-800 dark:to-neutral-900 rounded-3xl shadow-2xl border border-neutral-200 dark:border-neutral-700 flex items-center justify-center relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-br from-primary-500/10 to-secondary-500/10"></div>
                        <div class="relative z-10 text-center">
                            <div class="w-16 h-16 bg-gradient-to-r from-primary-500 to-secondary-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2">{{ __('app.placeholders.sudoku_board') }}</h3>
                            <p class="text-sm text-neutral-600 dark:text-neutral-400">{{ __('app.placeholders.sudoku_board_desc') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Competition & Leaderboard Section -->
    <section id="features" class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div class="order-2 lg:order-1">
                    <div class="relative">
                        <div class="aspect-[4/3] w-full max-w-md mx-auto bg-gradient-to-br from-white to-neutral-50 dark:from-neutral-800 dark:to-neutral-900 rounded-3xl shadow-2xl border border-neutral-200 dark:border-neutral-700 flex items-center justify-center relative overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-br from-secondary-500/10 to-accent-500/10"></div>
                            <div class="relative z-10 text-center">
                                <div class="w-16 h-16 bg-gradient-to-r from-secondary-500 to-accent-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2">{{ __('app.placeholders.leaderboard') }}</h3>
                                <p class="text-sm text-neutral-600 dark:text-neutral-400">{{ __('app.placeholders.leaderboard_desc') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="order-1 lg:order-2">
                    <h2 class="text-3xl sm:text-4xl font-bold text-neutral-900 dark:text-white mb-6">
                        {{ __('app.sections.competition.title') }}
                    </h2>
                    <p class="text-lg text-neutral-600 dark:text-neutral-300 mb-8">
                        {{ __('app.sections.competition.description') }}
                    </p>
                    <div class="space-y-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-8 h-8 bg-gradient-to-r from-secondary-500 to-secondary-600 rounded-lg flex items-center justify-center mr-4">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-neutral-900 dark:text-white">{{ __('app.features.daily_challenges.title') }}</h4>
                                <p class="text-neutral-600 dark:text-neutral-300">{{ __('app.features.daily_challenges.description') }}</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-8 h-8 bg-gradient-to-r from-secondary-500 to-secondary-600 rounded-lg flex items-center justify-center mr-4">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-neutral-900 dark:text-white">{{ __('app.features.leaderboards.title') }}</h4>
                                <p class="text-neutral-600 dark:text-neutral-300">{{ __('app.features.leaderboards.description') }}</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-8 h-8 bg-gradient-to-r from-secondary-500 to-secondary-600 rounded-lg flex items-center justify-center mr-4">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m-9 0h10m-9 0a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V6a2 2 0 00-2-2"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-neutral-900 dark:text-white">{{ __('app.features.progress_tracking.title') }}</h4>
                                <p class="text-neutral-600 dark:text-neutral-300">{{ __('app.features.progress_tracking.description') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section id="how-it-works" class="py-20 bg-gradient-to-r from-primary-600 via-secondary-600 to-accent-600 text-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold mb-6">{{ __('app.cta.ready_title') }}</h2>
            <p class="text-xl mb-8 opacity-90">{{ __('app.cta.ready_subtitle') }}</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('localized.register', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center justify-center px-8 py-4 bg-white text-primary-600 font-semibold rounded-xl hover:bg-neutral-50 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-primary-600 transition-all transform hover:scale-105 hover:shadow-xl">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    {{ __('app.cta.join_now') }}
                </a>
                <a href="{{ route('localized.login', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center justify-center px-8 py-4 bg-white/20 border border-white/30 text-white font-semibold rounded-xl hover:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-primary-600 transition-all backdrop-blur-sm">
                    {{ __('app.cta.login_now') }}
                </a>
            </div>
        </div>
    </section>
</x-site-layout>


