<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor\VariableManager;

class VariableManager
    extends \ArrayObject
{
    public function getVariable(string $name): ?int
    {
        if ($this->offsetExists($name)) {
            return $this[$name];
        }

        return null;
    }

    public function registerVariable(string $name, int $statementIndex): int
    {
        return $this[$name] = $statementIndex;
    }
}
