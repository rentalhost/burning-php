<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Session\Types\Abstracts;

use Rentalhost\BurningPHP\Session\SessionManager;
use Rentalhost\BurningPHP\Support\Traits\HasAttributesTrait;
use Rentalhost\BurningPHP\Support\Traits\SingletonPatternTrait;

abstract class AbstractType
    implements \JsonSerializable
{
    use HasAttributesTrait,
        SingletonPatternTrait {
        SingletonPatternTrait::getInstance as getSingletonInstance;
    }

    /**
     * @return static
     */
    public static function getInstance(): self
    {
        $instance = self::getInstanceNullable();

        if ($instance) {
            return $instance;
        }

        $instance = static::getSingletonInstance();

        SessionManager::getInstance()->register($instance);

        return $instance;
    }

    public static function execute(): void
    {
        static::getInstance();
    }

    /**
     * @return mixed|void
     */
    public function call(?array $args = null)
    {
    }
}
