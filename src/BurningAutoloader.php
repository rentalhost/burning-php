<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP;

use Composer\Autoload\ClassLoader;
use Rentalhost\BurningPHP\Processor\Processor;
use Rentalhost\BurningPHP\Session\Types\AutoloadType;
use Rentalhost\BurningPHP\Support\Deterministic;
use Rentalhost\BurningPHP\Support\SingletonPattern;
use Symfony\Component\Filesystem\Filesystem;
use function Composer\Autoload\includeFile;

class BurningAutoloader
{
    use SingletonPattern;

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

        $versionFile = $burningControlDirectory . '/version';

        if (is_file($versionFile)) {
            $versionValue = file_get_contents($versionFile);

            if ($versionValue !== (string) $burningConfiguration->getBurningVersionInt()) {
                $filesystem = new Filesystem;
                $filesystem->remove($burningCacheDirectory);

                mkdir($burningCacheDirectory, $workingDirPerms);
            }
        }

        file_put_contents($versionFile, $burningConfiguration->getBurningVersionInt());
    }

    public function register(): void
    {
        self::generateControlDirectory();

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

        $autoloadObjectInstance            = new AutoloadType;
        $autoloadObjectInstance->classname = $classname;
        $autoloadObjectInstance->file      = $file;
        $autoloadObjectInstance->write();

        return true;
    }
}
