<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Session\Types\Shutdown;

use Rentalhost\BurningPHP\Session\Types\Abstracts\AbstractType;

/**
 * @property float $timestamp
 */
class ShutdownType
    extends AbstractType
{
    public function initialize(): void
    {
        $this->timestamp = microtime(true);
    }
}
