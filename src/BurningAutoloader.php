<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP;

use Composer\Autoload\ClassLoader;
use Rentalhost\BurningPHP\Session\SessionManager;

class BurningAutoloader
{
    /** @var self */
    private static $instance;

    /** @var ClassLoader */
    public $composerClassLoader;

    public static function getInstance(): self
    {
        if (self::$instance) {
            return self::$instance;
        }

        SessionManager::getInstance();

        return self::$instance = new self;
    }

    public function register(): void
    {
        spl_autoload_register([ $this, 'autoload' ], true, true);
    }

    private function autoload(string $classname): void
    {
    }
}
