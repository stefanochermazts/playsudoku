@php
    $steps = $steps ?? [];
    $techniques = $techniques ?? [];
@endphp

@if(is_array($steps) && count($steps) > 0)
    <div>
        <span class="text-gray-600 dark:text-gray-400 text-sm">
            🔍 Passi di Risoluzione ({{ count($steps) }}):
        </span>
        <div class="mt-2 max-h-64 overflow-y-auto space-y-2">
            @foreach($steps as $index => $step)
                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded border-l-4 border-green-500">
                    <div class="flex items-start justify-between mb-1">
                        <span class="text-xs font-semibold text-gray-900 dark:text-white">
                            Passo {{ $index + 1 }}
                            @if(isset($techniques[$index]))
                                : {{ str_replace('_', ' ', ucfirst($techniques[$index])) }}
                            @endif
                        </span>
                        @if(isset($step['row']) && isset($step['col']))
                            <span class="text-xs px-2 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded font-mono">
                                R{{ $step['row'] + 1 }}C{{ $step['col'] + 1 }}
                                @if(isset($step['value']))
                                     = {{ $step['value'] }}
                                @endif
                            </span>
                        @endif
                    </div>
                    @if(isset($step['description']))
                        <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">{{ $step['description'] }}</p>
                    @endif
                    @if(isset($step['reason']))
                        <p class="text-xs text-blue-600 dark:text-blue-400 italic">💡 {{ $step['reason'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif