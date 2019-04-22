<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP;

use Composer\Autoload\ClassLoader;
use Rentalhost\BurningPHP\Session\Types\AutoloadType;

class BurningAutoloader
{
    /** @var self */
    private static $instance;

    /** @var ClassLoader */
    public $composerClassLoader;

    /** @var string */
    private $vendorDirectory;

    public function __construct()
    {
        $this->vendorDirectory = realpath(getcwd() . '/vendor') . DIRECTORY_SEPARATOR;
    }

    public static function getInstance(): self
    {
        if (self::$instance) {
            return self::$instance;
        }

        return self::$instance = new self;
    }

    public function register(): void
    {
        spl_autoload_register([ $this, 'autoload' ], true, true);
    }

    private function autoload(string $classname): bool
    {
        if (strpos($classname, 'Rentalhost\\BurningPHP\\') === 0) {
            return false;
        }

        $file = realpath($this->composerClassLoader->findFile($classname));

        if (strpos($file, $this->vendorDirectory) === 0) {
            return false;
        }

        if (is_readable($file)) {
            $autoloadObjectInstance            = new AutoloadType;
            $autoloadObjectInstance->classname = $classname;
            $autoloadObjectInstance->file      = $file;
            $autoloadObjectInstance->write();
        }

        return false;
    }
}
