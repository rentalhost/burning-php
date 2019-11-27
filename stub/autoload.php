<?php

declare(strict_types = 1);

use Composer\XdebugHandler\XdebugHandler;
use Rentalhost\BurningPHP\BurningAutoloader;
use Rentalhost\BurningPHP\BurningConfiguration;

if (!class_exists(BurningAutoloader::class, false)) {
    require '$composerAutoloadReal';

    if (!BurningConfiguration::getInstance()->allowXdebug) {
        $xdebugHandler = new XdebugHandler('BURNING');
        $xdebugHandler->check();
    }

    $instanceBurningAutoloader                      = BurningAutoloader::getInstance();
    $instanceBurningAutoloader->composerClassLoader = $composerAutoloadClassname::getLoader();
    $instanceBurningAutoloader->register();
}
