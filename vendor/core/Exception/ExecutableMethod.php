<?php declare(strict_types=1);

namespace Core\Exception;

use Throwable;
use Exception;

/**
 * Class ExecutableMethod
 *
 * @package Core\exception
 */
class ExecutableMethod extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $code = 500;
        $nameException = explode('\\', __CLASS__)[2];
        $message = "Ошибка [{$nameException}]: " . $message;
        parent::__construct($message, $code, $previous);
    }

}