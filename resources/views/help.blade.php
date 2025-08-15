<x-site-layout>
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-blue-900 py-8">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            
            {{-- Header --}}
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ __('app.help.title') }}
                </h1>
                <p class="text-xl text-gray-600 dark:text-gray-300">
                    {{ __('app.help.subtitle') }}
                </p>
            </div>
            
            {{-- Getting Started Section --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 mb-8">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                    {{ __('app.help.getting_started.title') }}
                </h2>
                
                <div class="grid md:grid-cols-1 gap-6">
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full bg-blue-500 mt-2 mr-4"></div>
                            <p class="text-gray-700 dark:text-gray-300">{{ __('app.help.getting_started.guest_play') }}</p>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full bg-blue-500 mt-2 mr-4"></div>
                            <p class="text-gray-700 dark:text-gray-300">{{ __('app.help.getting_started.register_benefits') }}</p>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full bg-blue-500 mt-2 mr-4"></div>
                            <p class="text-gray-700 dark:text-gray-300">{{ __('app.help.getting_started.choose_difficulty') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Game Features Section --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 mb-8">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                    {{ __('app.help.game_features.title') }}
                </h2>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full bg-green-500 mt-2 mr-4"></div>
                            <p class="text-gray-700 dark:text-gray-300">{{ __('app.help.game_features.candidates') }}</p>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full bg-green-500 mt-2 mr-4"></div>
                            <p class="text-gray-700 dark:text-gray-300">{{ __('app.help.game_features.hints') }}</p>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full bg-green-500 mt-2 mr-4"></div>
                            <p class="text-gray-700 dark:text-gray-300">{{ __('app.help.game_features.undo_redo') }}</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full bg-green-500 mt-2 mr-4"></div>
                            <p class="text-gray-700 dark:text-gray-300">{{ __('app.help.game_features.timer') }}</p>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full bg-green-500 mt-2 mr-4"></div>
                            <p class="text-gray-700 dark:text-gray-300">{{ __('app.help.game_features.error_detection') }}</p>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full bg-green-500 mt-2 mr-4"></div>
                            <p class="text-gray-700 dark:text-gray-300">{{ __('app.help.game_features.accessibility') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Daily Challenges Section --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 mb-8">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                    {{ __('app.help.daily_challenges.title') }}
                </h2>
                
                <div class="grid md:grid-cols-1 gap-6">
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full bg-yellow-500 mt-2 mr-4"></div>
                            <p class="text-gray-700 dark:text-gray-300">{{ __('app.help.daily_challenges.same_puzzle') }}</p>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full bg-yellow-500 mt-2 mr-4"></div>
                            <p class="text-gray-700 dark:text-gray-300">{{ __('app.help.daily_challenges.time_based') }}</p>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full bg-yellow-500 mt-2 mr-4"></div>
                            <p class="text-gray-700 dark:text-gray-300">{{ __('app.help.daily_challenges.penalty_system') }}</p>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full bg-yellow-500 mt-2 mr-4"></div>
                            <p class="text-gray-700 dark:text-gray-300">{{ __('app.help.daily_challenges.leaderboards') }}</p>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full bg-yellow-500 mt-2 mr-4"></div>
                            <p class="text-gray-700 dark:text-gray-300">{{ __('app.help.daily_challenges.weekly_challenges') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Training and Analyzer Sections --}}
            <div class="grid md:grid-cols-2 gap-8 mb-8">
                {{-- Training Section --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                        {{ __('app.help.training.title') }}
                    </h2>
                    
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full bg-purple-500 mt-2 mr-4"></div>
                            <p class="text-gray-700 dark:text-gray-300">{{ __('app.help.training.free_play') }}</p>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full bg-purple-500 mt-2 mr-4"></div>
                            <p class="text-gray-700 dark:text-gray-300">{{ __('app.help.training.difficulty_selection') }}</p>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full bg-purple-500 mt-2 mr-4"></div>
                            <p class="text-gray-700 dark:text-gray-300">{{ __('app.help.training.hint_system') }}</p>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full bg-purple-500 mt-2 mr-4"></div>
                            <p class="text-gray-700 dark:text-gray-300">{{ __('app.help.training.progress_tracking') }}</p>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <a href="{{ route('localized.sudoku.training', ['locale' => app()->getLocale()]) }}" 
                           class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                            {{ __('app.nav.training') }}
                        </a>
                    </div>
                </div>
                
                {{-- Analyzer Section --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                        {{ __('app.help.analyzer.title') }}
                    </h2>
                    
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full bg-red-500 mt-2 mr-4"></div>
                            <p class="text-gray-700 dark:text-gray-300">{{ __('app.help.analyzer.import_puzzle') }}</p>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full bg-red-500 mt-2 mr-4"></div>
                            <p class="text-gray-700 dark:text-gray-300">{{ __('app.help.analyzer.technique_analysis') }}</p>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full bg-red-500 mt-2 mr-4"></div>
                            <p class="text-gray-700 dark:text-gray-300">{{ __('app.help.analyzer.difficulty_rating') }}</p>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full bg-red-500 mt-2 mr-4"></div>
                            <p class="text-gray-700 dark:text-gray-300">{{ __('app.help.analyzer.step_by_step') }}</p>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <a href="{{ route('localized.sudoku.analyzer', ['locale' => app()->getLocale()]) }}" 
                           class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                            {{ __('app.nav.analyzer') }}
                        </a>
                    </div>
                </div>
            </div>
            
            {{-- Account Features Section --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 mb-8">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                    {{ __('app.help.account_features.title') }}
                </h2>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full bg-indigo-500 mt-2 mr-4"></div>
                            <p class="text-gray-700 dark:text-gray-300">{{ __('app.help.account_features.profile_stats') }}</p>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full bg-indigo-500 mt-2 mr-4"></div>
                            <p class="text-gray-700 dark:text-gray-300">{{ __('app.help.account_features.progress_tracking') }}</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full bg-indigo-500 mt-2 mr-4"></div>
                            <p class="text-gray-700 dark:text-gray-300">{{ __('app.help.account_features.notification_preferences') }}</p>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full bg-indigo-500 mt-2 mr-4"></div>
                            <p class="text-gray-700 dark:text-gray-300">{{ __('app.help.account_features.multilingual') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Tips Section --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 mb-8">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                    {{ __('app.help.tips.title') }}
                </h2>
                
                <div class="grid md:grid-cols-1 gap-6">
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full bg-orange-500 mt-2 mr-4"></div>
                            <p class="text-gray-700 dark:text-gray-300">{{ __('app.help.tips.start_easy') }}</p>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full bg-orange-500 mt-2 mr-4"></div>
                            <p class="text-gray-700 dark:text-gray-300">{{ __('app.help.tips.use_candidates') }}</p>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full bg-orange-500 mt-2 mr-4"></div>
                            <p class="text-gray-700 dark:text-gray-300">{{ __('app.help.tips.learn_techniques') }}</p>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full bg-orange-500 mt-2 mr-4"></div>
                            <p class="text-gray-700 dark:text-gray-300">{{ __('app.help.tips.practice_daily') }}</p>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full bg-orange-500 mt-2 mr-4"></div>
                            <p class="text-gray-700 dark:text-gray-300">{{ __('app.help.tips.analyze_mistakes') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Support Section --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 mb-8">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                    {{ __('app.help.support.title') }}
                </h2>
                
                <div class="space-y-4 mb-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-2 h-2 rounded-full bg-pink-500 mt-2 mr-4"></div>
                        <p class="text-gray-700 dark:text-gray-300">{{ __('app.help.support.contact_us') }}</p>
                    </div>
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-2 h-2 rounded-full bg-pink-500 mt-2 mr-4"></div>
                        <p class="text-gray-700 dark:text-gray-300">{{ __('app.help.support.feedback') }}</p>
                    </div>
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-2 h-2 rounded-full bg-pink-500 mt-2 mr-4"></div>
                        <p class="text-gray-700 dark:text-gray-300">{{ __('app.help.support.bug_reports') }}</p>
                    </div>
                </div>
                
                <div class="text-center">
                    <a href="{{ route('localized.contact', ['locale' => app()->getLocale()]) }}" 
                       class="inline-flex items-center px-6 py-3 bg-pink-600 text-white rounded-lg hover:bg-pink-700 transition-colors font-medium">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        {{ __('app.footer.contact') }}
                    </a>
                </div>
            </div>
            
        </div>
    </div>
</div>
</x-site-layout>
