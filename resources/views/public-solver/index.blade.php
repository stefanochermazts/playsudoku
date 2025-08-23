<x-site-layout>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                🤖 AI Sudoku Solver
            </h1>
            <p class="text-lg text-gray-600 dark:text-gray-400 mb-6">
                Enter any Sudoku puzzle and get the complete solution with step-by-step explanation.
            </p>
        </div>

        <div class="max-w-4xl mx-auto mb-12">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-lg">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                    📝 Enter Your Puzzle
                </h2>
                
                <div class="mb-6">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">🎯 
                        @switch(app()->getLocale())
                            @case('it') Carica Puzzle per Difficoltà @break
                            @case('de') Puzzle nach Schwierigkeit laden @break
                            @case('es') Cargar Puzzle por Dificultad @break
                            @default Load Puzzle by Difficulty
                        @endswitch
                    </h3>
                    <div class="flex flex-wrap gap-3">
                        <button id="btn-easy" type="button" onclick="loadByDifficulty('easy')" class="difficulty-btn px-4 py-2 rounded-full text-white font-medium bg-emerald-500 hover:bg-emerald-600 flex items-center gap-2">
                            <span class="btn-text">@switch(app()->getLocale()) @case('it') Facile @break @default Easy @endswitch</span>
                            <span class="btn-spinner hidden"><span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full inline-block animate-spin"></span></span>
                        </button>
                        <button id="btn-medium" type="button" onclick="loadByDifficulty('medium')" class="difficulty-btn px-4 py-2 rounded-full text-white font-medium bg-blue-500 hover:bg-blue-600 flex items-center gap-2">
                            <span class="btn-text">@switch(app()->getLocale()) @case('it') Normale @break @default Normal @endswitch</span>
                            <span class="btn-spinner hidden"><span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full inline-block animate-spin"></span></span>
                        </button>
                        <button id="btn-hard" type="button" onclick="loadByDifficulty('hard')" class="difficulty-btn px-4 py-2 rounded-full text-white font-medium bg-amber-500 hover:bg-amber-600 flex items-center gap-2">
                            <span class="btn-text">@switch(app()->getLocale()) @case('it') Difficile @break @default Hard @endswitch</span>
                            <span class="btn-spinner hidden"><span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full inline-block animate-spin"></span></span>
                        </button>
                        <button id="btn-expert" type="button" onclick="loadByDifficulty('expert')" class="difficulty-btn px-4 py-2 rounded-full text-white font-medium bg-rose-500 hover:bg-rose-600 flex items-center gap-2">
                            <span class="btn-text">@switch(app()->getLocale()) @case('it') Esperto @break @default Expert @endswitch</span>
                            <span class="btn-spinner hidden"><span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full inline-block animate-spin"></span></span>
                        </button>
                        <button id="btn-crazy" type="button" onclick="loadByDifficulty('crazy')" class="difficulty-btn px-4 py-2 rounded-full text-white font-medium bg-fuchsia-600 hover:bg-fuchsia-700 flex items-center gap-2">
                            <span class="btn-text">@switch(app()->getLocale()) @case('it') Folle @break @default Crazy @endswitch</span>
                            <span class="btn-spinner hidden"><span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full inline-block animate-spin"></span></span>
                        </button>
                    </div>
                </div>
                
                <div class="grid lg:grid-cols-2 gap-8">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            Interactive Grid
                        </h3>
                        
                        <div id="solver-grid" class="mb-4">
                            <div class="flex justify-center">
                                <div class="sudoku-board bg-white dark:bg-gray-800 border-4 border-gray-800 dark:border-gray-400 rounded-lg p-1">
                                    <div class="sudoku-grid">
                                        @for ($row = 0; $row < 9; $row++)
                                            @for ($col = 0; $col < 9; $col++)
                                                @php
                                                    $borderStyles = [];
                                                    if ($row % 3 === 0 && $row > 0) $borderStyles[] = 'border-t-4';
                                                    if ($col % 3 === 0 && $col > 0) $borderStyles[] = 'border-l-4';
                                                    $borderClass = implode(' ', $borderStyles);
                                                @endphp
                                                <input 
                                                    type="tel" 
                                                    inputmode="numeric"
                                                    pattern="[0-9]*"
                                                    maxlength="1"
                                                    data-row="{{ $row }}" 
                                                    data-col="{{ $col }}"
                                                    class="sudoku-cell {{ $borderClass }}"
                                                    oninput="validateSudokuInput(this)"
                                                />
                                            @endfor
                                        @endfor
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex flex-wrap gap-2 mb-4">
                            <button onclick="clearGrid()" 
                                    class="px-3 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 text-sm">
                                Clear
                            </button>
                            <button onclick="loadExample()" 
                                    class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                Example
                            </button>
                            <button id="solve-btn" onclick="solvePuzzle()" 
                                    class="px-6 py-2 bg-green-600 text-white rounded hover:bg-green-700 font-medium">
                                🚀 Solve
                            </button>
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            📊 Analysis Results
                        </h3>
                        
                        <div id="solver-results" class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 min-h-64">
                            <p class="text-gray-600 dark:text-gray-400 text-center">
                                Enter a puzzle and click "Solve" to see results here.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Sudoku Board Styling */
