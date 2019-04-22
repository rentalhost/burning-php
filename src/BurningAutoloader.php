<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP;

use Composer\Autoload\ClassLoader;
use Rentalhost\BurningPHP\Session\Types\AutoloadType;
use Rentalhost\BurningPHP\Support\Deterministic;
use Rentalhost\BurningPHP\Support\SingletonPattern;

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

        $burningDirectories = [
            $burningControlDirectory,
            $burningControlDirectory . '/sessions',
            $burningControlDirectory . '/caches'
        ];

        $workingDirPerms = fileperms($burningConfiguration->currentWorkingDir);

        foreach ($burningDirectories as $burningDirectory) {
            if (!is_dir($burningDirectory)) {
                mkdir($burningDirectory, $workingDirPerms);
            }
        }
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

        $autoloadObjectInstance            = new AutoloadType;
        $autoloadObjectInstance->classname = $classname;
        $autoloadObjectInstance->file      = $file;
        $autoloadObjectInstance->write();

        return false;
    }
}
