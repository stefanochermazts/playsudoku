<x-site-layout>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-neutral-900 dark:text-white mb-4">{{ __('app.badges.title') }}</h1>
        <p class="text-neutral-600 dark:text-neutral-300 mb-8">{{ __('app.badges.subtitle') }}</p>
        @php($user = auth()->user())
        @php($badges = \App\Models\Badge::orderBy('category')->get())
        @php($owned = $user ? $user->belongsToMany(\App\Models\Badge::class, 'user_badges')->pluck('badges.id')->toArray() : [])
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
            @foreach($badges as $badge)
                <div class="bg-white/70 dark:bg-neutral-800/70 rounded-2xl p-4 border border-neutral-200/50 dark:border-neutral-700/50 shadow">
                    <div class="flex items-center justify-center mb-3">
                        <x-badge :slug="$badge->slug" :label="__('app.badges.' . $badge->slug . '.name') ?: $badge->name" size="lg" />
                    </div>
                    <div class="text-center">
                        <div class="text-sm font-semibold text-neutral-900 dark:text-white">{{ __('app.badges.' . $badge->slug . '.name') ?: $badge->name }}</div>
                        <div class="text-xs text-neutral-600 dark:text-neutral-400">{{ __('app.badges.' . $badge->slug . '.description') ?: $badge->description }}</div>
                        @if(in_array($badge->id, $owned))
                            <span class="inline-block mt-2 text-xs px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">{{ __('app.badges.obtained') }}</span>
                        @else
                            <span class="inline-block mt-2 text-xs px-2 py-0.5 rounded-full bg-neutral-100 text-neutral-600 dark:bg-neutral-700/50 dark:text-neutral-300">{{ __('app.badges.locked') }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-site-layout>
