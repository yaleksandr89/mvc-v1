<?php declare(strict_types=1);

namespace Core\Exception;

use Throwable;
use Exception;

/**
 * Class ConnectFile
 *
 * @package Core\exception
 */
class ConnectFile extends Exception
{
    /**
     * ConnectFile constructor.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $code = 404;
        $nameException = explode('\\', __CLASS__)[2];
        $message = "Ошибка [{$nameException}]: " . $message;
        parent::__construct($message, $code, $previous);
    }

}