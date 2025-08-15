<x-site-layout>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
        {{-- Header --}}
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                {{ __('app.training.analyzer_title') }}
            </h1>
            <p class="text-lg text-gray-600 dark:text-gray-400 mb-6">
                {{ __('app.training.analyzer_subtitle') }}
            </p>
            
            <div class="flex flex-wrap justify-center gap-4 mb-6">
                <a href="{{ route('localized.sudoku.training', ['locale' => app()->getLocale()]) }}" 
                   class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 
                          focus:outline-none focus:ring-2 focus:ring-gray-500 font-medium">
                    {{ __('app.training.back_to_training') }}
                </a>
                <a href="{{ route('localized.sudoku.play', ['locale' => app()->getLocale()]) }}" 
                   class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 
                          focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                    {{ __('app.training.play') }}
                </a>
            </div>
        </div>

        {{-- Componente Analyzer --}}
        <div>
            @livewire('puzzle-analyzer', [], key('puzzle-analyzer'))
        </div>

        {{-- Informazioni --}}
        <div class="mt-8 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
            <h3 class="text-lg font-bold text-blue-900 dark:text-blue-100 mb-4">
                {{ __('app.training.how_it_works') }}
            </h3>
            <div class="grid md:grid-cols-2 gap-6 text-sm text-blue-800 dark:text-blue-200">
                <div>
                    <h4 class="font-semibold mb-2">{{ __('app.training.input_methods') }}</h4>
                    <ul class="space-y-1">
                        <li>• {!! __('app.training.input_manual') !!}</li>
                        <li>• {!! __('app.training.input_json') !!}</li>
                        <li>• {!! __('app.training.input_example') !!}</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-2">{{ __('app.training.report_generated') }}</h4>
                    <ul class="space-y-1">
                        <li>• {{ __('app.training.report_techniques') }}</li>
                        <li>• {{ __('app.training.report_stats') }}</li>
                        <li>• {{ __('app.training.report_solution') }}</li>
                        <li>• {{ __('app.training.report_difficulty') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
</x-site-layout>