.sudoku-board {
    width: 450px;
    height: 450px;
    max-width: 90vw;
    aspect-ratio: 1;
}

.sudoku-grid {
    display: grid;
    grid-template-columns: repeat(9, 1fr);
    grid-template-rows: repeat(9, 1fr);
    gap: 1px;
    width: 100%;
    height: 100%;
    background-color: #374151; /* Gray-700 */
}

.sudoku-cell {
    width: 100%;
    height: 100%;
    text-align: center;
    font-size: 1.25rem;
    font-weight: bold;
    font-family: ui-monospace, SFMono-Regular, "SF Mono", Consolas, "Liberation Mono", Menlo, monospace;
    background-color: white;
    color: #1f2937; /* Gray-900 */
    border: none;
    outline: none;
    transition: all 0.15s ease-in-out;
}

.dark .sudoku-cell {
    background-color: #374151; /* Gray-700 */
    color: white;
}

.sudoku-cell:focus {
    background-color: #dbeafe; /* Blue-50 */
    box-shadow: inset 0 0 0 2px #3b82f6; /* Blue-500 */
}

.dark .sudoku-cell:focus {
    background-color: #1e3a8a; /* Blue-900 */
    box-shadow: inset 0 0 0 2px #60a5fa; /* Blue-400 */
}

.sudoku-cell.border-t-4 {
    border-top: 3px solid #374151;
}

.dark .sudoku-cell.border-t-4 {
    border-top: 3px solid #9ca3af;
}

.sudoku-cell.border-l-4 {
    border-left: 3px solid #374151;
}

.dark .sudoku-cell.border-l-4 {
    border-left: 3px solid #9ca3af;
}

/* Solved cells styling */
.sudoku-cell.solved {
    background-color: #dcfce7 !important; /* Green-50 */
    color: #166534 !important; /* Green-800 */
}

.dark .sudoku-cell.solved {
    background-color: #14532d !important; /* Green-900 */
    color: #86efac !important; /* Green-300 */
}

/* Remove input number spinners */
.sudoku-cell::-webkit-outer-spin-button,
.sudoku-cell::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.sudoku-cell[type=number] {
    -moz-appearance: textfield;
}

/* Responsive sizing */
@media (max-width: 640px) {
    .sudoku-board {
        width: 350px;
        height: 350px;
    }
    
    .sudoku-cell {
        font-size: 1rem;
    }
}

