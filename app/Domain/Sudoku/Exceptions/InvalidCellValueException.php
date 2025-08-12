<?php
declare(strict_types=1);

namespace App\Domain\Sudoku\Exceptions;

use InvalidArgumentException;

/**
 * Eccezione lanciata quando un valore di cella non è valido
 */
class InvalidCellValueException extends InvalidArgumentException
{
}
