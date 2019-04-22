<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP;

use ColinODell\Json5\Json5Decoder;
use Rentalhost\BurningPHP\Support\HasAttributes;
use Rentalhost\BurningPHP\Support\SingletonPattern;

/**
 * @property string      $burningDirectory
 * @property string      $burningSessionFormat
 * @property string|null $burningVersion
 * @property bool        $devOnly
 * @property bool        $disableXdebug
 * @property bool        $forceWriteShutdownObject
 */
class BurningConfiguration
{
    use HasAttributes,
        SingletonPattern;

    private const
        DEFAULT_CONFIGURATION_FILE = __DIR__ . '/../.burning.json';

    /** @var string */
    public $currentWorkingDir;

    public function __construct()
    {
        $defaultConfigurationFile = realpath(self::DEFAULT_CONFIGURATION_FILE);
        $userConfigurationFile    = realpath(getcwd() . '/.burning.json') ?: null;

        $this->mergeWith($defaultConfigurationFile);

        $this->currentWorkingDir = getcwd();

        if ($userConfigurationFile !== null && $defaultConfigurationFile !== $userConfigurationFile) {
            $this->mergeWith($userConfigurationFile);
        }

        if ($this->burningVersion === null) {
            $selfComposer         = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true);
            $this->burningVersion = $selfComposer['version'];
        }
    }

    public function getBurningDirectory(): string
    {
        return $this->currentWorkingDir . DIRECTORY_SEPARATOR . $this->burningDirectory;
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
