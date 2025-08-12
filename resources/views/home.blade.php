@php($t = __('app'))
<x-site-layout>
    <section class="relative overflow-hidden py-12 sm:py-20 bg-gradient-to-b from-surface-50 to-white dark:from-gray-900 dark:to-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-10 items-center">
                <div>
                    <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">
                        {{ __('app.welcome_title') }}
                    </h1>
                    <p class="mt-4 text-lg text-gray-700 dark:text-gray-300">
                        {{ __('app.welcome_subtitle') }}
                    </p>
                    <div class="mt-8 flex gap-3">
                        <a href="{{ route('register') }}" class="inline-flex items-center rounded-md bg-brand-600 px-5 py-3 text-white hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-400 dark:focus:ring-offset-gray-900">
                            {{ __('auth.Register') }}
                        </a>
                        <a href="{{ route('login') }}" class="inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 px-5 py-3 text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-400 dark:focus:ring-offset-gray-900">
                            {{ __('auth.Log in') }}
                        </a>
                    </div>
                </div>
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-6 bg-white/70 dark:bg-gray-800">
                    <ul class="space-y-4">
                        <li class="flex items-start gap-3">
                            <span class="mt-1 h-2 w-2 rounded-full bg-brand-600" aria-hidden="true"></span>
                            <p class="text-gray-800 dark:text-gray-200">{{ __('Board 9×9 con candidati, evidenziazione, undo/redo e timer.') }}</p>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-1 h-2 w-2 rounded-full bg-brand-600" aria-hidden="true"></span>
                            <p class="text-gray-800 dark:text-gray-200">{{ __('Sfide asincrone con seed condiviso e classifiche a tempo (tie‑break).') }}</p>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-1 h-2 w-2 rounded-full bg-brand-600" aria-hidden="true"></span>
                            <p class="text-gray-800 dark:text-gray-200">{{ __('Solver logico e replay personale delle mosse.') }}</p>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-1 h-2 w-2 rounded-full bg-brand-600" aria-hidden="true"></span>
                            <p class="text-gray-800 dark:text-gray-200">{{ __('Accessibilità WCAG AA, tema chiaro/scuro.') }}</p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="py-12 sm:py-16 bg-white dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ __('app.home.after_signup') }}</h2>
            <div class="mt-8 grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-5 bg-white/80 dark:bg-gray-800">
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ __('app.home.daily_weekly') }}</h3>
                    <p class="mt-2 text-gray-700 dark:text-gray-300">{{ __('app.home.daily_weekly_desc') }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-5 bg-white/80 dark:bg-gray-800">
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ __('app.home.profile') }}</h3>
                    <p class="mt-2 text-gray-700 dark:text-gray-300">{{ __('app.home.profile_desc') }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-5 bg-white/80 dark:bg-gray-800">
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ __('app.home.solver') }}</h3>
                    <p class="mt-2 text-gray-700 dark:text-gray-300">{{ __('app.home.solver_desc') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Placeholder immagini promozionali accessibili -->
    <section class="py-12 sm:py-16 bg-surface-50 dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid md:grid-cols-2 gap-6">
            <figure class="aspect-[16/9] w-full overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-white/60 dark:bg-gray-800 flex items-center justify-center">
                <span class="text-sm text-gray-600 dark:text-gray-300">{{ __('Segnaposto immagine: schermata board 9×9') }}</span>
            </figure>
            <figure class="aspect-[16/9] w-full overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-white/60 dark:bg-gray-800 flex items-center justify-center">
                <span class="text-sm text-gray-600 dark:text-gray-300">{{ __('Segnaposto immagine: classifica sfida') }}</span>
            </figure>
        </div>
    </section>
</x-site-layout>


