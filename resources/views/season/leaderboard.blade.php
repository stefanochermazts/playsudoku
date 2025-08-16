<x-site-layout>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-neutral-900 dark:text-white mb-4">{{ __('app.season.leaderboard_title') ?? 'Classifica Stagionale' }}</h1>
        @php($season = \App\Models\Season::orderByDesc('starts_at')->first())
        @if($season)
            <p class="text-neutral-600 dark:text-neutral-300 mb-6">{{ $season->name }} ({{ $season->starts_at->toDateString() }} – {{ $season->ends_at->toDateString() }})</p>
            @php($rows = $season->leaderboards()->with('user')->orderByDesc('points')->limit(100)->get())
            <div class="overflow-x-auto rounded-xl border border-neutral-200 dark:border-neutral-700">
                <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                    <thead class="bg-neutral-50 dark:bg-neutral-800">
                        <tr>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-neutral-700 dark:text-neutral-200">#</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-neutral-700 dark:text-neutral-200">{{ __('app.season.player') ?? 'Giocatore' }}</th>
                            <th class="px-4 py-2 text-right text-sm font-semibold text-neutral-700 dark:text-neutral-200">{{ __('app.season.points') ?? 'Punti' }}</th>
                            <th class="px-4 py-2 text-right text-sm font-semibold text-neutral-700 dark:text-neutral-200">{{ __('app.season.wins') ?? 'Vittorie' }}</th>
                            <th class="px-4 py-2 text-right text-sm font-semibold text-neutral-700 dark:text-neutral-200">{{ __('app.season.participations') ?? 'Partecipazioni' }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700 bg-white dark:bg-neutral-900">
                        @foreach($rows as $i => $row)
                            <tr>
                                <td class="px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300">{{ $i+1 }}</td>
                                <td class="px-4 py-2 text-sm text-neutral-900 dark:text-white">{{ $row->user->name }}</td>
                                <td class="px-4 py-2 text-sm text-right text-neutral-900 dark:text-white">{{ $row->points }}</td>
                                <td class="px-4 py-2 text-sm text-right text-neutral-700 dark:text-neutral-300">{{ $row->wins }}</td>
                                <td class="px-4 py-2 text-sm text-right text-neutral-700 dark:text-neutral-300">{{ $row->participations }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-neutral-600 dark:text-neutral-300">{{ __('app.season.no_active') ?? 'Nessuna stagione attiva.' }}</p>
        @endif
    </div>
</x-site-layout>

