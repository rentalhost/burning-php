<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Support;

use Rentalhost\BurningPHP\Support\Traits\HasAttributesTrait;

class Fluent
    implements \JsonSerializable
{
    use HasAttributesTrait;
}
