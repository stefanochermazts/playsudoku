<?php
declare(strict_types=1);

namespace App\Models;

use App\Domain\Sudoku\Grid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modello per i puzzle Sudoku
 * 
 * @property int $id
 * @property int $seed
 * @property string $givens
 * @property string $solution
 * @property string $difficulty
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Puzzle extends Model
{
    use HasFactory;

    protected $fillable = [
        'seed',
        'givens',
        'solution',
        'difficulty',
    ];

    protected $casts = [
        'givens' => 'array',
        'solution' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relazione: un puzzle può avere molte sfide
     */
    public function challenges(): HasMany
    {
        return $this->hasMany(Challenge::class);
    }

    /**
     * Converte i givens in un oggetto Grid del dominio
     */
    public function toGrid(): Grid
    {
        return Grid::fromArray($this->givens);
    }

    /**
     * Converte la soluzione in un oggetto Grid del dominio
     */
    public function getSolutionGrid(): Grid
    {
        return Grid::fromArray($this->solution);
    }

    /**
     * Scope: filtra per difficoltà
     */
    public function scopeByDifficulty($query, string $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }

    /**
     * Scope: filtra per seed
     */
    public function scopeBySeed($query, int $seed)
    {
        return $query->where('seed', $seed);
    }
}
