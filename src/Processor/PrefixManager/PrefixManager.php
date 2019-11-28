<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor\PrefixManager;

class PrefixManager
    extends \ArrayObject
{
    public function __toString()
    {
        return implode($this->getArrayCopy());
    }

    public function pop(): void
    {
        end($this);
        $this->offsetUnset(key($this));
    }

    public function toString(?string $appendLast = null): string
    {
        return implode(';', $this->getArrayCopy()) . ';' . $appendLast;
    }
}
