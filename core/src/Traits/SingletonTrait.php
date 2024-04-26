<?php

namespace Yaa\Framework\Traits;

trait SingletonTrait
{
    private static ?self $instance = null;

    public static function getInstance(): static
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }
}
