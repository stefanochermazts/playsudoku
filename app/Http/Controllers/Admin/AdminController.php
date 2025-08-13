<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Challenge;
use App\Models\Puzzle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Dashboard amministrativa con statistiche
     */
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'admin_users' => User::where('role', 'admin')->count(),
            'regular_users' => User::where('role', 'user')->count(),
            'users_today' => User::whereDate('created_at', today())->count(),
            'users_this_week' => User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'users_this_month' => User::whereMonth('created_at', now()->month)->count(),
            'total_challenges' => Challenge::count(),
            'active_challenges' => Challenge::active()->count(),
            'total_puzzles' => Puzzle::count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    /**
     * Lista utenti con paginazione
     */
    public function users(Request $request)
    {
        $query = User::query();

        // Filtro per ruolo
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Ricerca per nome o email
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.users', compact('users'));
    }

    /**
     * Mostra il form per creare un nuovo utente
     */
    public function createUser()
    {
        return view('admin.users.create');
    }

    /**
     * Salva un nuovo utente
     */
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:user,admin'],
            'email_verified' => ['nullable', 'boolean'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'email_verified_at' => $request->has('email_verified') && $request->boolean('email_verified') ? now() : null,
        ]);

        return redirect()->route('admin.users')
            ->with('success', 'Utente creato con successo!');
    }

    /**
     * Mostra i dettagli di un utente
     */
    public function showUser(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    /**
     * Mostra il form per modificare un utente
     */
    public function editUser(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Aggiorna un utente
     */
    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:user,admin'],
            'email_verified' => ['nullable', 'boolean'],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'email_verified_at' => $request->has('email_verified') && $request->boolean('email_verified') ? now() : null,
        ];

        // Aggiorna password solo se fornita
        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('admin.users')
            ->with('success', 'Utente aggiornato con successo!');
    }

    /**
     * Elimina un utente
     */
    public function destroyUser(User $user)
    {
        // Previeni l'eliminazione dell'ultimo admin
        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return redirect()->route('admin.users')
                ->with('error', 'Non puoi eliminare l\'ultimo amministratore!');
        }

        // Previeni l'auto-eliminazione
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users')
                ->with('error', 'Non puoi eliminare te stesso!');
        }

        $user->delete();

        return redirect()->route('admin.users')
            ->with('success', 'Utente eliminato con successo!');
    }

    /*
     * ================================
     * CHALLENGE MANAGEMENT
     * ================================
     */

    /**
     * Lista sfide con filtri e paginazione
     */
    public function challenges(Request $request)
    {
        $query = Challenge::with(['puzzle', 'creator']);

        // Filtro per tipo
        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        // Filtro per stato
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Ricerca per titolo
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $challenges = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.challenges.index', compact('challenges'));
    }

    /**
     * Mostra il form per creare una nuova sfida
     */
    public function createChallenge()
    {
        $puzzles = Puzzle::unassigned()->orderBy('created_at', 'desc')->get();
        return view('admin.challenges.create', compact('puzzles'));
    }

    /**
     * Salva una nuova sfida
     */
    public function storeChallenge(Request $request)
    {
        $validated = $request->validate([
            'puzzle_id' => ['required', 'exists:puzzles,id'],
            'type' => ['required', 'in:daily,weekly,custom'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'starts_at' => ['required', 'date', 'after_or_equal:now'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'visibility' => ['required', 'in:public,private'],
            'status' => ['required', 'in:draft,active,completed,cancelled'],
        ]);

        Challenge::create([
            ...$validated,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.challenges')
            ->with('success', 'Sfida creata con successo!');
    }

    /**
     * Mostra i dettagli di una sfida
     */
    public function showChallenge(Challenge $challenge)
    {
        $challenge->load(['puzzle', 'creator', 'attempts.user']);
        return view('admin.challenges.show', compact('challenge'));
    }

    /**
     * Mostra il form per modificare una sfida
     */
    public function editChallenge(Challenge $challenge)
    {
        // Include puzzle non assegnati + quello attualmente selezionato
        $puzzles = Puzzle::where(function($query) use ($challenge) {
            $query->unassigned()->orWhere('id', $challenge->puzzle_id);
        })->orderBy('created_at', 'desc')->get();
        
        return view('admin.challenges.edit', compact('challenge', 'puzzles'));
    }

    /**
     * Aggiorna una sfida
     */
    public function updateChallenge(Request $request, Challenge $challenge)
    {
        $validated = $request->validate([
            'puzzle_id' => ['required', 'exists:puzzles,id'],
            'type' => ['required', 'in:daily,weekly,custom'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'visibility' => ['required', 'in:public,private'],
            'status' => ['required', 'in:draft,active,completed,cancelled'],
        ]);

        $challenge->update($validated);

        return redirect()->route('admin.challenges')
            ->with('success', 'Sfida aggiornata con successo!');
    }

    /**
     * Elimina una sfida
     */
    public function destroyChallenge(Challenge $challenge)
    {
        // Elimina anche i tentativi associati
        $challenge->attempts()->delete();
        $challenge->delete();

        return redirect()->route('admin.challenges')
            ->with('success', 'Sfida eliminata con successo!');
    }

    /*
     * ================================
     * PUZZLE MANAGEMENT
     * ================================
     */

    /**
     * Lista puzzle con informazioni su assegnazioni
     */
    public function puzzles(Request $request)
    {
        $query = Puzzle::with(['challenges']);

        // Filtro per difficoltà
        if ($request->filled('difficulty')) {
            $query->byDifficulty($request->difficulty);
        }

        // Filtro per stato assegnazione
        if ($request->filled('assigned')) {
            if ($request->assigned === 'yes') {
                $query->has('challenges');
            } elseif ($request->assigned === 'no') {
                $query->unassigned();
            }
        }

        $puzzles = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.puzzles.index', compact('puzzles'));
    }

    /**
     * Mostra il form per generare puzzle
     */
    public function generatePuzzles()
    {
        return view('admin.puzzles.generate');
    }

    /**
     * Genera e salva nuovi puzzle
     */
    public function storeGeneratedPuzzles(Request $request)
    {
        $validated = $request->validate([
            'count' => ['required', 'integer', 'min:1', 'max:50'],
            'difficulty' => ['required', 'in:easy,medium,hard,expert'],
            'seed_base' => ['nullable', 'integer', 'min:1'],
        ]);

        $generator = app(\App\Domain\Sudoku\Contracts\GeneratorInterface::class);
        $difficultyRater = app(\App\Domain\Sudoku\Contracts\DifficultyRaterInterface::class);
        
        $count = $validated['count'];
        $targetDifficulty = $validated['difficulty'];
        $seedBase = $validated['seed_base'] ?? time();
        
        $generated = 0;
        $attempts = 0;
        $maxAttempts = $count * 10; // Evita loop infiniti

        while ($generated < $count && $attempts < $maxAttempts) {
            $attempts++;
            $seed = $seedBase + $attempts;
            
            // Controlla se questo seed esiste già
            if (Puzzle::bySeed($seed)->exists()) {
                continue;
            }

            try {
                // Genera puzzle con difficoltà target (include generazione griglia completa)
                $puzzleGrid = $generator->generatePuzzleWithDifficulty($seed, $targetDifficulty);
                
                // Genera griglia completa separata per la soluzione
                $completeGrid = $generator->generateCompleteGrid($seed);
                
                // Verifica la difficoltà effettiva
                $actualDifficulty = $difficultyRater->rateDifficulty($puzzleGrid);
                
                // Se la difficoltà non corrisponde, riprova (con una certa tolleranza)
                $difficultyMap = ['easy' => 1, 'medium' => 2, 'hard' => 3, 'expert' => 4];
                $targetLevel = $difficultyMap[$targetDifficulty];
                $actualLevel = $difficultyMap[$actualDifficulty] ?? 0;
                
                // Tollera una differenza di 1 livello
                if (abs($targetLevel - $actualLevel) > 1) {
                    continue;
                }

                // Salva il puzzle
                Puzzle::create([
                    'seed' => $seed,
                    'givens' => $puzzleGrid->toArray(),
                    'solution' => $completeGrid->toArray(),
                    'difficulty' => $actualDifficulty,
                ]);

                $generated++;
            } catch (\Exception $e) {
                // Se c'è un errore, continua con il prossimo seed
                continue;
            }
        }

        if ($generated === 0) {
            return redirect()->route('admin.puzzles.generate')
                ->with('error', 'Impossibile generare puzzle con i parametri specificati. Riprova con seed o difficoltà diversa.');
        }

        return redirect()->route('admin.puzzles')
            ->with('success', "Generati con successo {$generated} puzzle di difficoltà {$targetDifficulty}!");
    }

    /**
     * Elimina un puzzle
     */
    public function destroyPuzzle(Puzzle $puzzle)
    {
        // Controlla se il puzzle è assegnato a sfide
        if ($puzzle->challenges()->exists()) {
            return redirect()->route('admin.puzzles')
                ->with('error', 'Impossibile eliminare il puzzle: è assegnato a una o più sfide!');
        }

        $puzzle->delete();

        return redirect()->route('admin.puzzles')
            ->with('success', 'Puzzle eliminato con successo!');
    }
}