@media (max-width: 480px) {
    .sudoku-board {
        width: 300px;
        height: 300px;
    }
    
    .sudoku-cell {
        font-size: 0.875rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Sudoku Solver Frontend Logic
let currentGrid = Array(9).fill(null).map(() => Array(9).fill(0));

function validateSudokuInput(input) {
    const value = input.value;
    
    if (value && (!/^[1-9]$/.test(value))) {
        input.value = '';
        return;
    }
    
    const row = parseInt(input.dataset.row);
    const col = parseInt(input.dataset.col);
    currentGrid[row][col] = value ? parseInt(value) : 0;
}

function clearGrid() {
    const inputs = document.querySelectorAll('#solver-grid input');
    inputs.forEach(input => {
        input.value = '';
    });
    currentGrid = Array(9).fill(null).map(() => Array(9).fill(0));
    document.getElementById('solver-results').innerHTML = `
        <p class="text-gray-600 dark:text-gray-400 text-center">
            Enter a puzzle and click "Solve" to see results here.
        </p>
    `;
}

function loadExample() {
    // Puzzle molto facile - principalmente Naked Singles
    const exampleGrid = [
        [5,3,0,0,7,0,0,0,0],
        [6,0,0,1,9,5,0,0,0],
        [0,9,8,0,0,0,0,6,0],
        [8,0,0,0,6,0,0,0,3],
        [4,0,0,8,0,3,0,0,1],
        [7,0,0,0,2,0,0,0,6],
        [0,6,0,0,0,0,2,8,0],
        [0,0,0,4,1,9,0,0,5],
        [0,0,0,0,8,0,0,7,9]
    ];
    
    const inputs = document.querySelectorAll('#solver-grid input');
    inputs.forEach((input, index) => {
        const row = Math.floor(index / 9);
        const col = index % 9;
        const value = exampleGrid[row][col];
        input.value = value === 0 ? '' : value.toString();
        currentGrid[row][col] = value;
    });
}

async function loadByDifficulty(difficulty) {
    showButtonLoading(difficulty);
    try {
        const response = await fetch('{{ route("api.public-solver.generate") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ difficulty })
        });

        const result = await response.json();
        if (!response.ok) throw new Error(result.error || 'Failed to generate');

        // Popola grid
        const grid = result.grid;
        const inputs = document.querySelectorAll('#solver-grid input');
        inputs.forEach((input, index) => {
            const row = Math.floor(index / 9);
            const col = index % 9;
            const value = grid[row][col] || 0;
            input.value = value === 0 ? '' : value.toString();
        });
        currentGrid = grid;

        // Reset risultati
        document.getElementById('solver-results').innerHTML = `
            <p class="text-gray-600 dark:text-gray-400 text-center">
                @switch(app()->getLocale())
                    @case('it') Puzzle caricato. Premi "Solve" per risolvere. @break
                    @case('de') Puzzle geladen. Klicke auf "Solve" um zu lösen. @break
                    @case('es') Puzzle cargado. Pulsa "Solve" para resolver. @break
                    @default Puzzle loaded. Press "Solve" to solve.
                @endswitch
            </p>`;
        hideButtonLoading();
    } catch (e) {
        hideButtonLoading();
        alert('Failed to load puzzle: ' + e.message);
    }
}

function showButtonLoading(difficulty) {
    const buttons = document.querySelectorAll('.difficulty-btn');
    buttons.forEach(btn => {
        btn.disabled = true;
        const spinner = btn.querySelector('.btn-spinner');
        const text = btn.querySelector('.btn-text');
        if (spinner) spinner.classList.add('hidden');
        if (text) text.classList.remove('hidden');
    });
    const activeBtn = document.getElementById(`btn-${difficulty}`);
    if (activeBtn) {
        const spinner = activeBtn.querySelector('.btn-spinner');
        const text = activeBtn.querySelector('.btn-text');
        if (spinner) spinner.classList.remove('hidden');
        if (text) text.classList.add('hidden');
    }
}

function hideButtonLoading() {
    const buttons = document.querySelectorAll('.difficulty-btn');
    buttons.forEach(btn => {
        btn.disabled = false;
        const spinner = btn.querySelector('.btn-spinner');
        const text = btn.querySelector('.btn-text');
        if (spinner) spinner.classList.add('hidden');
        if (text) text.classList.remove('hidden');
    });
}

async function solvePuzzle() {
    const solveBtn = document.getElementById('solve-btn');
    const resultsDiv = document.getElementById('solver-results');
    
    const hasValues = currentGrid.some(row => row.some(cell => cell !== 0));
    if (!hasValues) {
        resultsDiv.innerHTML = `
            <div class="text-red-600 dark:text-red-400 text-center">
                <p class="font-medium">⚠️ Inserisci almeno alcuni numeri nel puzzle prima di risolverlo.</p>
            </div>
        `;
        return;
    }
    
    // Save original grid for "Show Original" functionality
    originalGrid = currentGrid.map(row => [...row]);
    
    solveBtn.disabled = true;
    solveBtn.textContent = '⏳ Solving...';
    
    resultsDiv.innerHTML = `
        <div class="text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Analyzing puzzle...</p>
        </div>
    `;
    
    try {
        const response = await fetch('{{ route("api.public-solver.solve") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                grid: currentGrid,
                step_by_step: true
            })
        });
        
        const result = await response.json();
        
        if (!response.ok) {
            throw new Error(result.error || 'Failed to solve puzzle');
        }
        
        displayResults(result);
        
    } catch (error) {
        console.error('Solving error:', error);
        resultsDiv.innerHTML = `
            <div class="text-red-600 dark:text-red-400 text-center">
                <p class="font-medium">❌ Errore nella risoluzione del puzzle.</p>
                <p class="text-sm mt-1">${error.message}</p>
            </div>
        `;
    } finally {
        solveBtn.disabled = false;
        solveBtn.innerHTML = '🚀 Solve';
    }
}

