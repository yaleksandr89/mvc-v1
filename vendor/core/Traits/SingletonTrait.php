<?php declare(strict_types=1);

namespace Core\Traits;

/**
 * Trait SingletonTrait
 *
 * @package Core\traits
 */
trait SingletonTrait
{
    private static ?self $instance = null;

    /**
     * @return self
     */
    public static function getInstance(): self
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }
}