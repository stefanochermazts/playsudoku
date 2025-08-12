<?php
declare(strict_types=1);

namespace App\Domain\Sudoku\Exceptions;

use InvalidArgumentException;

/**
 * Eccezione lanciata quando una mossa non è valida
 */
class InvalidMoveException extends InvalidArgumentException
{
}
