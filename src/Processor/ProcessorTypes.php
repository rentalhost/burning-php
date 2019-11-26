<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor;

use Rentalhost\BurningPHP\BurningConfiguration;
use Rentalhost\BurningPHP\Support\Traits\SingletonPatternTrait;

class ProcessorTypes
{
    use SingletonPatternTrait;

    /** @var int[] */
    private $typesIndex = [];

    /** @var resource|null */
    private $typesResource;

    public function getTypeIndex(string $type): int
    {
        if (!array_key_exists($type, $this->typesIndex)) {
            return $this->registerType($type);
        }

        return $this->typesIndex[$type];
    }

    public function initialize(): void
    {
        $burningConfiguration = BurningConfiguration::getInstance();

        $this->typesResource = fopen($burningConfiguration->getBurningDirectory() . '/' .
                                     $burningConfiguration->getPathWithSessionMask('TYPES'), 'wb');
    }

    private function registerType(string $type): int
    {
        $typeIndex = count($this->typesIndex);

        $this->typesIndex[$type] = $typeIndex;

        fwrite($this->typesResource, $type . "\n");

        return $typeIndex;
    }
}
