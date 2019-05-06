<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Session\Types;

use Rentalhost\BurningPHP\BurningConfiguration;
use Rentalhost\BurningPHP\Session\Types\Abstracts\AbstractType;

/**
 * @property int    $version
 * @property float  $requestTimestamp
 * @property string $workingDirectory
 */
class InitializeType
    extends AbstractType
{
    public static function generate(?array $args = null): array
    {
        return parent::generate(array_replace($args ?? [], [
            'version'          => BurningConfiguration::getInstance()->getBurningVersionInt(),
            'requestTimestamp' => $_SERVER['REQUEST_TIME_FLOAT'],
            'workingDirectory' => getcwd()
        ]));
    }
}
