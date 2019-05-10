<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Session\Types\Initialize;

use Rentalhost\BurningPHP\BurningConfiguration;
use Rentalhost\BurningPHP\Session\Types\Abstracts\AbstractType;

/**
 * @property int    $version
 * @property float  $requestTimestamp
 * @property float  $timestamp
 * @property string $workingDirectory
 */
class InitializeType
    extends AbstractType
{
    public function initialize(): void
    {
        $this->version          = BurningConfiguration::getInstance()->getBurningVersionInt();
        $this->requestTimestamp = $_SERVER['REQUEST_TIME_FLOAT'];
        $this->timestamp        = microtime(true);
        $this->workingDirectory = getcwd();
    }
}
