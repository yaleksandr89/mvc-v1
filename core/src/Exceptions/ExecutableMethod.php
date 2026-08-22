<?php
declare(strict_types=1);

namespace Yaa\Framework\Exceptions;

use Throwable;
use Exception;

class ExecutableMethod extends Exception
{
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        if (0 === $code) {
            $code = 500;
        }

        $nameException = basename(str_replace('\\', '/', __CLASS__));
        $message = "Ошибка [$nameException]: " . $message;

        parent::__construct($message, $code, $previous);
    }
}
