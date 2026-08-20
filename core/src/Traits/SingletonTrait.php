<?php
declare(strict_types=1);

namespace Yaa\Framework\Traits;

trait SingletonTrait
{
    /** @var array<class-string, object> */
    private static array $instances = [];

    public static function getInstance(): static
    {
        $class = static::class;

        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new static();
        }

        $instance = self::$instances[$class];
        if (!$instance instanceof static) {
            throw new \LogicException("Singleton instance for $class has an invalid type.");
        }

        return $instance;
    }
}
