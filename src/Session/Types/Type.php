<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Session\Types;

use Rentalhost\BurningPHP\Support\HasAttributes;

/**
 * @property string $type
 */
abstract class Type
    implements \JsonSerializable
{
    use HasAttributes;

    private const
        REGEXP_BASE_IDENTIFIER = '<\\\\(?<base>[^\\\\]+)Type$>';

    public function __construct()
    {
        if (!preg_match(self::REGEXP_BASE_IDENTIFIER, static::class, $staticMatch)) {
            throw new \RuntimeException('the type class "' . static::class . '" should be suffixed with Type');
        }

        $typeBasename = $staticMatch['base'];

        $this->type = strtolower(substr($typeBasename, 0, 1)) . substr($typeBasename, 1);
    }
}
