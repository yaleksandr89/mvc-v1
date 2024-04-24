<?php

namespace App\Helper;

class StrHelper
{
    public static function prepareNameMethod(string $nameMethods): string
    {
        preg_match('/::(.*)/', $nameMethods, $matches);

        return str_replace(['::', $matches[1]], ['@', ucfirst($matches[1])], $nameMethods);
    }
}
