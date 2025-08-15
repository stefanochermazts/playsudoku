<x-site-layout>
    @php
        // Initialize services for homepage with fallback
        try {
            $homepageStats = app(App\Services\HomepageStatsService::class);
            $stats = $homepageStats->getStats();
            $liveStats = $homepageStats->getLiveStats();
            $testimonials = $homepageStats->getTestimonials();
        } catch (Exception $e) {
            // Fallback stats if service fails
            \Log::warning('HomepageStatsService failed: ' . $e->getMessage());
            $stats = [
                'total_users' => 1000,
                'total_challenges' => 500,
                'total_puzzles_solved' => 2500,
                'active_users_today' => 50,
                'challenges_completed_today' => 15,
                'avg_completion_time' => 8.5,
                'featured_stats' => ['top_completion_time' => null]
            ];
            $liveStats = ['users_online' => 25];
            $testimonials = [
                ['name' => 'Marco R.', 'rating' => 5, 'location' => 'Milano, IT'],
                ['name' => 'Sarah J.', 'rating' => 5, 'location' => 'New York, US'],
                ['name' => 'Giovanni P.', 'rating' => 4, 'location' => 'Roma, IT']
            ];
            $homepageStats = new class { 
                public function formatNumber($num) { return number_format($num); }
            };
        }
        
        // Set homepage-specific meta tags
        try {
            $metaService = app(App\Services\MetaService::class);
            $metaService->setPage(
                __('app.homepage.hero.title'),
                __('app.homepage.hero.subtitle'),
                ['url' => url()->current()]
            );
        } catch (Exception $e) {
            \Log::warning('MetaService failed: ' . $e->getMessage());
        }
    @endphp

    {{-- Hero Section --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-blue-600 via-purple-600 to-indigo-700">
        {{-- Background Pattern --}}
        <div class="absolute inset-0 bg-black/20"></div>
        <div class="absolute inset-0 opacity-30">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle, rgba(255,255,255,0.1) 2px, transparent 2px); background-size: 60px 60px;"></div>
        </div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-32">
            <div class="text-center">
                <h1 class="text-4xl md:text-6xl lg:text-7xl font-black text-white mb-6 leading-tight">
                    {{ __('app.homepage.hero.title') }}
                </h1>
                <p class="text-xl md:text-2xl text-blue-100 mb-8 max-w-4xl mx-auto leading-relaxed">
                    {{ __('app.homepage.hero.subtitle') }}
                </p>
                
                {{-- Stats Row --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-12 max-w-4xl mx-auto">
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 border border-white/20">
                        <div class="text-2xl md:text-3xl font-bold text-white" data-counter="{{ $stats['total_users'] }}">
                            {{ $homepageStats->formatNumber($stats['total_users']) }}
                        </div>
                        <div class="text-blue-100 text-sm">{{ __('app.homepage.hero.stats_users', ['count' => '']) }}</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 border border-white/20">
                        <div class="text-2xl md:text-3xl font-bold text-white" data-counter="{{ $stats['total_challenges'] }}">
                            {{ $homepageStats->formatNumber($stats['total_challenges']) }}
                        </div>
                        <div class="text-blue-100 text-sm">{{ __('app.homepage.hero.stats_challenges', ['count' => '']) }}</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 border border-white/20">
                        <div class="text-2xl md:text-3xl font-bold text-white" data-counter="{{ $stats['total_puzzles_solved'] }}">
                            {{ $homepageStats->formatNumber($stats['total_puzzles_solved']) }}
                        </div>
                        <div class="text-blue-100 text-sm">{{ __('app.homepage.hero.stats_puzzles', ['count' => '']) }}</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 border border-white/20">
                        <div class="text-2xl md:text-3xl font-bold text-green-300" data-counter="{{ $liveStats['users_online'] }}">
                            {{ $liveStats['users_online'] }}
                        </div>
                        <div class="text-blue-100 text-sm">{{ __('app.homepage.hero.live_users', ['count' => '']) }}</div>
                    </div>
                </div>
                
                {{-- CTA Buttons --}}
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    @guest
                        <a href="{{ route('register') }}" 
                           class="inline-flex items-center px-8 py-4 text-lg font-semibold text-blue-600 bg-white rounded-xl hover:bg-blue-50 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            {{ __('app.homepage.hero.cta_register') }}
                        </a>
                        <a href="{{ route('localized.sudoku.training', ['locale' => app()->getLocale()]) }}" 
                           class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-white/20 backdrop-blur-sm border border-white/30 rounded-xl hover:bg-white/30 transition-all duration-200">
                            {{ __('app.homepage.hero.cta_training') }}
                        </a>
                    @else
                        <a href="{{ route('localized.dashboard', ['locale' => app()->getLocale()]) }}" 
                           class="inline-flex items-center px-8 py-4 text-lg font-semibold text-blue-600 bg-white rounded-xl hover:bg-blue-50 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            {{ __('app.homepage.cta.join_challenge') }}
                        </a>
                        <a href="{{ route('localized.sudoku.training', ['locale' => app()->getLocale()]) }}" 
                           class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-white/20 backdrop-blur-sm border border-white/30 rounded-xl hover:bg-white/30 transition-all duration-200">
                            {{ __('app.homepage.cta.start_training') }}
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </section>

    {{-- Features Showcase --}}
    <section class="py-20 bg-gray-50 dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ __('app.homepage.features.title') }}
                </h2>
                <p class="text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
                    {{ __('app.homepage.features.subtitle') }}
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                {{-- Training Feature --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-1">
                    <div class="text-4xl mb-4">🎯</div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                        {{ __('app.homepage.features.training.title') }}
                    </h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-6">
                        {{ __('app.homepage.features.training.description') }}
                    </p>
                    <a href="{{ route('localized.sudoku.training', ['locale' => app()->getLocale()]) }}" 
                       class="inline-flex items-center text-blue-600 dark:text-blue-400 font-medium hover:text-blue-700 dark:hover:text-blue-300">
                        {{ __('app.homepage.features.training.cta') }}
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                        </svg>
                    </a>
                </div>
                
                {{-- Competitive Feature --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-1">
                    <div class="text-4xl mb-4">🏆</div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                        {{ __('app.homepage.features.competitive.title') }}
                    </h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-6">
                        {{ __('app.homepage.features.competitive.description') }}
                    </p>
                    @guest
                        <a href="{{ route('register') }}" 
                           class="inline-flex items-center text-blue-600 dark:text-blue-400 font-medium hover:text-blue-700 dark:hover:text-blue-300">
                            {{ __('app.homepage.features.competitive.cta') }}
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                            </svg>
                        </a>
                    @else
                        <a href="{{ route('localized.dashboard', ['locale' => app()->getLocale()]) }}" 
                           class="inline-flex items-center text-blue-600 dark:text-blue-400 font-medium hover:text-blue-700 dark:hover:text-blue-300">
                            {{ __('app.homepage.features.competitive.cta') }}
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                            </svg>
                        </a>
                    @endguest
                </div>
                
                {{-- Analyzer Feature --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-1">
                    <div class="text-4xl mb-4">🔍</div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                        {{ __('app.homepage.features.analyzer.title') }}
                    </h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-6">
                        {{ __('app.homepage.features.analyzer.description') }}
                    </p>
                    <a href="{{ route('localized.sudoku.analyzer', ['locale' => app()->getLocale()]) }}" 
                       class="inline-flex items-center text-blue-600 dark:text-blue-400 font-medium hover:text-blue-700 dark:hover:text-blue-300">
                        {{ __('app.homepage.features.analyzer.cta') }}
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                        </svg>
                    </a>
                </div>
                
                {{-- Social Feature --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-1">
                    <div class="text-4xl mb-4">👥</div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                        {{ __('app.homepage.features.social.title') }}
                    </h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-6">
                        {{ __('app.homepage.features.social.description') }}
                    </p>
                    @guest
                        <a href="{{ route('register') }}" 
                           class="inline-flex items-center text-blue-600 dark:text-blue-400 font-medium hover:text-blue-700 dark:hover:text-blue-300">
                            {{ __('app.homepage.features.social.cta') }}
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                            </svg>
                        </a>
                    @else
                        <a href="{{ route('localized.friends.index', ['locale' => app()->getLocale()]) }}" 
                           class="inline-flex items-center text-blue-600 dark:text-blue-400 font-medium hover:text-blue-700 dark:hover:text-blue-300">
                            {{ __('app.homepage.features.social.cta') }}
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                            </svg>
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </section>

    {{-- Social Proof --}}
    <section class="py-20 bg-white dark:bg-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ __('app.homepage.social_proof.title') }}
                </h2>
                <p class="text-xl text-gray-600 dark:text-gray-300">
                    {{ __('app.homepage.social_proof.subtitle') }}
                </p>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-3xl md:text-4xl font-bold text-blue-600 dark:text-blue-400">{{ $homepageStats->formatNumber($stats['active_users_today']) }}</div>
                    <div class="text-gray-600 dark:text-gray-300">{{ __('app.homepage.social_proof.stat_active_today', ['count' => '']) }}</div>
                </div>
                <div>
                    <div class="text-3xl md:text-4xl font-bold text-green-600 dark:text-green-400">{{ $stats['challenges_completed_today'] }}</div>
                    <div class="text-gray-600 dark:text-gray-300">{{ __('app.homepage.social_proof.stat_completed_today', ['count' => '']) }}</div>
                </div>
                @if($stats['avg_completion_time'])
                <div>
                    <div class="text-3xl md:text-4xl font-bold text-purple-600 dark:text-purple-400">{{ $stats['avg_completion_time'] }}</div>
                    <div class="text-gray-600 dark:text-gray-300">{{ __('app.homepage.social_proof.stat_avg_time', ['time' => '']) }}</div>
                </div>
                @endif
                @if(isset($stats['featured_stats']['top_completion_time']))
                <div>
                    <div class="text-3xl md:text-4xl font-bold text-orange-600 dark:text-orange-400">{{ $stats['featured_stats']['top_completion_time']['time_minutes'] }}</div>
                    <div class="text-gray-600 dark:text-gray-300">{{ __('app.homepage.social_proof.stat_best_time_today', ['time' => '']) }}</div>
                </div>
                @endif
            </div>
        </div>
    </section>

    {{-- Benefits Section --}}
    <section class="py-20 bg-gradient-to-r from-green-50 to-blue-50 dark:from-gray-900 dark:to-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ __('app.homepage.benefits.title') }}
                </h2>
                <p class="text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
                    {{ __('app.homepage.benefits.subtitle') }}
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">{{ __('app.homepage.benefits.memory.title') }}</h3>
                    <p class="text-gray-600 dark:text-gray-300">{{ __('app.homepage.benefits.memory.description') }}</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">{{ __('app.homepage.benefits.logic.title') }}</h3>
                    <p class="text-gray-600 dark:text-gray-300">{{ __('app.homepage.benefits.logic.description') }}</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">{{ __('app.homepage.benefits.concentration.title') }}</h3>
                    <p class="text-gray-600 dark:text-gray-300">{{ __('app.homepage.benefits.concentration.description') }}</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">{{ __('app.homepage.benefits.stress.title') }}</h3>
                    <p class="text-gray-600 dark:text-gray-300">{{ __('app.homepage.benefits.stress.description') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Registration Benefits --}}
    @guest
    <section class="py-20 bg-blue-600">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
                    {{ __('app.homepage.registration.title') }}
                </h2>
                <p class="text-xl text-blue-100 max-w-3xl mx-auto mb-8">
                    {{ __('app.homepage.registration.subtitle') }}
                </p>
                <a href="{{ route('register') }}" 
                   class="inline-flex items-center px-8 py-4 text-lg font-semibold text-blue-600 bg-white rounded-xl hover:bg-blue-50 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                    {{ __('app.homepage.registration.cta') }}
                </a>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-2">{{ __('app.homepage.registration.benefits.progress.title') }}</h3>
                    <p class="text-blue-100">{{ __('app.homepage.registration.benefits.progress.description') }}</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-2">{{ __('app.homepage.registration.benefits.leaderboards.title') }}</h3>
                    <p class="text-blue-100">{{ __('app.homepage.registration.benefits.leaderboards.description') }}</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-2">{{ __('app.homepage.registration.benefits.challenges.title') }}</h3>
                    <p class="text-blue-100">{{ __('app.homepage.registration.benefits.challenges.description') }}</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-2">{{ __('app.homepage.registration.benefits.analytics.title') }}</h3>
                    <p class="text-blue-100">{{ __('app.homepage.registration.benefits.analytics.description') }}</p>
                </div>
            </div>
        </div>
    </section>
    @endguest

    {{-- Testimonials --}}
    <section class="py-20 bg-gray-50 dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ __('app.homepage.testimonials.title') }}
                </h2>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                @foreach($testimonials as $testimonial)
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-lg">
                    <div class="flex items-center mb-4">
                        @for($i = 0; $i < 5; $i++)
                            <svg class="w-5 h-5 {{ $i < $testimonial['rating'] ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                        @endfor
                    </div>
                    <blockquote class="text-gray-600 dark:text-gray-300 mb-4">
                        "{{ __('app.homepage.testimonials.' . strtolower(explode(' ', $testimonial['name'])[0])) }}"
                    </blockquote>
                    <cite class="font-semibold text-gray-900 dark:text-white">{{ $testimonial['name'] }}</cite>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $testimonial['location'] }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- FAQ Section --}}
    <section class="py-20 bg-white dark:bg-gray-800">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ __('app.homepage.faq.title') }}
                </h2>
                <p class="text-xl text-gray-600 dark:text-gray-300">
                    {{ __('app.homepage.faq.subtitle') }}
                </p>
            </div>
            
            <div class="space-y-6" x-data="{ activeTab: null }">
                @php $faqItems = ['how_to_play', 'difficulties', 'competitive', 'free', 'mobile', 'hints']; @endphp
                @foreach($faqItems as $index => $item)
                <div class="bg-gray-50 dark:bg-gray-700 rounded-xl overflow-hidden">
                    <button class="w-full px-6 py-4 text-left focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800" 
                            @click="activeTab = activeTab === {{ $index }} ? null : {{ $index }}">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ __('app.homepage.faq.' . $item . '.question') }}
                            </h3>
                            <svg class="w-5 h-5 text-gray-500 transform transition-transform duration-200" 
                                 :class="{ 'rotate-180': activeTab === {{ $index }} }" 
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </button>
                    <div x-show="activeTab === {{ $index }}" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 transform -translate-y-2"
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 transform translate-y-0"
                         x-transition:leave-end="opacity-0 transform -translate-y-2"
                         class="px-6 pb-4">
                        <p class="text-gray-600 dark:text-gray-300">
                            {{ __('app.homepage.faq.' . $item . '.answer') }}
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Final CTA --}}
    <section class="py-20 bg-gradient-to-r from-blue-600 to-purple-600">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
                {{ app()->getLocale() === 'it' ? 'Pronto a Diventare un Maestro del Sudoku?' : 'Ready to Become a Sudoku Master?' }}
            </h2>
            <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
                {{ app()->getLocale() === 'it' ? 'Unisciti a migliaia di giocatori in tutto il mondo e inizia la tua avventura Sudoku oggi stesso.' : 'Join thousands of players worldwide and start your Sudoku adventure today.' }}
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                @guest
                    <a href="{{ route('register') }}" 
                       class="inline-flex items-center px-8 py-4 text-lg font-semibold text-blue-600 bg-white rounded-xl hover:bg-blue-50 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        {{ __('app.homepage.cta.register_now') }}
                    </a>
                    <a href="{{ route('localized.sudoku.training', ['locale' => app()->getLocale()]) }}" 
                       class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-white/20 backdrop-blur-sm border border-white/30 rounded-xl hover:bg-white/30 transition-all duration-200">
                        {{ __('app.homepage.cta.start_training') }}
                    </a>
                @else
                    <a href="{{ route('localized.dashboard', ['locale' => app()->getLocale()]) }}" 
                       class="inline-flex items-center px-8 py-4 text-lg font-semibold text-blue-600 bg-white rounded-xl hover:bg-blue-50 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        {{ __('app.homepage.cta.join_challenge') }}
                    </a>
                @endguest
            </div>
        </div>
    </section>
    
    @push('scripts')
    <script>
    // Animate counters on scroll
    document.addEventListener('DOMContentLoaded', function() {
        const counters = document.querySelectorAll('[data-counter]');
        const observerOptions = {
            threshold: 0.7,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counter = entry.target;
                    const target = parseInt(counter.dataset.counter);
                    const duration = 2000; // 2 seconds
                    const increment = target / (duration / 16); // 60fps
                    let current = 0;

                    const timer = setInterval(() => {
                        current += increment;
                        if (current >= target) {
                            counter.textContent = target.toLocaleString();
                            clearInterval(timer);
                        } else {
                            counter.textContent = Math.floor(current).toLocaleString();
                        }
                    }, 16);

                    observer.unobserve(counter);
                }
            });
        }, observerOptions);

        counters.forEach(counter => observer.observe(counter));
    });
    </script>
    @endpush
</x-site-layout>
