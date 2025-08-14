<x-site-layout>
    <div class="max-w-5xl mx-auto px-4 py-8">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-neutral-900 dark:text-white mb-2">Classifica — Sfida #{{ $challenge->id }}</h1>
                <p class="text-neutral-600 dark:text-neutral-300">Puzzle: {{ ucfirst($challenge->puzzle->difficulty) }} — Seed {{ $challenge->puzzle->seed }}</p>
            </div>
            
            <div class="mt-4 lg:mt-0">
                <a href="{{ route('localized.leaderboard.export', ['locale' => app()->getLocale(), 'challenge' => $challenge->id]) }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    {{ __('app.export_csv') }}
                </a>
            </div>
        </div>

        @if(!is_null($userRank))
            <div class="mb-4 p-3 rounded-lg bg-primary-50 dark:bg-primary-900/20 text-primary-800 dark:text-primary-200">
                La tua posizione: <strong>#{{ $userRank }}</strong>
            </div>
        @endif

        <div class="overflow-x-auto bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                <caption class="sr-only">Classifica sfida</caption>
                <thead class="bg-neutral-50 dark:bg-neutral-900/50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-neutral-600 dark:text-neutral-300 uppercase tracking-wider">Pos</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-neutral-600 dark:text-neutral-300 uppercase tracking-wider">Utente</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-neutral-600 dark:text-neutral-300 uppercase tracking-wider">Tempo</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-neutral-600 dark:text-neutral-300 uppercase tracking-wider">Errori</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-neutral-600 dark:text-neutral-300 uppercase tracking-wider">Hints</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-neutral-600 dark:text-neutral-300 uppercase tracking-wider">Data</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-neutral-800 divide-y divide-neutral-200 dark:divide-neutral-700">
                    @php($offset = ($attempts->currentPage()-1)*$attempts->perPage())
                    @forelse($attempts as $i => $attempt)
                        @php($pos = $offset + $i + 1)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-900/30">
                            <td class="px-4 py-3 whitespace-nowrap font-semibold">
                                #{{ $pos }}
                                @if($pos === 1)
                                    <span aria-label="Primo" title="Primo" class="ml-2 inline-flex items-center px-2 py-0.5 text-xs rounded bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">🥇</span>
                                @elseif($pos === 2)
                                    <span aria-label="Secondo" title="Secondo" class="ml-2 inline-flex items-center px-2 py-0.5 text-xs rounded bg-neutral-200 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-200">🥈</span>
                                @elseif($pos === 3)
                                    <span aria-label="Terzo" title="Terzo" class="ml-2 inline-flex items-center px-2 py-0.5 text-xs rounded bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">🥉</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $attempt->user?->name ?? '—' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap font-mono">
                                @php($ms = (int) ($attempt->duration_ms ?? 0))
                                @php($s = intdiv($ms, 1000))
                                @php($cs = intdiv($ms % 1000, 10))
                                {{ sprintf('%02d:%02d.%02d', intdiv($s,60), $s%60, $cs) }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $attempt->errors_count }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $attempt->hints_used }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-neutral-500">{{ optional($attempt->completed_at)->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-neutral-600 dark:text-neutral-300">Nessun risultato ancora.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $attempts->links() }}</div>
    </div>
</x-site-layout>


