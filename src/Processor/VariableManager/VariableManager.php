<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor\VariableManager;

class VariableManager
    extends \ArrayObject
{
    /** @var array[] */
    private $stacks = [];

    public function getVariable(string $name): ?int
    {
        if ($this->offsetExists($name)) {
            return $this[$name];
        }

        return null;
    }

    public function importVariable(string $name): ?int
    {
        if (!$this->stacks) {
            return null;
        }

        $previousStack = end($this->stacks);

        if (array_key_exists($name, $previousStack)) {
            return $this->registerVariable($name, $previousStack[$name]);
        }

        return null;
    }

    public function pop(): void
    {
        $previousStack = array_pop($this->stacks) ?? [];
        $this->exchangeArray($previousStack);
    }

    public function push(): void
    {
        $this->stacks[] = $this->getArrayCopy();
        $this->exchangeArray([]);
    }

    public function registerVariable(string $name, int $statementIndex): int
    {
        return $this[$name] = $statementIndex;
    }
}
