<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Support\Traits;

trait HasAttributesTrait
{
    /** @var mixed[] */
    protected $attributes = [];

    public function &__get(string $name)
    {
        return $this->attributes[$name];
    }

    public function __set(string $name, $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function __isset(string $name): bool
    {
        return array_key_exists($name, $this->attributes);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        $attributes = $this->attributes;

        foreach ((new \ReflectionClass($this))->getProperties(\ReflectionProperty::IS_PUBLIC) as $reflectionProperty) {
            $attributes[$reflectionProperty->name] = $reflectionProperty->getValue($this);
        }

        return array_map(static function ($value) {
            return $value instanceof \JsonSerializable
                ? $value->jsonSerialize()
                : $value;
        }, $attributes);
    }
}
