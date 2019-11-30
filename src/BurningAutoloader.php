<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP;

use Composer\Autoload\ClassLoader;
use Rentalhost\BurningPHP\Processor\Processor;
use Rentalhost\BurningPHP\Processor\ProcessorCall;
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

    private static function createDirectoryLink(string $directoryReal, string $directoryLink, ?bool $overwrite = null): bool
    {
        if ($overwrite === true && is_dir($directoryLink)) {
            rmdir($directoryLink);
        }

        if (PHP_OS_FAMILY !== 'Windows') {
            return symlink($directoryReal, $directoryLink);
        }

        exec('mklink /J ' . escapeshellarg($directoryLink) . ' ' . escapeshellarg($directoryReal));

        return is_dir($directoryLink);
    }

    private static function generateControlDirectory(): void
    {
        $burningConfiguration        = BurningConfiguration::getInstance();
        $burningControlDirectory     = $burningConfiguration->getBurningDirectory();
        $burningCacheDirectory       = $burningControlDirectory . '/cache';
        $burningSessionDirectory     = $burningControlDirectory . '/' . $burningConfiguration->getBurningSessionFolder();
        $burningSessionLastDirectory = $burningControlDirectory . '/session-last';

        $burningDirectories = [
            $burningControlDirectory,
            $burningCacheDirectory
        ];

        $workingDirPerms = fileperms($burningConfiguration->currentWorkingDir);

        self::rebuildDirectory($burningSessionDirectory, $workingDirPerms);
        self::createDirectoryLink($burningSessionDirectory, $burningSessionLastDirectory, true);

        foreach ($burningDirectories as $burningDirectory) {
            if (!is_dir($burningDirectory)) {
                mkdir($burningDirectory, $workingDirPerms);
            }
        }

        $burningHeaderFile     = $burningControlDirectory . '/HEADER';
        $burningHeaderContents = sprintf("BURNING v%u s%s c%s\n",
            $burningConfiguration->getBurningVersionInt(),
            $burningConfiguration->burningSourceHash,
            $burningConfiguration->getHash());

        file_put_contents($burningHeaderFile, $burningHeaderContents);

        copy($burningHeaderFile, $burningControlDirectory . '/' .
                                 $burningConfiguration->getPathWithSessionMask('HEADER'));
    }

    private static function rebuildDirectory(string $directory, int $workingDirPerms): void
    {
        $filesystem = new Filesystem;
        $filesystem->remove($directory);

        clearstatcache(true, $directory);

        mkdir($directory, $workingDirPerms, true);
    }

    public function getProcessedSourcePath(string $file): string
    {
        if (!$file || !is_readable($file)) {
            return $file;
        }

        $file = realpath($file);

        foreach ($this->ignorablePrefixes as $ignorablePrefix) {
            if (strpos($file, $ignorablePrefix) === 0) {
                return $file;
            }
        }

        return Processor::getInstance()->process($file)->sourceProcessedResourcePath;
    }

    public function register(): void
    {
        self::generateControlDirectory();

        ProcessorCall::register();

        spl_autoload_register([ $this, 'autoload' ], true, true);
    }

    private function autoload(string $classname): bool
    {
        if (strpos($classname, 'Rentalhost\\BurningPHP\\') === 0) {
            return false;
        }

        $file = $this->composerClassLoader->findFile($classname);

        if (!$file) {
            return false;
        }

        includeFile($this->getProcessedSourcePath($file));

        return true;
    }
}