function displayResults(result) {
    const resultsDiv = document.getElementById('solver-results');
    
    if (!result.is_solvable) {
        resultsDiv.innerHTML = `
            <div class="text-yellow-600 dark:text-yellow-400 text-center">
                <p class="font-medium">⚠️ Questo puzzle non può essere risolto logicamente.</p>
                <p class="text-sm mt-1">Controlla che i numeri inseriti siano corretti.</p>
            </div>
        `;
        return;
    }
    
    // Fill the grid with the solution
    if (result.solution) {
        fillGridWithSolution(result.solution);
    }
    
    const techniques = (result.techniques_used || []).map(t => t.replace(/_/g, ' '));
    const difficulty = result.difficulty || 'unknown';
    const solvingTime = result.solving_time_ms || 0;
    const steps = result.steps || [];
    
    // Funzione per ottenere il nome tecnica in italiano
    function getTechniqueName(technique) {
        const names = {
            'naked_singles': 'Singolo Nudo',
            'hidden_singles': 'Singolo Nascosto', 
            'locked_candidates_pointing': 'Candidati Bloccati (Pointing)',
            'locked_candidates_claiming': 'Candidati Bloccati (Claiming)',
            'naked_pairs': 'Coppie Nude',
            'hidden_pairs': 'Coppie Nascoste',
            'naked_triples': 'Terzine Nude',
            'hidden_triples': 'Terzine Nascoste',
            'x_wing': 'X-Wing',
            'swordfish': 'Swordfish',
            'coloring': 'Colorazione',
            'simple_chains': 'Catene Semplici',
            'backtracking': 'Ricerca Esaustiva'
        };
        return names[technique] || technique.replace(/_/g, ' ');
    }

    // Conta tecniche
    const techCounts = {};
    techniques.forEach(tech => {
        techCounts[tech] = (techCounts[tech] || 0) + 1;
    });
    
    resultsDiv.innerHTML = `
        <div class="space-y-4">
            <div class="text-green-600 dark:text-green-400 text-center">
                <p class="font-bold text-lg">✅ Puzzle Risolto!</p>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div class="text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Difficoltà</p>
                    <p class="font-bold text-lg text-blue-600 dark:text-blue-400">${difficulty.charAt(0).toUpperCase() + difficulty.slice(1)}</p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Tempo</p>
                    <p class="font-bold text-lg text-purple-600 dark:text-purple-400">${solvingTime}ms</p>
                </div>
            </div>
            
            ${techniques.length > 0 && !techniques.includes('backtracking') ? `
            <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">📊 Tecniche Utilizzate:</p>
                    <div class="space-y-1">
                        ${Object.entries(techCounts).map(([tech, count]) => `
                            <div class="flex justify-between items-center p-2 bg-blue-50 dark:bg-blue-900/20 rounded">
                                <span class="font-medium">${getTechniqueName(tech)}</span>
                                <span class="px-2 py-1 bg-blue-600 text-white rounded-full text-xs">${count}</span>
                            </div>
                        `).join('')}
                    </div>
                </div>
            ` : `
                <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded">
                    <p class="text-yellow-800 dark:text-yellow-200 text-sm text-center">
                        ⚠️ Risolto con ricerca esaustiva - puzzle molto difficile!
                    </p>
                </div>
            `}
            
            ${steps.length > 0 ? `
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">🔍 Passi di Risoluzione (${steps.length}):</p>
                    <div class="max-h-48 overflow-y-auto space-y-2">
                        ${steps.map((step, index) => {
                            const technique = techniques[index] || 'unknown';
                            const techniqueDisplay = getTechniqueName(technique);
                            
                            return `
                                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg border-l-4 border-green-500">
                                    <div class="flex items-start justify-between mb-1">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                            Passo ${index + 1}: ${techniqueDisplay}
                                        </span>
                                        ${step.row !== undefined && step.col !== undefined ? `
                                            <span class="text-xs px-2 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded">
                                                R${step.row + 1}C${step.col + 1} ${step.value ? `= ${step.value}` : ''}
                                            </span>
                                        ` : ''}
                                    </div>
                                    ${step.description ? `
                                        <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">
                                            ${step.description}
                                        </p>
                                    ` : ''}
                                    ${step.reason ? `
                                        <p class="text-xs text-blue-600 dark:text-blue-400 italic">
                                            💡 ${step.reason}
                                        </p>
                                    ` : ''}
                                </div>
                            `;
                        }).join('')}
                </div>
            </div>
            ` : ''}
            
            <div class="text-center space-y-2">
                <button onclick="showOriginalGrid()" 
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm mr-2">
                    👁️ Mostra Originale
                </button>
                <button onclick="submitForPermanentUrl()" 
                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                    🔗 Link Permanente
                </button>
            </div>
        </div>
    `;
}

