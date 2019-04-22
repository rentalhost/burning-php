<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP;

use ColinODell\Json5\Json5Decoder;

/**
 * @property-read bool $devOnly
 * @property-read bool $disableXdebug
 */
class BurningConfiguration
{
    private const
        DEFAULT_CONFIGURATION_FILE = __DIR__ . '/../burning.json5';

    /** @var self */
    private static $instance;

    /** @var mixed[] */
    private $attributes = [];

    public static function getInstance(): self
    {
        if (self::$instance) {
            return self::$instance;
        }

        $defaultConfigurationFile = realpath(self::DEFAULT_CONFIGURATION_FILE);
        $userConfigurationFile    = realpath(getcwd() . '/burning.json5') ?: null;

        $self = new static;
        $self->mergeWith($defaultConfigurationFile);

        if ($userConfigurationFile !== null && $defaultConfigurationFile !== $userConfigurationFile) {
            $self->mergeWith($userConfigurationFile);
        }

        return self::$instance = $self;
    }

    public function __get(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    public function __set(string $name, $value)
    {
        $this->attributes[$name] = $value;
    }

    public function __isset(string $name)
    {
        return array_key_exists($name, $this->attributes);
    }

    private function mergeWith(?string $configurationFile): void
    {
        if ($configurationFile !== null && is_file($configurationFile) && is_readable($configurationFile)) {
            $this->attributes = array_replace_recursive($this->attributes, Json5Decoder::decode(file_get_contents($configurationFile), true));
        }
    }
}
