<?php

declare(strict_types = 1);

namespace Rentalhost\BurningPHP\Processor;

use Rentalhost\BurningPHP\BurningConfiguration;
use Rentalhost\BurningPHP\Support\Traits\SingletonPatternTrait;

class ProcessorStrings
{
    use SingletonPatternTrait;

    /** @var int[] */
    private $stringsIndex = [];

    /** @var resource|null */
    private $stringsResource;

    public function getStringIndex(string $type): int
    {
        if (!array_key_exists($type, $this->stringsIndex)) {
            return $this->registerType($type);
        }

        return $this->stringsIndex[$type];
    }

    public function initialize(): void
    {
        $burningConfiguration = BurningConfiguration::getInstance();

        $this->stringsResource = fopen($burningConfiguration->getBurningDirectory() . '/' .
                                       $burningConfiguration->getPathWithSessionMask('STRINGS'), 'wb');
    }

    private function registerType(string $type): int
    {
        $typeIndex = count($this->stringsIndex);

        $this->stringsIndex[$type] = $typeIndex;

        fwrite($this->stringsResource, Processor::stringifyString($type) . "\n");

        return $typeIndex;
    }
}
