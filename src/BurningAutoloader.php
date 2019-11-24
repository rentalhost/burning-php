<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP;

use Composer\Autoload\ClassLoader;
use Rentalhost\BurningPHP\Processor\Processor;
use Rentalhost\BurningPHP\Session\SessionProxyFactory;
use Rentalhost\BurningPHP\Session\Types\Initialize\InitializeType;
use Rentalhost\BurningPHP\Session\Types\Path\PathType;
use Rentalhost\BurningPHP\Support\Deterministic;
use Rentalhost\BurningPHP\Support\Traits\SingletonPatternTrait;
use Symfony\Component\Filesystem\Filesystem;
use function Composer\Autoload\includeFile;

class BurningAutoloader
{
    use SingletonPatternTrait;

    /** @var ClassLoader */
    public $composerClassLoader;

    /** @var string[] */
    private $ignorablePrefixes;

    public function __construct()
    {
        $this->ignorablePrefixes = array_merge(
            [ realpath(getcwd() . '/vendor') . DIRECTORY_SEPARATOR ],
            Deterministic::withClosure(\Closure::fromCallable([ BurningConfiguration::getInstance(), 'getTargetDevelopmentPaths' ]))
        );
    }

    private static function clearCache(string $burningCacheDirectory, int $workingDirPerms): void
    {
        $filesystem = new Filesystem;
        $filesystem->remove($burningCacheDirectory);

        mkdir($burningCacheDirectory, $workingDirPerms);
    }

    private static function generateControlDirectory(): void
    {
        $burningConfiguration    = BurningConfiguration::getInstance();
        $burningControlDirectory = $burningConfiguration->getBurningDirectory();
        $burningCacheDirectory   = $burningControlDirectory . '/caches';

        $burningDirectories = [
            $burningControlDirectory,
            $burningControlDirectory . '/sessions',
            $burningCacheDirectory
        ];

        $workingDirPerms = fileperms($burningConfiguration->currentWorkingDir);

        foreach ($burningDirectories as $burningDirectory) {
            if (!is_dir($burningDirectory)) {
                mkdir($burningDirectory, $workingDirPerms);
            }
        }

        $burningHeaderFile     = $burningControlDirectory . '/HEADER';
        $burningHeaderContents = sprintf('BURNING v%u c<%s>',
            $burningConfiguration->getBurningVersionInt(),
            $burningConfiguration->getHash());

        if ($burningConfiguration->disableCache) {
            self::clearCache($burningCacheDirectory, $workingDirPerms);
        }
        else if (is_file($burningHeaderFile)) {
            $burningHeaderContentsPrevious = file_get_contents($burningHeaderFile);

            if ($burningHeaderContentsPrevious !== $burningHeaderContents) {
                self::clearCache($burningCacheDirectory, $workingDirPerms);
            }
        }

        file_put_contents($burningHeaderFile, $burningHeaderContents);
    }

    public function register(): void
    {
        self::generateControlDirectory();

        SessionProxyFactory::register();
        InitializeType::execute();

        spl_autoload_register([ $this, 'autoload' ], true, true);
    }

    private function autoload(string $classname): bool
    {
        if (strpos($classname, 'Rentalhost\\BurningPHP\\') === 0) {
            return false;
        }

        $file = $this->composerClassLoader->findFile($classname);

        if (!$file || !is_readable($file)) {
            return false;
        }

        $file = realpath($file);

        foreach ($this->ignorablePrefixes as $ignorablePrefix) {
            if (strpos($file, $ignorablePrefix) === 0) {
                return false;
            }
        }

        includeFile(Processor::getInstance()->process($file));

        $burningWorkingLength = strlen(BurningConfiguration::getInstance()->currentWorkingDir);

        PathType::getInstance()->registerClass(substr($file, $burningWorkingLength + 1), $classname);

        return true;
    }
}
