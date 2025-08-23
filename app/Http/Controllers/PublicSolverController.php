<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Sudoku\Contracts\SolverInterface;
use App\Domain\Sudoku\Contracts\DifficultyRaterInterface;
use App\Domain\Sudoku\Grid;
use App\Jobs\ProcessPublicPuzzleJob;
use App\Models\PublicPuzzle;
use App\Services\MetaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PublicSolverController extends Controller
{
    public function __construct(
        private SolverInterface $solver,
        private DifficultyRaterInterface $difficultyRater,
        private MetaService $metaService,
    ) {}

    /**
     * Pagina principale del Sudoku Solver AI pubblico
     */
    public function index(): Response
    {
        // Configura SEO meta tags per la pagina solver
        $this->metaService->setPage(
            title: match(app()->getLocale()) {
                'it' => 'Risolvi Sudoku Online | Solver AI Gratuito Step-by-Step',
                'de' => 'Sudoku Online Lösen | Kostenloser KI Solver Schritt-für-Schritt',
                'es' => 'Resolver Sudoku Online | Solucionador IA Gratis Paso a Paso',
                default => 'Solve Sudoku Online | Free AI Solver Step-by-Step',
            },
            description: match(app()->getLocale()) {
                'it' => 'Risolvi qualsiasi puzzle Sudoku con il nostro solver IA gratuito. Spiegazione passo-passo, tecniche avanzate, analisi difficoltà. Perfetto per imparare!',
                'de' => 'Löse jedes Sudoku-Rätsel mit unserem kostenlosen KI-Löser. Schritt-für-Schritt-Erklärung, fortgeschrittene Techniken, Schwierigkeitsanalyse. Perfekt zum Lernen!',
                'es' => 'Resuelve cualquier puzzle Sudoku con nuestro solucionador IA gratuito. Explicación paso a paso, técnicas avanzadas, análisis de dificultad. ¡Perfecto para aprender!',
                default => 'Solve any Sudoku puzzle with our free AI solver. Step-by-step explanation, advanced techniques, difficulty analysis. Perfect for learning!',
            },
            options: ['url' => request()->url()]
        );

        // Schema.org configurato direttamente nella view se necessario

        // Carica puzzle più popolari per showcase
        $popularPuzzles = Cache::remember('public_solver_popular', 3600, function () {
            return PublicPuzzle::processed()
                ->solvable()
                ->mostViewed(8)
                ->select(['hash', 'difficulty', 'seo_title', 'view_count', 'techniques_used'])
                ->get();
        });

        return response()->view('public-solver.index', [
            'metaService' => $this->metaService,
            'popularPuzzles' => $popularPuzzles,
        ]);
    }

    /**
     * Genera un puzzle per difficoltà (senza persistenza)
     */
    public function generate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'difficulty' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $difficultyInput = strtolower($request->string('difficulty')->toString());
        // Normalizza valori accettati
        $map = [
            'facile' => 'easy',
            'easy' => 'easy',
            'normale' => 'medium',
            'medio' => 'medium',
            'medium' => 'medium',
            'difficile' => 'hard',
            'hard' => 'hard',
            'esperto' => 'expert',
            'expert' => 'expert',
            'folle' => 'crazy',
            'evil' => 'crazy',
            'crazy' => 'crazy',
        ];
        $difficulty = $map[$difficultyInput] ?? 'medium';

        try {
            // Risolvi generator e rater dal container
            /** @var \App\Domain\Sudoku\Contracts\GeneratorInterface $generator */
            $generator = app(\App\Domain\Sudoku\Contracts\GeneratorInterface::class);
            $seed = random_int(1000, 999999);
            $grid = $generator->generatePuzzleWithDifficulty($seed, $difficulty);

            // Valuta difficoltà effettiva
            $rated = $this->difficultyRater->rateDifficulty($grid);

            return response()->json([
                'seed' => $seed,
                'requested_difficulty' => $difficulty,
                'difficulty' => $rated,
                'grid' => $grid->toArray(),
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Failed to generate puzzle',
                'details' => app()->environment('local') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Landing page SEO per puzzle specifico risolto
     */
    public function show(string $hash): Response
    {
        $puzzle = PublicPuzzle::where('hash', $hash)->first();
        
        if (!$puzzle) {
            abort(404, 'Puzzle not found');
        }

        // Se il puzzle è ancora pending, processarlo al volo
        if ($puzzle->status === 'pending') {
            try {
                $grid = Grid::fromArray($puzzle->grid_data);
                $result = $this->solver->solve($grid);
                $puzzle->markAsProcessed($result);
                $puzzle->refresh(); // Ricarica i dati dal database
            } catch (\Exception $e) {
                // Se non riusciamo a processarlo, mostriamolo comunque
                logger()->error('Failed to process puzzle on-the-fly', ['hash' => $hash, 'error' => $e->getMessage()]);
            }
        }

        // Se mancano i passi dettagliati, rigenera una sola volta lato view
        if (empty($puzzle->solver_steps) || (is_array($puzzle->solver_steps) && count($puzzle->solver_steps) === 0)) {
            try {
                $grid = Grid::fromArray($puzzle->grid_data);
                $startTime = microtime(true);
                $result = $this->solver->solve($grid);
                $solvingTime = (int) round((microtime(true) - $startTime) * 1000);

                $puzzle->update([
                    'solution_data'   => $result['grid']?->toArray(),
                    'solver_steps'    => $result['steps'] ?? [],
                    'techniques_used' => $result['techniques'] ?? [],
                    'solving_time_ms' => $puzzle->solving_time_ms ?: $solvingTime,
                    'is_solvable'     => $result['grid'] !== null,
                ]);
                $puzzle->refresh();
            } catch (\Throwable $e) {
                logger()->warning('Unable to regenerate solver steps on view', [
                    'hash' => $hash,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Incrementa view count
        $puzzle->incrementViewCount();

        // Configura SEO meta tags dinamici 
        $title = $puzzle->seo_title ?? "Risolvi questo Puzzle Sudoku Online - Passo dopo Passo";
        $description = $puzzle->seo_description ?? "Solver Sudoku gratuito con spiegazione dettagliata.";
        
        $this->metaService->setPage(
            title: $title,
            description: $description,
            options: [
                'url' => $puzzle->canonical_url ?: request()->url(),
                'image' => url('/favicon.svg'), // Use existing favicon as fallback
            ]
        );

        // Schema.org per il puzzle verrà configurato nella view

        return response()->view('public-solver.show', [
            'puzzle' => $puzzle,
            'metaService' => $this->metaService,
        ]);
    }

    /**
     * Sottometti un puzzle per la risoluzione
     */
    public function submit(Request $request): JsonResponse
    {
        // Rate limiting per prevenire spam
        $key = 'public_solver_submit:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            return response()->json([
                'error' => 'Too many attempts. Please try again later.'
            ], 429);
        }

        RateLimiter::increment($key, 300); // 5 minutes window

        $validator = Validator::make($request->all(), [
            'grid' => ['required', 'array', 'size:9'],
            'grid.*' => ['array', 'size:9'],
            'grid.*.*' => ['nullable', 'integer', 'between:0,9'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $gridData = $request->input('grid');
        $hash = PublicPuzzle::generateHash($gridData);

        try {
            DB::beginTransaction();

            // Verifica se il puzzle esiste già
            $existingPuzzle = PublicPuzzle::where('hash', $hash)->first();
            
            if ($existingPuzzle) {
                DB::commit();
                return response()->json([
                    'hash' => $hash,
                    'url' => $existingPuzzle->getPublicUrl(),
                    'status' => $existingPuzzle->status,
                    'existing' => true,
                ]);
            }

            // Crea nuovo puzzle
            $puzzle = PublicPuzzle::createFromGrid($gridData, [
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Dispatch job asincrono per processare il puzzle
            ProcessPublicPuzzleJob::dispatch($puzzle);

            DB::commit();

            return response()->json([
                'hash' => $hash,
                'url' => $puzzle->getPublicUrl(),
                'status' => 'pending',
                'existing' => false,
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'error' => 'Failed to process puzzle. Please try again.'
            ], 500);
        }
    }

    /**
     * API endpoint per risolvere puzzle al volo (senza persistenza)
     */
    public function solve(Request $request): JsonResponse
    {
        // Rate limiting più stringente per API solve
        $key = 'public_solver_api:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json([
                'error' => 'Too many API requests. Please try again later.'
            ], 429);
        }

        RateLimiter::increment($key, 60); // 1 minute window

        $validator = Validator::make($request->all(), [
            'grid' => ['required', 'array', 'size:9'],
            'grid.*' => ['array', 'size:9'],
            'grid.*.*' => ['nullable', 'integer', 'between:0,9'],
            'step_by_step' => ['boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $gridData = $request->input('grid');
            $stepByStep = $request->boolean('step_by_step', false);
            
            // Converte in oggetto Grid del dominio
            $grid = Grid::fromArray($gridData);
            
            // Calcola difficoltà
            $difficulty = $this->difficultyRater->rateDifficulty($grid);
            
            // Risolve il puzzle
            $startTime = microtime(true);
            $result = $this->solver->solve($grid);
            $endTime = microtime(true);
            
            $solvingTime = round(($endTime - $startTime) * 1000); // in milliseconds

            return response()->json([
                'original_grid' => $gridData,
                'solution' => $result['grid']?->toArray(),
                'is_solvable' => $result['grid'] !== null,
                'difficulty' => $difficulty,
                'techniques_used' => $result['techniques'],
                'solving_time_ms' => $solvingTime,
                'steps' => $stepByStep ? $result['steps'] : null,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to solve puzzle. Please check your input.',
                'details' => app()->environment('local') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Endpoint per incrementare il contatore di condivisioni
     */
    public function share(string $hash): JsonResponse
    {
        $puzzle = PublicPuzzle::where('hash', $hash)->first();
        
        if (!$puzzle) {
            return response()->json(['error' => 'Puzzle not found'], 404);
        }

        $puzzle->incrementShareCount();

        return response()->json(['message' => 'Share count incremented']);
    }

    /**
     * API per ottenere statistiche pubbliche
     */
    public function stats(): JsonResponse
    {
        $stats = Cache::remember('public_solver_stats', 1800, function () {
            return [
                'total_puzzles_solved' => PublicPuzzle::processed()->count(),
                'total_views' => PublicPuzzle::sum('view_count'),
                'average_solving_time' => PublicPuzzle::processed()->avg('solving_time_ms'),
                'difficulty_distribution' => PublicPuzzle::processed()
                    ->selectRaw('difficulty, COUNT(*) as count')
                    ->groupBy('difficulty')
                    ->pluck('count', 'difficulty')
                    ->toArray(),
                'most_used_techniques' => $this->getMostUsedTechniques(),
            ];
        });

        return response()->json($stats);
    }

    /**
     * Helper per ottenere le tecniche più utilizzate
     */
    private function getMostUsedTechniques(): array
    {
        $techniques = [];
        
        PublicPuzzle::processed()
            ->whereNotNull('techniques_used')
            ->chunk(100, function ($puzzles) use (&$techniques) {
                foreach ($puzzles as $puzzle) {
                    if (is_array($puzzle->techniques_used)) {
                        foreach ($puzzle->techniques_used as $technique) {
                            $techniques[$technique] = ($techniques[$technique] ?? 0) + 1;
                        }
                    }
                }
            });

        arsort($techniques);
        return array_slice($techniques, 0, 10, true);
    }
}
