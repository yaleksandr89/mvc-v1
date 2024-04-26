<?php

namespace Yaa\Framework\Exceptions;

use Throwable;
use Exception;

class ConnectClass extends Exception
{
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        if (0 === $code) {
            $code = 500;
        }

        $nameException = explode('\\', __CLASS__)[2];
        $message = "Ошибка [$nameException]: " . $message;

        parent::__construct($message, $code, $previous);
    }
}
