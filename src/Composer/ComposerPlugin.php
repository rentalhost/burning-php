<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Composer;

use Composer\Autoload\AutoloadGenerator;
use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Rentalhost\BurningPHP\BurningConfiguration;

class ComposerPlugin
    implements PluginInterface,
               EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'post-autoload-dump' => 'postAutoloadDump'
        ];
    }

    public static function postAutoloadDump(Event $event): void
    {
        $burningConfiguration = BurningConfiguration::getInstance();

        if (!$burningConfiguration->devOnly || $event->isDevMode()) {
            $composer             = $event->getComposer();
            $composerAutoload     = realpath($composer->getConfig()->get('vendor-dir') . '/autoload.php');
            $composerAutoloadReal = dirname($composerAutoload) . '/autoload_composer.php';

            if (is_file($composerAutoloadReal)) {
                unlink($composerAutoloadReal);
            }

            /** @see AutoloadGenerator::dump() */
            if (!preg_match('{(ComposerAutoloaderInit[^:\s]+)::}', file_get_contents($composerAutoload), $composerAutoloadClassnameMatch)) {
                throw new \RuntimeException('could not identify ComposerAutoloaderInit classname');
            }

            rename($composerAutoload, $composerAutoloadReal);

            $composerAutoloadStub = strtr(
                file_get_contents(__DIR__ . '/../../stub/autoload.php.stub'),
                [
                    '$composerAutoloadReal'      => realpath($composerAutoloadReal),
                    '$composerAutoloadClassname' => $composerAutoloadClassnameMatch[1]
                ]
            );

            file_put_contents($composerAutoload, $composerAutoloadStub, LOCK_EX);
        }
    }

    public function activate(Composer $composer, IOInterface $io): void
    {
    }
}
