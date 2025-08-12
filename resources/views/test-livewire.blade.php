<x-app-layout>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Test Livewire Component</h1>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow">
            <h2 class="text-xl font-bold mb-4">Basic Test</h2>
            <p>This should load without the Sudoku board component.</p>
        </div>
        
        <div class="mt-8 bg-white dark:bg-gray-800 rounded-lg p-6 shadow">
            <h2 class="text-xl font-bold mb-4">Simple Sudoku Test</h2>
            <p>Loading a simple Sudoku component below:</p>
            
            @livewire('simple-sudoku')
        </div>
        
        <div class="mt-8 bg-white dark:bg-gray-800 rounded-lg p-6 shadow">
            <h2 class="text-xl font-bold mb-4">Full Sudoku Board Test</h2>
            <p>Loading the full Sudoku board component below:</p>
            
            @livewire('sudoku-board')
        </div>
    </div>
</x-app-layout>
