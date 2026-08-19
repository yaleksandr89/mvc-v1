<?php
declare(strict_types=1);

namespace App\Helper;

class StrHelper
{
    public static function prepareNameMethod(string $nameMethods): string
    {
        $parts = explode('::', $nameMethods, 2);

        if (count($parts) !== 2) {
            return $nameMethods;
        }

        return $parts[0] . '@' . ucfirst($parts[1]);
    }
}
