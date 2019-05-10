<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Support\Traits;

trait SingletonPatternTrait
{
    /** @var static[] */
    protected static $instances = [];

    /**
     * @return static
     */
    public static function getInstance(): self
    {
        $instance = self::getInstanceNullable();

        if ($instance) {
            return $instance;
        }

        $instance = new static;
        $instance->initialize();

        return self::$instances[static::class] = $instance;
    }

    /**
     * @return static|null
     */
    private static function getInstanceNullable(): ?self
    {
        return self::$instances[static::class] ?? null;
    }

    public function initialize(): void
    {
    }
}
