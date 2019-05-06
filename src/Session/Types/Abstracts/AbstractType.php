<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Session\Types\Abstracts;

use Rentalhost\BurningPHP\Session\SessionProxyFactory;
use Rentalhost\BurningPHP\Support\Traits\HasAttributesTrait;

/**
 * @property string   $type
 * @property float    $timestamp
 * @property int|null $offset
 * @property int|null $length
 */
abstract class AbstractType
    implements \JsonSerializable
{
    use HasAttributesTrait;

    private const
        REGEXP_BASE_IDENTIFIER = '<\\\\(?<base>[^\\\\]+)Type$>';

    public static function generate(?array $args = null): array
    {
        if ($args === null) {
            return [
                'type'      => static::class,
                'timestamp' => microtime(true)
            ];
        }

        return array_replace($args, [
            'type'      => static::class,
            'timestamp' => microtime(true)
        ]);
    }

    public static function getType(): String
    {
        if (!preg_match(self::REGEXP_BASE_IDENTIFIER, static::class, $staticMatch)) {
            throw new \RuntimeException('a type class "' . static::class . '" should be suffixed with Type');
        }

        $typeBasename = $staticMatch['base'];

        return strtolower(substr($typeBasename, 0, 1)) . substr($typeBasename, 1);
    }

    public static function postProcessing(array $args): array
    {
        return $args;
    }

    public static function write(?array $args = null): void
    {
        SessionProxyFactory::write(static::generate($args));
    }

    public function jsonSerialize(): array
    {
        $this->timestamp = microtime(true);

        return $this->toArray();
    }
}
