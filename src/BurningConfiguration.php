<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP;

use ColinODell\Json5\Json5Decoder;
use Rentalhost\BurningPHP\Support\Traits\HasAttributesTrait;
use Rentalhost\BurningPHP\Support\Traits\SingletonPatternTrait;
use Symfony\Component\Finder\Finder;

/**
 * @property string|null $burningVersion
 * @property string|null $burningSourceHash
 * @property bool        $allowXdebug
 * @property bool        $includeDevelopmentPaths
 */
class BurningConfiguration
{
    use HasAttributesTrait,
        SingletonPatternTrait;

    private const
        DEFAULT_CONFIGURATION_FILE = __DIR__ . '/../burning.json';

    /** @var string */
    public $currentWorkingDir;

    /** @var array */
    private $targetComposer = [];

    public function __construct()
    {
        $defaultConfigurationFile = realpath(self::DEFAULT_CONFIGURATION_FILE);
        $userConfigurationFile    = realpath(getcwd() . '/burning.json') ?: null;

        $this->mergeWith($defaultConfigurationFile);

        $this->currentWorkingDir = getcwd();

        if ($userConfigurationFile !== null && $defaultConfigurationFile !== $userConfigurationFile) {
            $this->mergeWith($userConfigurationFile);
        }

        $targetComposerFile = $this->currentWorkingDir . '/composer.json';

        if (is_readable($targetComposerFile)) {
            $this->targetComposer = json_decode(file_get_contents($targetComposerFile), true, 512, JSON_THROW_ON_ERROR) ?: [];
        }

        $selfComposer = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true, 512, JSON_THROW_ON_ERROR);

        $this->burningVersion    = $selfComposer['version'];
        $this->burningSourceHash = $this->getBurningSourceHash();
    }

    public function getBurningDirectory(): string
    {
        return $this->currentWorkingDir . DIRECTORY_SEPARATOR . '.burning';
    }

    public function getBurningSessionFolder(): string
    {
        return 'session-' . str_pad(var_export($_SERVER['REQUEST_TIME_FLOAT'], true), 17, '0');
    }

    public function getBurningSourceHash(): string
    {
        $finder = new Finder;
        $finder->files()->in(__DIR__);

        $filesModificationTime = [];

        foreach ($finder as $file) {
            $filesModificationTime[] = $file->getMTime();
        }

        return strtoupper(substr(hash('sha256', implode(',', $filesModificationTime)), 0, 8));
    }

    public function getBurningVersionInt(): int
    {
        [ $majorVersion, $minorVersion, $patchVersion ] = explode('.', $this->burningVersion);

        return $majorVersion * 10000 + $minorVersion * 100 + $patchVersion;
    }

    public function getHash(): string
    {
        return strtoupper(substr(hash('sha256', serialize($this->attributes)), 0, 8));
    }

    public function getPathWithSessionMask(string $headerType): string
    {
        return $this->getBurningSessionFolder() . '/' . $headerType;
    }

    /**
     * @return string[]
     */
    public function getTargetDevelopmentPaths(): array
    {
        if ($this->includeDevelopmentPaths) {
            return [];
        }

        $files       = $this->targetComposer['autoload-dev']['files'] ?? [];
        $directories = $this->targetComposer['autoload-dev']['psr-4'] ?? [];

        $files = array_map(function (string $path) {
            return realpath($this->currentWorkingDir . DIRECTORY_SEPARATOR . $path);
        }, array_filter($files, static function (string $path) {
            return is_file($path);
        }));

        $directories = array_map(function (string $path) {
            return realpath($this->currentWorkingDir . DIRECTORY_SEPARATOR . $path) . DIRECTORY_SEPARATOR;
        }, array_filter($directories, static function (string $path) {
            return is_dir($path);
        }));

        return array_merge($files, $directories);
    }

    private function mergeWith(?string $configurationFile): void
    {
        if ($configurationFile !== null && is_file($configurationFile) && is_readable($configurationFile)) {
            $this->attributes = array_replace_recursive($this->attributes, Json5Decoder::decode(file_get_contents($configurationFile), true));
        }
    }
}
