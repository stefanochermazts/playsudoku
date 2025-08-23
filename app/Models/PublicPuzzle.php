<?php
declare(strict_types=1);

namespace App\Models;

use App\Domain\Sudoku\Grid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

/**
 * Modello per i puzzle pubblici del Sudoku Solver AI
 * 
 * @property int $id
 * @property string $hash
 * @property array $grid_data
 * @property array|null $solution_data
 * @property string|null $difficulty
 * @property string $status
 * @property string|null $seo_title
 * @property string|null $seo_description
 * @property string|null $seo_keywords
 * @property string|null $canonical_url
 * @property array|null $solver_steps
 * @property array|null $techniques_used
 * @property int|null $solving_time_ms
 * @property bool $is_solvable
 * @property int $view_count
 * @property int $share_count
 * @property \Illuminate\Support\Carbon|null $last_viewed_at
 * @property \Illuminate\Support\Carbon|null $processed_at
 * @property int|null $submitted_by_user_id
 * @property string|null $submitted_from_ip
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class PublicPuzzle extends Model
{
    use HasFactory;

    protected $fillable = [
        'hash',
        'grid_data',
        'solution_data',
        'difficulty',
        'status',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'canonical_url',
        'solver_steps',
        'techniques_used',
        'solving_time_ms',
        'is_solvable',
        'submitted_by_user_id',
        'submitted_from_ip',
        'user_agent',
    ];

    protected $casts = [
        'grid_data' => 'array',
        'solution_data' => 'array',
        'solver_steps' => 'array',
        'techniques_used' => 'array',
        'is_solvable' => 'boolean',
        'view_count' => 'integer',
        'share_count' => 'integer',
        'solving_time_ms' => 'integer',
        'last_viewed_at' => 'datetime',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relazione: puzzle appartiene ad un utente che l'ha sottomesso
     */
    public function submittedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    /**
     * Converte grid_data in un oggetto Grid del dominio
     */
    public function toGrid(): Grid
    {
        return Grid::fromArray($this->grid_data);
    }

    /**
     * Converte solution_data in un oggetto Grid del dominio
     */
    public function getSolutionGrid(): ?Grid
    {
        if (!$this->solution_data) {
            return null;
        }
        return Grid::fromArray($this->solution_data);
    }

    /**
     * Genera hash SHA-256 per una griglia
     */
    public static function generateHash(array $gridData): string
    {
        return hash('sha256', json_encode($gridData));
    }

    /**
     * Crea un nuovo public puzzle da una griglia
     */
    public static function createFromGrid(array $gridData, array $metadata = []): self
    {
        $hash = self::generateHash($gridData);
        
        return self::create([
            'hash' => $hash,
            'grid_data' => $gridData,
            'status' => 'pending',
            'submitted_by_user_id' => $metadata['user_id'] ?? null,
            'submitted_from_ip' => $metadata['ip'] ?? null,
            'user_agent' => $metadata['user_agent'] ?? null,
        ]);
    }

    /**
     * Incrementa contatore delle visualizzazioni
     */
    public function incrementViewCount(): void
    {
        $this->increment('view_count');
        $this->touch('last_viewed_at');
    }

    /**
     * Incrementa contatore delle condivisioni
     */
    public function incrementShareCount(): void
    {
        $this->increment('share_count');
    }

    /**
     * Marca il puzzle come processato con i risultati del solver
     */
    public function markAsProcessed(array $solverResults): void
    {
        $this->update([
            'status' => 'processed',
            'solution_data' => $solverResults['grid']?->toArray(),
            'solver_steps' => $solverResults['steps'] ?? [],
            'techniques_used' => $solverResults['techniques'] ?? [],
            'solving_time_ms' => $solverResults['solving_time_ms'] ?? null,
            'is_solvable' => $solverResults['grid'] !== null,
            'processed_at' => now(),
        ]);
    }

    /**
     * Genera metadati SEO automaticamente
     */
    public function generateSeoMetadata(): void
    {
        $difficulty = $this->difficulty ? ucfirst($this->difficulty) : 'Unknown';
        $techniquesCount = is_array($this->techniques_used) ? count($this->techniques_used) : 0;
        
        $this->update([
            'seo_title' => "Solve this {$difficulty} Sudoku Puzzle Online - Step by Step Solution",
            'seo_description' => "Free Sudoku solver with step-by-step explanation. This {$difficulty} puzzle uses {$techniquesCount} solving techniques. Learn how to solve Sudoku puzzles with our AI solver.",
            'seo_keywords' => "sudoku solver,{$difficulty} sudoku,puzzle solution,step by step,sudoku techniques,online sudoku",
            'canonical_url' => $this->getPublicUrl(),
        ]);
    }

    /**
     * Ottiene URL pubblico per il puzzle
     */
    public function getPublicUrl(): string
    {
        $locale = app()->getLocale();
        return URL::to("/{$locale}/solve/this-sudoku-puzzle/{$this->hash}");
    }

    /**
     * Ottiene URL SEO-friendly per il puzzle
     */
    public function getSeoUrl(): string
    {
        $locale = app()->getLocale();
        $difficulty = $this->difficulty ? Str::slug($this->difficulty) : 'medium';
        return URL::to("/{$locale}/solve/{$difficulty}-sudoku-puzzle/{$this->hash}");
    }

    /**
     * Scope: puzzle processati con successo
     */
    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }

    /**
     * Scope: puzzle risolvibili logicamente
     */
    public function scopeSolvable($query)
    {
        return $query->where('is_solvable', true);
    }

    /**
     * Scope: puzzle per difficoltà
     */
    public function scopeByDifficulty($query, string $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }

    /**
     * Scope: puzzle più visualizzati
     */
    public function scopeMostViewed($query, int $limit = 10)
    {
        return $query->orderByDesc('view_count')->limit($limit);
    }

    /**
     * Scope: puzzle più condivisi
     */
    public function scopeMostShared($query, int $limit = 10)
    {
        return $query->orderByDesc('share_count')->limit($limit);
    }

    /**
     * Scope: puzzle recenti
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
