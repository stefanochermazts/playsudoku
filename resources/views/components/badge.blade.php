@props(['slug' => '', 'label' => '', 'size' => 'md'])

@php
    $sizes = [
        'sm' => 'w-8 h-8',
        'md' => 'w-12 h-12',
        'lg' => 'w-16 h-16',
    ];
    $cls = $sizes[$size] ?? $sizes['md'];
@endphp

<span class="inline-flex items-center justify-center {{$cls}} rounded-full bg-gradient-to-br from-neutral-200 to-neutral-100 dark:from-neutral-800 dark:to-neutral-700 border border-neutral-300/60 dark:border-neutral-600/60 shadow-inner" role="img" aria-label="{{ $label }}">
    @switch($slug)
        @case('first_win')
            <svg class="w-6 h-6 text-amber-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 17l-4.5 2.4 1-5.1-3.7-3.6 5.1-.7L12 5l2.1 4.9 5.1.7-3.7 3.6 1 5.1z"/></svg>
            @break
        @case('five_wins')
            <svg class="w-6 h-6 text-yellow-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 21h10l-1-7 5-5-7-1L12 2 10 8 3 9l5 5-1 7z"/></svg>
            @break
        @case('speedster_60s')
            <svg class="w-6 h-6 text-orange-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13 3a9 9 0 106.32 2.68l1.41-1.42A11 11 0 1113 1v2zM11 7h2v6h-2z"/></svg>
            @break
        @case('hard_solver')
            <svg class="w-6 h-6 text-violet-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l7 4v6c0 5-3.5 9.74-7 10-3.5-.26-7-5-7-10V6l7-4z"/></svg>
            @break
        @case('no_hints')
            <svg class="w-6 h-6 text-emerald-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M9 21h6v-2H9v2zm3-19a7 7 0 00-4 12.9V17h8v-2.1A7 7 0 0012 2z"/></svg>
            @break
        @case('perfect_run')
            <svg class="w-6 h-6 text-rose-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l3 7 7 1-5 5 1 7-6-3-6 3 1-7-5-5 7-1 3-7z"/></svg>
            @break
        @case('weekly_warrior')
            <svg class="w-6 h-6 text-cyan-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 2v2H5a2 2 0 00-2 2v2h18V6a2 2 0 00-2-2h-2V2h-2v2H9V2H7zm14 8H3v10a2 2 0 002 2h14a2 2 0 002-2V10z"/></svg>
            @break
        @case('social_starter')
            <svg class="w-6 h-6 text-pink-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M16 11c1.66 0 3-1.34 3-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zM8 11c1.66 0 3-1.34 3-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5C15 14.17 10.33 13 8 13zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.93 1.97 3.45V19h6v-2.5C23 14.17 18.33 13 16 13z"/></svg>
            @break
        @case('club_member')
            <svg class="w-6 h-6 text-teal-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M4 3h10l-1 3 1 3H4v11H2V3h2zm14 0h4v12l-3-2-3 2V3h2z"/></svg>
            @break
        @default
            <svg class="w-6 h-6 text-neutral-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="12" cy="12" r="10"/></svg>
    @endswitch
</span>
