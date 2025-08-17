/**
 * Ottimizzazioni JavaScript per SudokuBoard
 * Performance client-side per griglia 9x9 reattiva
 */

class SudokuBoardOptimizer {
    constructor() {
        this.lastInput = 0;
        this.inputThrottle = 50; // 50ms minimum tra input
        this.animationFrameId = null;
        this.pendingUpdates = new Set();
        this.cellCache = new Map();
        this.eventListeners = new Map();
        
        this.init();
    }

    init() {
        this.setupKeyboardOptimization();
        // Disabilitato il delegation dei click/touch: lasciamo gestire a Livewire
        // per evitare doppi handler e condizioni di race.
        // this.setupMouseOptimization();
        // this.setupTouchOptimization();
        this.setupRenderOptimization();
        this.setupMemoryManagement();
        
        console.log('🚀 SudokuBoard optimization loaded');
    }

    /**
     * Trova l'istanza Livewire del componente board più vicino alla griglia
     */
    getLivewireBoardComponent(contextEl = null) {
        if (!window.Livewire) return null;
        let base = contextEl || document.getElementById('sudoku-main-grid') || document.querySelector('.sudoku-grid');
        if (!base) return null;
        const root = base.closest('[wire\\:id]');
        if (!root) return null;
        return window.Livewire.find(root.getAttribute('wire:id'));
    }

    /**
     * OTTIMIZZAZIONE: Gestione tastiera con debouncing
     */
    setupKeyboardOptimization() {
        let keyBuffer = '';
        let keyTimeout = null;
        
        const optimizedKeyHandler = (event) => {
            const now = Date.now();
            
            // Throttling - ignora input troppo rapidi
            if (now - this.lastInput < this.inputThrottle) {
                event.preventDefault();
                return false;
            }
            
            this.lastInput = now;
            
            // Buffer per input multi-carattere
            if (event.key >= '1' && event.key <= '9') {
                keyBuffer += event.key;
                
                // Clear buffer dopo 500ms
                clearTimeout(keyTimeout);
                keyTimeout = setTimeout(() => {
                    keyBuffer = '';
                }, 500);
                
                // Processa solo ultimo carattere se buffer pieno
                if (keyBuffer.length > 1) {
                    keyBuffer = event.key;
                }
            }
            
            // Batch update per miglior performance
            this.scheduleUpdate(() => {
                this.processKeyInput(event.key, keyBuffer);
            });
        };
        
        document.addEventListener('keydown', optimizedKeyHandler, { passive: false });
        this.eventListeners.set('keydown', optimizedKeyHandler);
    }

    /**
     * OTTIMIZZAZIONE: Gestione mouse con event delegation
     */
    setupMouseOptimization() {
        // Event delegation invece di 81 event listeners
        const optimizedMouseHandler = (event) => {
            const cell = event.target.closest('.sudoku-cell');
            if (!cell) return;
            
            const row = parseInt(cell.dataset.row);
            const col = parseInt(cell.dataset.col);
            
            if (isNaN(row) || isNaN(col)) return;
            
            // Throttling per click rapidi
            const now = Date.now();
            if (now - this.lastInput < this.inputThrottle) return;
            this.lastInput = now;
            
            this.scheduleUpdate(() => {
                this.processCellClick(row, col, event);
            });
        };
        
        const boardElement = document.querySelector('.sudoku-grid');
        if (boardElement) {
            boardElement.addEventListener('click', optimizedMouseHandler, { passive: true });
            this.eventListeners.set('click', optimizedMouseHandler);
        }
    }

    /**
     * OTTIMIZZAZIONE: Gestione touch per mobile
     */
    setupTouchOptimization() {
        let touchStartTime = 0;
        let lastTouchEnd = 0;
        
        const optimizedTouchStart = (event) => {
            touchStartTime = Date.now();
        };
        
        const optimizedTouchEnd = (event) => {
            const now = Date.now();
            const touchDuration = now - touchStartTime;
            const timeSinceLastTouch = now - lastTouchEnd;
            
            // Ignora tap troppo veloci (double-tap accidentali)
            if (touchDuration < 50 || timeSinceLastTouch < 100) {
                event.preventDefault();
                return;
            }
            
            lastTouchEnd = now;
            
            const cell = event.target.closest('.sudoku-cell');
            if (!cell) return;
            
            const row = parseInt(cell.dataset.row);
            const col = parseInt(cell.dataset.col);

            if (isNaN(row) || isNaN(col)) return;

            this.scheduleUpdate(() => {
                this.processCellClick(row, col, event);
            });
        };
        
        const boardElement = document.querySelector('.sudoku-grid');
        if (boardElement) {
            boardElement.addEventListener('touchstart', optimizedTouchStart, { passive: true });
            boardElement.addEventListener('touchend', optimizedTouchEnd, { passive: false });
            this.eventListeners.set('touchstart', optimizedTouchStart);
            this.eventListeners.set('touchend', optimizedTouchEnd);
        }
    }

