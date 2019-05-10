<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Session\Types\Call;

use Rentalhost\BurningPHP\Session\Types\Interfaces\PositionalAttributesInterface;
use Rentalhost\BurningPHP\Support\Fluent;

/**
 * @property CallFlow[] $callFlows
 */
class CallReference
    extends Fluent
    implements PositionalAttributesInterface
{
    public function __construct()
    {
        $this->callFlows = [];
    }
}
