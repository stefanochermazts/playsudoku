<div class="p-4 bg-white dark:bg-gray-800 rounded-lg">
    <h3 class="text-lg font-bold mb-4">Simple Sudoku Test</h3>
    <p>This is a basic Livewire component test.</p>
    
    <div class="mt-4 grid grid-cols-9 gap-1 w-64 h-64 border-2 border-gray-800">
        @for($row = 0; $row < 9; $row++)
            @for($col = 0; $col < 9; $col++)
                <div class="border border-gray-300 flex items-center justify-center text-sm">
                    {{ $grid[$row][$col] ?? '' }}
                </div>
            @endfor
        @endfor
    </div>
</div>