    /**
     * OTTIMIZZAZIONE: Rendering con requestAnimationFrame
     */
    setupRenderOptimization() {
        // Intersection Observer per lazy loading di candidati
        if ('IntersectionObserver' in window) {
            this.candidateObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.loadCandidatesForCell(entry.target);
                    } else {
                        this.unloadCandidatesForCell(entry.target);
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: '50px'
            });
            
            // Osserva celle con candidati
            document.querySelectorAll('.sudoku-cell[data-has-candidates="true"]').forEach(cell => {
                this.candidateObserver.observe(cell);
            });
        }
        
        // Virtualized scrolling per pannello candidati se necessario
        this.setupVirtualScrolling();
    }

    /**
     * OTTIMIZZAZIONE: Virtual scrolling per candidati
     */
    setupVirtualScrolling() {
        const candidateContainer = document.querySelector('.candidates-container');
        if (!candidateContainer) return;
        
        const virtualScroller = {
            itemHeight: 30,
            visibleItems: 9,
            totalItems: 0,
            scrollTop: 0,
            
            update: () => {
                const startIndex = Math.floor(this.scrollTop / this.itemHeight);
                const endIndex = Math.min(startIndex + this.visibleItems, this.totalItems);
                
                // Renderizza solo elementi visibili
                this.renderVisibleCandidates(startIndex, endIndex);
            }
        };
        
        candidateContainer.addEventListener('scroll', () => {
            virtualScroller.scrollTop = candidateContainer.scrollTop;
            this.scheduleUpdate(() => virtualScroller.update());
        }, { passive: true });
    }

    /**
     * OTTIMIZZAZIONE: Memory management
     */
    setupMemoryManagement() {
        // Cleanup periodico
        setInterval(() => {
            this.cleanupCache();
        }, 30000); // Ogni 30 secondi
        
        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            this.cleanup();
        });
        
        // Cleanup su visibility change
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.cleanupCache();
            }
        });
    }

    /**
     * OTTIMIZZAZIONE: Scheduling updates con RAF
     */
    scheduleUpdate(callback) {
        if (this.animationFrameId) {
            cancelAnimationFrame(this.animationFrameId);
        }
        
        this.animationFrameId = requestAnimationFrame(() => {
            try {
                callback();
            } catch (error) {
                console.error('Sudoku update error:', error);
            } finally {
                this.animationFrameId = null;
            }
        });
    }

    /**
     * OTTIMIZZAZIONE: Processa input tastiera ottimizzato
     */
    processKeyInput(key, keyBuffer) {
        // Delega a Livewire ma con batching
        if (window.Livewire) {
            const component = this.getLivewireBoardComponent();
            if (component) {
                // Batch multiple key inputs
                if (!this.keyBatch) {
                    this.keyBatch = [];
                    setTimeout(() => {
                        component.call('handleKeyInputBatch', this.keyBatch);
                        this.keyBatch = null;
                    }, 10);
                }
                this.keyBatch.push(key);
            }
        }
    }

    /**
     * OTTIMIZZAZIONE: Processa click celle ottimizzato
     */
    processCellClick(row, col, event) {
        // Cache della cella per evitare re-query
        const cellKey = `${row}-${col}`;
        let cellInfo = this.cellCache.get(cellKey);
        
        if (!cellInfo) {
            const cellElement = document.querySelector(`[data-row="${row}"][data-col="${col}"]`);
            cellInfo = {
                element: cellElement,
                isGiven: cellElement?.classList.contains('given') || false,
                isEmpty: cellElement?.textContent.trim() === '' || false
            };
            this.cellCache.set(cellKey, cellInfo);
        }
        
        // Skip se cella given
        if (cellInfo.isGiven) return;
        
        // Delega a Livewire
        if (window.Livewire) {
            const component = this.getLivewireBoardComponent();
            if (component) {
                component.call('selectCell', row, col);
            }
        }
    }

    /**
     * OTTIMIZZAZIONE: Lazy loading candidati
     */
    loadCandidatesForCell(cellElement) {
        const row = parseInt(cellElement.dataset.row);
        const col = parseInt(cellElement.dataset.col);
        
        if (isNaN(row) || isNaN(col)) return;
        
        // Carica candidati solo quando visibili
        if (window.Livewire) {
            const component = this.getLivewireBoardComponent(cellElement);
            if (component) {
                component.call('loadCandidatesForCell', row, col);
            }
        }
    }

    /**
     * OTTIMIZZAZIONE: Unload candidati per memoria
     */
    unloadCandidatesForCell(cellElement) {
        const candidatesElement = cellElement.querySelector('.candidates');
        if (candidatesElement) {
            candidatesElement.innerHTML = ''; // Libera memoria
        }
    }

    /**
     * OTTIMIZZAZIONE: Cleanup cache
     */
    cleanupCache() {
        // Pulisci cache delle celle non visibili
        const visibleCells = new Set();
        document.querySelectorAll('.sudoku-cell:not([style*="display: none"])').forEach(cell => {
            const row = cell.dataset.row;
            const col = cell.dataset.col;
            if (row && col) {
                visibleCells.add(`${row}-${col}`);
            }
        });
        
        // Rimuovi dal cache celle non visibili
        for (const [key] of this.cellCache) {
            if (!visibleCells.has(key)) {
                this.cellCache.delete(key);
            }
        }
        
        // Pulisci update pendenti
        this.pendingUpdates.clear();
        
        console.log('🧹 Cache cleaned, size:', this.cellCache.size);
    }

    /**
     * OTTIMIZZAZIONE: Cleanup completo
     */
    cleanup() {
        // Cancella animation frame
        if (this.animationFrameId) {
            cancelAnimationFrame(this.animationFrameId);
        }
        
        // Rimuovi event listeners
        this.eventListeners.forEach((listener, event) => {
            document.removeEventListener(event, listener);
        });
        
        // Disconnetti observers
        if (this.candidateObserver) {
            this.candidateObserver.disconnect();
        }
        
        // Pulisci cache
        this.cellCache.clear();
        this.pendingUpdates.clear();
        
        console.log('🧹 SudokuBoard optimization cleaned up');
    }

    /**
     * API per integrare con Livewire
     */
    static initializeForLivewire() {
        // Inizializza quando Livewire è pronto
        if (window.Livewire) {
            window.sudokuOptimizer = new SudokuBoardOptimizer();
            
            // Hook nei lifecycle di Livewire
            window.Livewire.hook('component.init', () => {
                console.log('🔄 Livewire component initialized, refreshing optimizations');
                window.sudokuOptimizer?.setupRenderOptimization();
            });
            
        } else {
            // Retry se Livewire non ancora caricato
            setTimeout(() => SudokuBoardOptimizer.initializeForLivewire(), 100);
        }
    }

    /**
     * Metodi helper per debug performance
     */
    static measurePerformance(name, fn) {
        const start = performance.now();
        const result = fn();
        const end = performance.now();
        console.log(`⚡ ${name}: ${(end - start).toFixed(2)}ms`);
        return result;
    }

    static profileMemory() {
        if (performance.memory) {
            const memory = performance.memory;
            console.log('🧠 Memory usage:', {
                used: `${(memory.usedJSHeapSize / 1024 / 1024).toFixed(2)}MB`,
                allocated: `${(memory.totalJSHeapSize / 1024 / 1024).toFixed(2)}MB`,
                limit: `${(memory.jsHeapSizeLimit / 1024 / 1024).toFixed(2)}MB`
            });
        }
    }
}

// Auto-inizializzazione
document.addEventListener('DOMContentLoaded', () => {
    SudokuBoardOptimizer.initializeForLivewire();
});

// Export per uso globale
window.SudokuBoardOptimizer = SudokuBoardOptimizer;