function fillGridWithSolution(solution) {
    const inputs = document.querySelectorAll('#solver-grid input');
    inputs.forEach((input) => {
        const row = parseInt(input.getAttribute('data-row'));
        const col = parseInt(input.getAttribute('data-col'));
        const value = solution[row][col];

        input.value = value.toString();

        // Mark solved cells with different styling
        if (originalGrid && originalGrid[row][col] === 0) {
            input.classList.add('solved');
        }
    });
}

let originalGrid = null;

function showOriginalGrid() {
    if (originalGrid) {
        const inputs = document.querySelectorAll('#solver-grid input');
        inputs.forEach((input) => {
            const row = parseInt(input.getAttribute('data-row'));
            const col = parseInt(input.getAttribute('data-col'));
            const value = originalGrid[row][col];
            
            input.value = value === 0 ? '' : value.toString();
            
            // Reset styling
            input.classList.remove('solved');
        });
    }
}

async function submitForPermanentUrl() {
    try {
        const response = await fetch('{{ route("api.public-solver.submit") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                grid: currentGrid
            })
        });
        
        const result = await response.json();
        
        if (response.ok) {
            window.open(result.url, '_blank');
        }
        
    } catch (error) {
        console.error('Submit error:', error);
        alert('Failed to generate permanent link');
    }
}
</script>
@endpush

{{-- Structured data JSON-LD temporaneamente disabilitato --}}
</x-site-layout>