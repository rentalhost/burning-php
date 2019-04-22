<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Session\Types;

use Rentalhost\BurningPHP\BurningConfiguration;

/**
 * @property int    $version
 * @property float  $requestTimestamp
 * @property string $workingDirectory
 */
class InitializeType
    extends Type
{
    public function __construct()
    {
        parent::__construct();

        $this->version          = BurningConfiguration::getInstance()->getBurningVersionInt();
        $this->requestTimestamp = $_SERVER['REQUEST_TIME_FLOAT'];
        $this->workingDirectory = getcwd();
    }
}
