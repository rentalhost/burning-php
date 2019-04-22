<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Support;

class Deterministic
{
    /** @var mixed[] */
    private static $cached = [];

    public static function withClosure(\Closure $closure)
    {
        $closureHash = hash('sha256', (new \ReflectionFunction($closure))->__toString());

        if (array_key_exists($closureHash, self::$cached)) {
            return self::$cached[$closureHash];
        }

        return self::$cached[$closureHash] = $closure();
    }
}
