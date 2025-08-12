<?php
declare(strict_types=1);

namespace App\Domain\Sudoku\Exceptions;

use InvalidArgumentException;

/**
 * Eccezione lanciata quando una griglia Sudoku non è valida
 */
class InvalidGridException extends InvalidArgumentException
{
}
