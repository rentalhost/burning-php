<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP;

use ColinODell\Json5\Json5Decoder;
use Rentalhost\BurningPHP\Support\HasAttributes;

/**
 * @property string|null $burningVersion
 * @property bool        $devOnly
 * @property bool        $disableXdebug
 */
class BurningConfiguration
{
    use HasAttributes;

    private const
        DEFAULT_CONFIGURATION_FILE = __DIR__ . '/../burning.json5';

    /** @var self */
    private static $instance;

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

        if ($self->burningVersion === null) {
            $composerJson         = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true);
            $self->burningVersion = $composerJson['version'];
        }

        return self::$instance = $self;
    }

    public function getBurningVersionInt(): int
    {
        [ $majorVersion, $minorVersion, $patchVersion ] = explode('.', $this->burningVersion);

        return $majorVersion * 10000 + $minorVersion * 100 + $patchVersion;
    }

    private function mergeWith(?string $configurationFile): void
    {
        if ($configurationFile !== null && is_file($configurationFile) && is_readable($configurationFile)) {
            $this->attributes = array_replace_recursive($this->attributes, Json5Decoder::decode(file_get_contents($configurationFile), true));
        }
    }
}
