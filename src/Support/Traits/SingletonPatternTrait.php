<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Support\Traits;

trait SingletonPatternTrait
{
    /** @var static */
    private static $instance;

    /**
     * @return static
     */
    public static function getInstance(): self
    {
        if (static::$instance) {
            return static::$instance;
        }

        static::initialize();

        return static::$instance = new static;
    }

    protected static function initialize(): void
    {
    }
}
