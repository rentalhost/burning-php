<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Support\Traits;

trait HasAttributesTrait
{
    /** @var mixed[] */
    private $attributes = [];

    public function __get(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    public function __set(string $name, $value)
    {
        $this->attributes[$name] = $value;
    }

    public function __isset(string $name)
    {
        return array_key_exists($name, $this->attributes);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        return $this->attributes;
    }
}
