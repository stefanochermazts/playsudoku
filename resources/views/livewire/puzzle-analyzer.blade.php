<div class="grid lg:grid-cols-2 gap-8">
    {{-- Input Section --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
            {{ __('app.analyzer.import_puzzle') }}
        </h2>

        {{-- Method Selection --}}
        <div class="mb-6">
            <div class="flex flex-wrap gap-2 mb-4">
                <button wire:click="setInputMethod('manual')" 
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors
                               @if($inputMethod === 'manual') bg-blue-600 text-white @else bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 @endif">
                    {{ __('app.analyzer.manual_input') }}
                </button>
                <button wire:click="setInputMethod('json')" 
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors
                               @if($inputMethod === 'json') bg-blue-600 text-white @else bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 @endif">
                    {{ __('app.analyzer.json_input') }}
                </button>
                <button wire:click="loadSamplePuzzle" 
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">
                    {{ __('app.analyzer.load_example') }}
                </button>
                <button wire:click="clearGrid" 
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium">
                    {{ __('app.analyzer.clear_grid') }}
                </button>
            </div>
        </div>

        @if($inputMethod === 'manual')
            {{-- Manual Input Grid --}}
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Griglia di Input</h3>
                <div class="sudoku-input-grid mx-auto" style="display: grid; grid-template-columns: repeat(9, 1fr); grid-template-rows: repeat(9, 1fr); width: 100%; max-width: 450px; aspect-ratio: 1; gap: 1px; border: 3px solid #374151; background-color: #374151;">
                    @for ($row = 0; $row < 9; $row++)
                        @for ($col = 0; $col < 9; $col++)
                            @php
                                $borderStyle = '';
                                if ($row % 3 === 0 && $row > 0) $borderStyle .= 'border-top: 3px solid #374151;';
                                if ($col % 3 === 0 && $col > 0) $borderStyle .= 'border-left: 3px solid #374151;';
                            @endphp
                            <input type="number" 
                                   min="1" max="9"
                                   wire:change="updateCell({{ $row }}, {{ $col }}, $event.target.value)"
                                   value="{{ $inputGrid[$row][$col] ?? '' }}"
                                   class="w-full h-full text-center text-lg font-bold bg-white dark:bg-gray-700 text-gray-900 dark:text-white border-0 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                   style="{{ $borderStyle }}"
                                   placeholder="">
                        @endfor
                    @endfor
                </div>
            </div>
        @else
            {{-- JSON Input --}}
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">{{ __('app.analyzer.json_input_title') }}</h3>
                <textarea wire:model="jsonInput" 
                          rows="6"
                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm font-mono"
                          placeholder="{{ __('app.analyzer.json_placeholder') }}">
                </textarea>
                <button wire:click="importFromJson" 
                        class="mt-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                    {{ __('app.analyzer.import_from_json') }}
                </button>
            </div>
        @endif

        {{-- Error Message --}}
        @if($errorMessage)
            <div class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <div class="text-red-800 dark:text-red-200 text-sm">
                    ❌ {{ $errorMessage }}
                </div>
            </div>
        @endif

        {{-- Analyze Button --}}
        <div class="text-center">
            <button wire:click="analyzePuzzle" 
                    @if($isAnalyzing) disabled @endif
                    class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                @if($isAnalyzing)
                    {{ __('app.analyzer.analyzing') }}
                @else
                    {{ __('app.analyzer.analyze_button') }}
                @endif
            </button>
        </div>
    </div>

    {{-- Results Section --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
            {{ __('app.analyzer.analysis_report') }}
        </h2>

        @if($hasResults)
            {{-- Summary --}}
            <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <h3 class="text-lg font-semibold text-green-900 dark:text-green-100 mb-2">
                    {{ __('app.analyzer.puzzle_solved') }}
                </h3>
                <div class="grid grid-cols-2 gap-4 text-sm text-green-800 dark:text-green-200">
                    <div>
                        <strong>{{ __('app.analyzer.initial_numbers') }}</strong> {{ $solverReport['givenCount'] ?? 0 }}
                    </div>
                    <div>
                        <strong>{{ __('app.analyzer.total_steps') }}</strong> {{ $solverReport['totalSteps'] ?? 0 }}
                    </div>
                    <div>
                        <strong>{{ __('app.analyzer.different_techniques') }}</strong> {{ count($solverReport['uniqueTechniques'] ?? []) }}
                    </div>
                    <div>
                        <strong>{{ __('app.analyzer.completed') }}</strong> {{ ($solverReport['isComplete'] ?? false) ? __('app.analyzer.yes') : __('app.analyzer.no') }}
                    </div>
                </div>
            </div>

            {{-- Techniques Used --}}
            @if(!empty($solverReport['techniqueCounts']))
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
                        {{ __('app.analyzer.techniques_used') }}
                    </h3>
                    <div class="space-y-2">
                        @foreach($solverReport['techniqueCounts'] as $technique => $count)
                            <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <span class="font-medium text-gray-900 dark:text-white">
                                    {{ $this->getTechniqueName($technique) }}
                                </span>
                                <span class="px-2 py-1 bg-blue-600 text-white rounded-full text-sm">
                                    {{ $count }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Step by Step --}}
            @if(!empty($solverReport['steps']))
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
                        {{ __('app.analyzer.solving_steps') }}
                    </h3>
                    <div class="max-h-64 overflow-y-auto space-y-2">
                        @foreach($solverReport['steps'] as $index => $step)
                            @php
                                $technique = $solverReport['techniques'][$index] ?? 'unknown';
                                $stepData = $step ?? [];
                            @endphp
                            <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ __('app.analyzer.step_number', ['number' => $index + 1]) }}: {{ $this->getTechniqueName($technique) }}
                                    </span>
                                    @if(isset($stepData['row'], $stepData['col'], $stepData['value']))
                                        <span class="text-xs px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded">
                                            R{{ $stepData['row'] + 1 }}C{{ $stepData['col'] + 1 }} = {{ $stepData['value'] }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        @else
            <div class="text-center text-gray-500 dark:text-gray-400 py-12">
                <div class="text-6xl mb-4">🔍</div>
                <p class="text-lg">{{ __('app.analyzer.empty_grid_message') }}</p>
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
.sudoku-input-grid input::-webkit-outer-spin-button,
.sudoku-input-grid input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.sudoku-input-grid input[type=number] {
    -moz-appearance: textfield;
}
</style>
@endpush


