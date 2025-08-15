<x-site-layout>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        {{-- Header --}}
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                {{ __('app.training.play_title') }}
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                {{ __('app.training.puzzle_seed') }} <span class="font-mono bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">{{ $seed }}</span>
            </p>
        </div>

        {{-- Board di gioco --}}
        <div class="mb-8">
            @livewire('sudoku-board', [
                'initialGrid' => $initialGrid,
                'readOnly' => false,
                'startTimer' => true
            ])
        </div>

        {{-- Azioni --}}
        <div class="flex flex-wrap justify-center gap-4">
            <a href="{{ route('localized.sudoku.training', ['locale' => app()->getLocale()]) }}" 
               class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 
                      focus:outline-none focus:ring-2 focus:ring-gray-500">
                {{ __('app.training.back_to_demo') }}
            </a>
            
            <a href="{{ route('localized.sudoku.play', ['locale' => app()->getLocale()]) }}" 
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 
                      focus:outline-none focus:ring-2 focus:ring-blue-500">
                {{ __('app.training.new_puzzle') }}
            </a>
            
            <button onclick="resetPuzzle()" 
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 
                           focus:outline-none focus:ring-2 focus:ring-red-500">
                {{ __('app.training.reset') }}
            </button>
        </div>

        {{-- Suggerimenti --}}
        <div class="mt-8 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <h3 class="font-bold text-blue-900 dark:text-blue-100 mb-2">{{ __('app.training.hints_title') }}</h3>
            <ul class="text-blue-800 dark:text-blue-200 text-sm space-y-1">
                <li>• Usa <kbd class="px-1 bg-blue-100 dark:bg-blue-800 rounded">C</kbd> {{ __('app.training.hint_mode_switch') }}</li>
                <li>• {{ __('app.training.hint_candidates') }}</li>
                <li>• Usa <kbd class="px-1 bg-blue-100 dark:bg-blue-800 rounded">U</kbd> {{ __('app.training.hint_undo') }}</li>
                <li>• {{ __('app.training.hint_errors') }}</li>
                <li>• <kbd class="px-1 bg-blue-100 dark:bg-blue-800 rounded">Tab</kbd> {{ __('app.training.hint_accessibility') }}</li>
            </ul>
        </div>
    </div>
</div>

<script>
function resetPuzzle() {
    if (confirm('{{ __('app.training.reset_confirm') }}')) {
        location.reload();
    }
}

// Gestione eventi Livewire
document.addEventListener('livewire:initialized', () => {
    Livewire.on('puzzle-completed', (event) => {
        const time = event.time;
        const minutes = Math.floor(time / 60);
        const seconds = time % 60;
        const formattedTime = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        
        setTimeout(() => {
            if (confirm(`{{ __('app.training.completion_message', ['time' => '${formattedTime}']) }}`)) {
                window.location.href = '{{ route('localized.sudoku.play', ['locale' => app()->getLocale()]) }}';
            }
        }, 2000);
    });
});
</script>
</x-site-layout>
