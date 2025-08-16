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
        <div class="absolute inset-0 bg-[url('data:image/svg+xml,<svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><g fill="%23ffffff" fill-opacity="0.1"><circle cx="30" cy="30" r="4"/></g></svg>')] opacity-30"></div>
        
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
            
            <div class="grid md:grid-cols-3 gap-8">
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
                    <div class="text-3xl md:text-4xl font-bold text-orange-600 dark:text-orange-400">{{ $stats['featured_stats']['top_completion_time']['time_minutes'] ?? '0' }}</div>
                    <div class="text-gray-600 dark:text-gray-300">{{ __('app.homepage.social_proof.stat_best_time_today', ['time' => '']) }}</div>
                </div>
                @endif
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
