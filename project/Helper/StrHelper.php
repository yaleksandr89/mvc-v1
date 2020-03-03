<?php declare(strict_types=1);

namespace Project\Helper;

/**
 * Class StrHelper
 *
 * @package Project\Helper
 */
class StrHelper
{
    /**
     * @param string $nameMethos
     * @return string
     */
    public static function prepareNameMethod(string $nameMethos): string
    {
        preg_match('/::(.*)/', $nameMethos, $matches);
        return str_replace(['::', $matches[1]], ['@', ucfirst($matches[1])], $nameMethos);
    }
}